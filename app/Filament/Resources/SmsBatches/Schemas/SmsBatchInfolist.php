<?php

namespace App\Filament\Resources\SmsBatches\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SmsBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch Summary')
                    ->schema([
                        TextEntry::make('id')->label('Batch #'),
                        TextEntry::make('user.name')->label('Sent by')->placeholder('System'),
                        TextEntry::make('audience')->badge(),
                        TextEntry::make('class.class_code')->label('Class')->placeholder('—'),
                        TextEntry::make('total_recipients')->label('Recipients'),
                        TextEntry::make('sent_count')->label('Sent')->color('success'),
                        TextEntry::make('failed_count')->label('Failed')->color('danger'),
                        TextEntry::make('status')->badge(),
                        TextEntry::make('created_at')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(3),

                Section::make('Message')
                    ->schema([
                        TextEntry::make('body')->columnSpanFull()->prose(),
                    ]),

                Section::make('Recipients')
                    ->schema([
                        RepeatableEntry::make('messages')
                            ->schema([
                                TextEntry::make('guardian.full_name')->label('Guardian')->placeholder('—'),
                                TextEntry::make('phone'),
                                TextEntry::make('status')->badge()
                                    ->color(fn($state) => match ($state) {
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('sent_at')->dateTime('d/m/Y H:i')->placeholder('—'),
                                TextEntry::make('error')->placeholder('—')->columnSpanFull(),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }
}
