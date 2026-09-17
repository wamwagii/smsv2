<?php

namespace App\Jobs;

use App\Models\SmsMessage;
use App\Services\Sms\SmsProvider;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSmsMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public int $messageId)
    {
    }

    public function handle(SmsProvider $provider): void
    {
        $message = SmsMessage::find($this->messageId);

        if (! $message || $message->status === 'sent') {
            return;
        }

        $result = $provider->send($message->phone, $message->body);

        $message->update([
            'status'              => $result['success'] ? 'sent' : 'failed',
            'provider'            => $provider->name(),
            'provider_message_id' => $result['message_id'],
            'error'               => $result['error'],
            'sent_at'             => $result['success'] ? now() : null,
        ]);

        $batch = $message->batch;

        if ($batch) {
            $sent   = $batch->messages()->where('status', 'sent')->count();
            $failed = $batch->messages()->where('status', 'failed')->count();
            $total  = $batch->messages()->count();

            $batch->update([
                'sent_count'   => $sent,
                'failed_count' => $failed,
                'status'       => ($sent + $failed) >= $total ? 'completed' : 'processing',
            ]);
        }
    }
}
