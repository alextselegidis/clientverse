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

use App\Models\Contract;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells staff that a recurring contract is about to end, so the renewal is not missed.
 */
class ContractRenewalDue extends Notification
{
    public function __construct(public Contract $contract) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $values = [
            'title' => $this->contract->title,
            'customer' => $this->contract->customer?->name ?? '-',
            'date' => $this->contract->end_date->format('M d, Y'),
        ];

        return (new MailMessage())
            ->subject(__('contract_renewal_subject', $values))
            ->line(__('contract_renewal_body', $values))
            ->action(__('open_contract'), route('contracts.show', $this->contract->id));
    }
}
