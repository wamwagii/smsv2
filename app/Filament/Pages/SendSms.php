<?php

namespace App\Filament\Pages;

use App\Models\Classes;
use App\Models\Guardian;
use App\Models\SmsBatch;
use App\Models\SmsTemplate;
use App\Services\Sms\SmsBatchService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class SendSms extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-s-paper-airplane';

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 0;

    protected static ?string $navigationLabel = 'Send SMS';

    protected static ?string $title = 'Send SMS to Parents';

    protected string $view = 'filament.pages.send-sms';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'audience'    => 'all',
            'min_balance' => 1,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('template_id')
                    ->label('Template (optional)')
                    ->options(SmsTemplate::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('body', SmsTemplate::find($state)?->body);
                        }
                    }),

                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->rows(5)
                    ->maxLength(480)
                    ->helperText('Placeholders: {guardian_name}, {student_name}, {school_name}, {balance}, {balance_formatted}'),

                Radio::make('audience')
                    ->label('Recipients')
                    ->options([
                        'all'     => 'All guardians (opted-in, active students)',
                        'class'   => 'By class',
                        'custom'  => 'Custom selection',
                        'balance' => 'Guardians with outstanding fees',
                    ])
                    ->default('all')
                    ->live()
                    ->required(),

                Select::make('class_id')
                    ->label('Class')
                    ->options(
                        Classes::orderBy('level')->get()
                            ->mapWithKeys(fn ($c) => [$c->id => 'Grade ' . $c->level])
                    )
                    ->visible(fn (callable $get) => $get('audience') === 'class')
                    ->required(fn (callable $get) => $get('audience') === 'class'),

                Select::make('guardian_ids')
                    ->label('Guardians')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn () => Guardian::query()
                        ->where('status', 'active')
                        ->whereNotNull('phone_number')
                        ->orderBy('first_name')
                        ->get()
                        ->mapWithKeys(fn ($g) => [$g->id => $g->full_name . ' — ' . $g->phone_number])
                    )
                    ->visible(fn (callable $get) => $get('audience') === 'custom')
                    ->required(fn (callable $get) => $get('audience') === 'custom'),

                TextInput::make('min_balance')
                    ->label('Minimum balance (KES)')
                    ->numeric()
                    ->default(1)
                    ->minValue(0)
                    ->step(1000)
                    ->prefix('KES')
                    ->visible(fn (callable $get) => $get('audience') === 'balance')
                    ->required(fn (callable $get) => $get('audience') === 'balance')
                    ->helperText('Only guardians whose total outstanding balance exceeds this amount will be messaged.'),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send SMS')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Send SMS')
                ->modalDescription(function () {
                    $data = $this->form->getState();

                    $audience   = $data['audience'] ?? 'all';
                    $classId    = $data['class_id'] ?? null;
                    $guardianIds = $data['guardian_ids'] ?? [];
                    $minBalance = (float) ($data['min_balance'] ?? 1);

                    $count = app(SmsBatchService::class)->previewCount(
                        audience:    $audience,
                        classId:     $classId,
                        guardianIds: $guardianIds,
                        minBalance:  $minBalance,
                    );

                    $cost = number_format($count * 0.80, 2);

                    $extra = '';

                    if ($audience === 'balance') {
                        $extra = ' Only guardians with a balance above KES ' .
                                 number_format($minBalance, 0) . ' will receive this.';
                    }

                    return "You are about to send to {$count} guardian(s).{$extra} " .
                           "Estimated cost: KES {$cost}. Continue?";
                })
                ->modalSubmitActionLabel('Yes, send now')
                ->modalCancelActionLabel('Cancel')
                ->action(fn () => $this->send(app(SmsBatchService::class))),
        ];
    }

    public function send(SmsBatchService $service): void
    {
        $data = $this->form->getState();

        /** @var SmsBatch $batch */
        $batch = $service->dispatch(
            body:        $data['body'],
            audience:    $data['audience'],
            classId:     $data['class_id'] ?? null,
            guardianIds: $data['guardian_ids'] ?? [],
            templateId:  $data['template_id'] ?? null,
            userId:      auth()->id(),
            minBalance:  (float) ($data['min_balance'] ?? 1),
        );

        Notification::make()
            ->title("SMS batch queued — {$batch->total_recipients} recipients")
            ->success()
            ->send();

        $this->redirect(
            route('filament.admin.resources.sms-batches.view', ['record' => $batch->id])
        );
    }
}