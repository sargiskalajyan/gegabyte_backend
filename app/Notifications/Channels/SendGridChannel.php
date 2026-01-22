<?php

namespace App\Notifications\Channels;

use App\Services\SendGridService;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendGridChannel
{
    protected SendGridService $sendGrid;

    public function __construct(SendGridService $sendGrid)
    {
        $this->sendGrid = $sendGrid;
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toMail')) {
            return;
        }

        $mailMessage = $notification->toMail($notifiable);
        if (!$mailMessage) {
            return;
        }

        // Determine recipient
        $to = null;
        if (method_exists($notifiable, 'routeNotificationFor')) {
            $to = $notifiable->routeNotificationFor('mail') ?: ($notifiable->email ?? null);
        }
        $to = $to ?: ($notifiable->email ?? null);
        if (empty($to)) {
            return;
        }

        // Subject
        $subject = $mailMessage->subject ?? config('app.name');

        // Render view if provided
        $html = '';
        if (isset($mailMessage->view)) {
            $view = $mailMessage->view;
            $data = $mailMessage->viewData ?? [];
            try {
                if (is_array($view) && isset($view['html'])) {
                    $html = view($view['html'], $data)->render();
                } elseif (is_string($view)) {
                    $html = view($view, $data)->render();
                }
            } catch (\Throwable $e) {
                $html = '';
            }
        }

        // If no html yet, try to build basic HTML from lines/text (fallback)
        if (empty($html) && isset($mailMessage->introLines)) {
            $lines = array_merge($mailMessage->introLines ?? [], $mailMessage->outroLines ?? []);
            $html = '<p>' . implode('</p><p>', $lines) . '</p>';
        }

        // send via SendGridService
        if (!empty($html)) {
            $this->sendGrid->sendHtml($to, $subject, $html);
        }
    }
}
