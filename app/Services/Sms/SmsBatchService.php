<?php

namespace App\Services\Sms;

use App\Jobs\SendSmsMessage;
use App\Models\Guardian;
use App\Models\SmsBatch;
use App\Models\SmsMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SmsBatchService
{
    public function dispatch(
        string $body,
        string $audience,
        ?int $classId = null,
        array $guardianIds = [],
        ?int $templateId = null,
        ?int $userId = null,
        float $minBalance = 0.01,
    ): SmsBatch {
        $recipients = $this->resolveRecipients($audience, $classId, $guardianIds, $minBalance);

        /** @var SmsBatch $batch */
        $batch = DB::transaction(
            fn (): SmsBatch => $this->createBatchTransaction(
                body: $body,
                audience: $audience,
                classId: $classId,
                templateId: $templateId,
                userId: $userId,
                recipients: $recipients,
            )
        );

        return $batch;
    }

    protected function createBatchTransaction(
        string $body,
        string $audience,
        ?int $classId,
        ?int $templateId,
        ?int $userId,
        Collection $recipients,
    ): SmsBatch {
        $batch = SmsBatch::create([
            'user_id'          => $userId,
            'template_id'      => $templateId,
            'body'             => $body,
            'audience'         => $audience,
            'class_id'         => $classId,
            'total_recipients' => $recipients->count(),
            'status'           => $recipients->isEmpty() ? 'completed' : 'processing',
        ]);

        foreach ($recipients as $recipient) {
            $message = SmsMessage::create([
                'batch_id'    => $batch->id,
                'guardian_id' => $recipient->id,
                'student_id'  => $recipient->primary_student_id,
                'phone'       => $recipient->phone_number,
                'body'        => $this->personalise($body, $recipient),
                'status'      => 'pending',
            ]);

            SendSmsMessage::dispatch($message->id);
        }

        return $batch;
    }

    /**
     * Resolve recipients.
     *
     * POLICY: Only a student's father or mother receives SMS.
     * Guardians (relationship = 'guardian') and others are never contacted,
     * even if linked to a student and opted in.
     *
     * @return Collection<int, Guardian>
     */
    public function resolveRecipients(
        string $audience,
        ?int $classId = null,
        array $guardianIds = [],
        float $minBalance = 0.01,
    ): Collection {
        if ($audience === 'balance') {
            return $this->resolveBalanceRecipients($minBalance);
        }

        $studentConstraint = function ($q) use ($audience, $classId) {
            $q->where('students.status', 'active')
              ->where('student_parent.receives_notifications', true);

            if ($audience === 'class' && $classId) {
                $q->where('students.class_id', $classId);
            }

            return $q;
        };

        $query = Guardian::query()
            ->where('parents.status', 'active')
            ->whereNotNull('parents.phone_number')
            ->where('parents.phone_number', '!=', '')
            ->whereIn('parents.relationship', Guardian::SMS_RELATIONSHIPS)
            ->whereHas('students', $studentConstraint);

        if ($audience === 'custom' && ! empty($guardianIds)) {
            $query->whereIn('parents.id', $guardianIds);
        }

        return $query
            ->with(['students' => function ($q) use ($studentConstraint) {
                $studentConstraint($q);

                $q->select(
                    'students.id',
                    'students.first_name',
                    'students.middle_name',
                    'students.last_name',
                    'students.class_id',
                );
            }])
            ->get()
            ->map(function (Guardian $guardian) {
                $primary = $guardian->students->firstWhere('pivot.is_primary_contact', true)
                    ?? $guardian->students->first();

                $guardian->primary_student_id = $primary?->id;
                $guardian->primary_student    = $primary;
                $guardian->balance_owed       = 0.0;

                return $guardian;
            })
            ->filter(fn (Guardian $g) => $g->students->isNotEmpty())
            ->values();
    }

    /**
     * Balance audience. Only father/mother contacts are considered.
     *
     * @return Collection<int, Guardian>
     */
    protected function resolveBalanceRecipients(float $minBalance): Collection
    {
        $minBalance = max($minBalance, 1.00);

        $eligible = $this->eligibleBalanceGuardians($minBalance);

        if ($eligible->isEmpty()) {
            return collect();
        }

        $guardianIds = $eligible->pluck('parent_id')->all();
        $balances    = $eligible->pluck('balance', 'parent_id');

        return Guardian::query()
            ->whereIn('parents.id', $guardianIds)
            ->where('parents.status', 'active')
            ->whereNotNull('parents.phone_number')
            ->where('parents.phone_number', '!=', '')
            ->whereIn('parents.relationship', Guardian::SMS_RELATIONSHIPS)
            ->with(['students' => function ($q) {
                $q->where('students.status', 'active')
                  ->where('student_parent.receives_notifications', true)
                  ->select(
                      'students.id',
                      'students.first_name',
                      'students.middle_name',
                      'students.last_name',
                      'students.class_id',
                  );
            }])
            ->get()
            ->map(function (Guardian $guardian) use ($balances) {
                $primary = $guardian->students->firstWhere('pivot.is_primary_contact', true)
                    ?? $guardian->students->first();

                $guardian->primary_student_id = $primary?->id;
                $guardian->primary_student    = $primary;
                $guardian->balance_owed       = (float) ($balances[$guardian->id] ?? 0.0);

                return $guardian;
            })
            ->filter(fn (Guardian $g) => $g->students->isNotEmpty())
            ->values();
    }

    public function previewCount(
        string $audience,
        ?int $classId = null,
        array $guardianIds = [],
        float $minBalance = 0.01,
    ): int {
        return $this->resolveRecipients($audience, $classId, $guardianIds, $minBalance)->count();
    }

    /**
     * Guardians (parents only) who owe money, with their outstanding balance.
     * The relationship filter is part of the SQL — so a "guardian" (uncle)
     * with a high balance is never included.
     *
     * @return \Illuminate\Support\Collection<int, object{parent_id:int, balance:float}>
     */
    protected function eligibleBalanceGuardians(float $minBalance): Collection
    {
        $balanceExpr = $this->invoicesHaveBalanceColumn()
            ? 'SUM(invoices.balance)'
            : 'SUM(invoices.amount - invoices.amount_paid)';

        return DB::table('student_parent')
            ->join('parents', 'parents.id', '=', 'student_parent.parent_id')
            ->join('students', 'students.id', '=', 'student_parent.student_id')
            ->join('invoices', 'invoices.student_id', '=', 'students.id')
            ->where('student_parent.receives_notifications', true)
            ->where('students.status', 'active')
            ->whereIn('parents.relationship', Guardian::SMS_RELATIONSHIPS)
            ->whereNotIn('invoices.status', ['paid', 'waived'])
            ->groupBy('student_parent.parent_id')
            ->havingRaw("{$balanceExpr} > CAST(? AS REAL)", [
                number_format($minBalance, 2, '.', ''),
            ])
            ->selectRaw("student_parent.parent_id as parent_id, {$balanceExpr} as balance")
            ->get();
    }

    protected function invoicesHaveBalanceColumn(): bool
    {
        static $has = null;

        if ($has === null) {
            $has = Schema::hasColumn('invoices', 'balance');
        }

        return $has;
    }

    /**
     * Replace placeholders in the message body.
     *
     * Supported: {guardian_name}, {student_name}, {school_name},
     *            {balance}, {balance_formatted}
     */
    protected function personalise(string $body, Guardian $guardian): string
    {
        $student = $guardian->students->firstWhere('pivot.is_primary_contact', true)
            ?? $guardian->students->first();

        $balance = max(0.0, (float) ($guardian->balance_owed ?? 0.0));

        return str_replace(
            ['{guardian_name}', '{student_name}', '{school_name}', '{balance}', '{balance_formatted}'],
            [
                $guardian->full_name,
                $student?->full_name ?? 'Parent/Guardian',
                config('school.name', config('app.name', 'School')),
                number_format($balance, 2, '.', ''),
                'KES ' . number_format($balance, 2),
            ],
            $body,
        );
    }
}