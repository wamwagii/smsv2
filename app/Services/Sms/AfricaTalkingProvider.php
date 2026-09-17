<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricaTalkingProvider implements SmsProvider
{
    protected string $username;
    protected string $apiKey;
    protected ?string $from;
    protected bool $sandbox;

    public function __construct()
{
    $this->username = trim((string) config('sms.africastalking.username'));
    $this->apiKey   = trim((string) config('sms.africastalking.api_key'));
    $this->from     = trim((string) config('sms.africastalking.from')) ?: null;
    $this->sandbox  = (bool) config('sms.africastalking.sandbox', true);
}

    public function name(): string
    {
        return 'africastalking';
    }

    public function send(string $to, string $message): array
    {
        $endpoint = $this->sandbox
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        // AT expects international format without the leading +
        $recipient = $this->normalisePhone($to);

        $payload = [
            'username' => $this->username,
            'to'       => $recipient,
            'message'  => $message,
        ];

        if ($this->from) {
            $payload['from'] = $this->from;
        }

        try {
            $response = Http::withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])
                ->asForm()
                ->timeout(15)
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                return [
                    'success'    => false,
                    'message_id' => null,
                    'error'      => 'HTTP ' . $response->status() . ': ' . $response->body(),
                ];
            }

            $data      = $response->json();
            $recipient = $data['SMSMessageData']['Recipients'][0] ?? null;

            if (! $recipient) {
                return [
                    'success'    => false,
                    'message_id' => null,
                    'error'      => 'Unexpected provider response: ' . json_encode($data),
                ];
            }

            // AT returns statusCode 101 for accepted; anything else is an error
            $statusCode = (int) ($recipient['statusCode'] ?? 0);
            $statusText = $recipient['status'] ?? null;

            if ($statusCode !== 101) {
                return [
                    'success'    => false,
                    'message_id' => $recipient['messageId'] ?? null,
                    'error'      => $statusText ?? 'Unknown error',
                ];
            }

            return [
                'success'    => true,
                'message_id' => $recipient['messageId'] ?? null,
                'error'      => null,
            ];
        } catch (\Throwable $e) {
            Log::error("Africa's Talking SMS failed", [
                'to'      => $to,
                'message' => $e->getMessage(),
            ]);

            return [
                'success'    => false,
                'message_id' => null,
                'error'      => $e->getMessage(),
            ];
        }
    }

    /**
     * Convert a Kenyan phone number to AT's expected format.
     * Accepts: +254712345678, 254712345678, 0712345678, 712345678
     * Returns: 254712345678
     */
    protected function normalisePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0')) {
            // 0712… → 254712…
            $digits = '254' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            // 712345678 → 254712345678
            $digits = '254' . $digits;
        }

        return $digits;
    }
}