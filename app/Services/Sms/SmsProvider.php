<?php

namespace App\Services\Sms;

interface SmsProvider
{
    /**
     * Send a single SMS.
     *
     * @return array{success: bool, message_id: ?string, error: ?string}
     */
    public function send(string $to, string $message): array;

    /**
     * Machine-readable provider name for logging.
     */
    public function name(): string;
}