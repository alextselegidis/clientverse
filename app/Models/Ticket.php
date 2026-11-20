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

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = ['customer_id', 'contact_id', 'assigned_to', 'subject', 'status', 'priority'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->oldest();
    }

    /**
     * Where replies are emailed: the ticket's contact, else the customer.
     */
    public function getRecipientEmailAttribute(): ?string
    {
        return $this->contact?->email ?: $this->customer?->email;
    }

    public static function statuses(): array
    {
        return [
            'open' => __('open'),
            'answered' => __('answered'),
            'on_hold' => __('on_hold'),
            'closed' => __('closed'),
        ];
    }

    public static function statusColors(): array
    {
        return ['open' => 'danger', 'answered' => 'info', 'on_hold' => 'warning', 'closed' => 'secondary'];
    }
}
