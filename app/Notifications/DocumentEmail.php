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

use App\Models\Customer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as Notifier;

/**
 * A plain email built from one of the Setup > Email templates.
 */
class DocumentEmail extends Notification
{
    public function __construct(public string $subject, public string $body) {}

    /**
     * Fill in the $type template and email it to the customer. False when the
     * customer has no email address; throws when the mail cannot be sent.
     */
    public static function sendTemplate(?Customer $customer, string $type, array $values): bool
    {
        if (blank($customer?->email)) {
            return false;
        }

        $template = email_template($type, $values + [
            'customer' => $customer->name,
            'company' => setting('company_name') ?: config('app.name'),
        ]);

        Notifier::route('mail', $customer->email)->notify(new self($template['subject'], $template['body']));

        return true;
    }

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
