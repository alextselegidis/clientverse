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

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketReplied;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TicketsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_ticket_is_opened_answered_and_the_customer_is_emailed(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $customer = Customer::create(['name' => 'Acme', 'email' => 'help@acme.test']);

        $this->actingAs($user)->get(route('tickets.create'))->assertOk();

        $this->actingAs($user)
            ->post(route('tickets.store'), [
                'subject' => 'VPN is down',
                'customer_id' => $customer->id,
                'priority' => 'high',
                'body' => 'Nobody can connect since 9am.',
            ])
            ->assertSessionHasNoErrors();

        $ticket = Ticket::sole();
        $this->assertSame('open', $ticket->status);

        $this->actingAs($user)->get(route('tickets', ['status' => 'open']))->assertOk()->assertSee('VPN is down');
        $this->actingAs($user)->get(route('tickets.show', $ticket))->assertOk()->assertSee('Nobody can connect');

        $this->actingAs($user)
            ->post(route('tickets.replies.store', $ticket), ['body' => 'Restarted the gateway.', 'notify' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertSame('answered', $ticket->fresh()->status);
        $this->assertSame(2, $ticket->replies()->count());
        Notification::assertSentTo(
            new AnonymousNotifiable,
            TicketReplied::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'help@acme.test'
        );

        $this->actingAs($user)
            ->put(route('tickets.update', $ticket), ['status' => 'closed', 'priority' => 'normal'])
            ->assertSessionHasNoErrors();
        $this->assertSame('closed', $ticket->fresh()->status);
    }

    public function test_an_unticked_reply_sends_nothing(): void
    {
        Notification::fake();

        $ticket = Ticket::create(['subject' => 'Printer', 'customer_id' => Customer::create(['name' => 'A', 'email' => 'a@a.test'])->id]);

        $this->actingAs(User::factory()->create())
            ->post(route('tickets.replies.store', $ticket), ['body' => 'Internal note', 'notify' => '0']);

        Notification::assertNothingSent();
    }
}
