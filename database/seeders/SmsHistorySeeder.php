<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\SmsBatch;
use App\Models\SmsMessage;
use App\Models\SmsTemplate;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SmsHistorySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $templates = SmsTemplate::all();

        if ($templates->isEmpty()) {
            $this->command->error('No SMS templates found. Run SmsTemplateSeeder first.');
            return;
        }

        $guardians = Guardian::query()
            ->where('status', 'active')
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->inRandomOrder()
            ->limit(60)
            ->get();

        if ($guardians->isEmpty()) {
            $this->command->error('No guardians with phone numbers found.');
            return;
        }

        $batchesCreated = 0;
        $messagesCreated = 0;

        // Create 15 historical batches spread over the last 90 days
        for ($i = 0; $i < 15; $i++) {
            $template = $templates->random();
            $audience = collect(['all', 'class', 'custom'])->random();

            // Pick a class_id only when audience = 'class'
            $classId = $audience === 'class'
                ? Student::query()->whereNotNull('class_id')->inRandomOrder()->value('class_id')
                : null;

            // Choose 5–25 random guardians for this batch
            $recipientCount = rand(5, 25);
            $recipients = $guardians->random(min($recipientCount, $guardians->count()));

            $createdAt = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));

            $sentCount = 0;
            $failedCount = 0;

            // First create the batch with placeholder counts
            $batch = SmsBatch::create([
                'user_id'          => $user?->id,
                'template_id'      => $template->id,
                'body'             => $template->body,
                'audience'         => $audience,
                'class_id'         => $classId,
                'total_recipients' => $recipients->count(),
                'sent_count'       => 0,
                'failed_count'     => 0,
                'status'           => 'processing',
                'created_at'       => $createdAt,
                'updated_at'       => $createdAt,
            ]);

            foreach ($recipients as $guardian) {
                $student = $guardian->students()->inRandomOrder()->first();

                // 90% success rate, 10% failure
                $success = rand(1, 100) <= 90;

                $body = str_replace(
                    ['{guardian_name}', '{student_name}', '{school_name}'],
                    [
                        $guardian->full_name,
                        $student?->full_name ?? 'Parent/Guardian',
                        config('app.name', 'School'),
                    ],
                    $template->body,
                );

                $sentAt = $success
                    ? $createdAt->copy()->addSeconds(rand(1, 60))
                    : null;

                SmsMessage::create([
                    'batch_id'            => $batch->id,
                    'guardian_id'         => $guardian->id,
                    'student_id'          => $student?->id,
                    'phone'               => $guardian->phone_number,
                    'body'                => $body,
                    'status'              => $success ? 'sent' : 'failed',
                    'provider'            => 'africastalking',
                    'provider_message_id' => $success ? 'ATXid_' . bin2hex(random_bytes(12)) : null,
                    'error'               => $success ? null : collect([
                        'InvalidPhoneNumber',
                        'DeliveryFailure',
                        'InsufficientBalance',
                    ])->random(),
                    'sent_at'             => $sentAt,
                    'created_at'          => $createdAt,
                    'updated_at'          => $sentAt ?? $createdAt,
                ]);

                $success ? $sentCount++ : $failedCount++;
                $messagesCreated++;
            }

            // Update batch with real counts
            $batch->update([
                'sent_count'   => $sentCount,
                'failed_count' => $failedCount,
                'status'       => 'completed',
                'updated_at'   => $createdAt->copy()->addMinutes(5),
            ]);

            $batchesCreated++;
        }

        $this->command->info("SMS history seeded: {$batchesCreated} batches, {$messagesCreated} messages.");
    }
}