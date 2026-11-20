<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A plain email built from one of the Setup > Email templates.
 */
class DocumentEmail extends Notification
{
    public function __construct(public string $subject, public string $body) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())->subject($this->subject);

        foreach (preg_split('/\R{2,}/', trim($this->body)) as $paragraph) {
            $message->line($paragraph);
        }

        return $message;
    }
}
