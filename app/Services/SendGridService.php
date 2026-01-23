<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailable;

class SendGridService
{
    public function sendMailable(string $toEmail, Mailable $mailable): bool
    {
        $apiKey = config('services.sendgrid.key') ?: env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('SENDGRID_API_KEY is not configured.');
        }

        // Ensure mailable is built so subject and view are prepared
        if (method_exists($mailable, 'build')) {
            $mailable->build();
        }

        // Render the mailable to HTML
        $html = method_exists($mailable, 'render') ? $mailable->render() : '';

        // Try to read subject from the mailable
        $subject = property_exists($mailable, 'subject') && !empty($mailable->subject)
            ? $mailable->subject
            : ($mailable->subject ?? '');

        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name');

        $payload = [
            'personalizations' => [
                [
                    'to' => [[ 'email' => $toEmail ]],
                    'subject' => $subject ?: 'Message from ' . ($fromName ?? $fromEmail),
                ],
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $html,
                ],
            ],
        ];

        $response = Http::withToken($apiKey)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('https://api.sendgrid.com/v3/mail/send', $payload);

        if (!$response->successful()) {
            try {
                Log::error('SendGrid sendMailable failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload,
                ]);
            } catch (\Throwable $e) {
                // swallow logging errors to avoid breaking mail flow
            }
            return false;
        }

        return true;
    }

    /**
     * Send raw HTML content via SendGrid API.
     */
    public function sendHtml(string $toEmail, string $subject, string $html): bool
    {
        $apiKey = config('services.sendgrid.key') ?: env('SENDGRID_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('SENDGRID_API_KEY is not configured.');
        }

        $fromEmail = config('mail.from.address');
        $fromName = config('mail.from.name');

        $payload = [
            'personalizations' => [
                [
                    'to' => [[ 'email' => $toEmail ]],
                    'subject' => $subject ?: 'Message from ' . ($fromName ?? $fromEmail),
                ],
            ],
            'from' => [
                'email' => $fromEmail,
                'name' => $fromName,
            ],
            'content' => [
                [
                    'type' => 'text/html',
                    'value' => $html,
                ],
            ],
        ];

        $response = Http::withToken($apiKey)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('https://api.sendgrid.com/v3/mail/send', $payload);

        if (!$response->successful()) {
            try {
                Log::error('SendGrid sendHtml failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload,
                ]);
            } catch (\Throwable $e) {
                // swallow logging errors to avoid breaking mail flow
            }
            return false;
        }

        return true;
    }
}
