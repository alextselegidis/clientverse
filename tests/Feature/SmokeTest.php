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

use App\Models\Announcement;
use App\Models\Article;
use App\Models\Contact;
use App\Models\Contract;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomField;
use App\Models\Department;
use App\Models\Estimate;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\Survey;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Tests\TestCase;

/**
 * Opens every GET screen with a record of each kind in place, so a broken view,
 * a missing translation variable or a bad query shows up as a failure here.
 */
class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private array $params = [];

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create(['name' => 'Jane <b>Doe</b>', 'company' => 'Acme', 'email' => 'jane@acme.test']);
        $contact = Contact::create(['customer_id' => $customer->id, 'first_name' => 'Jo', 'last_name' => 'Smith', 'email' => 'jo@acme.test']);
        CustomerNote::create(['customer_id' => $customer->id, 'user_id' => $admin->id, 'content' => 'Called']);
        $project = Project::create(['customer_id' => $customer->id, 'name' => 'Website']);
        $milestone = Milestone::create(['project_id' => $project->id, 'name' => 'Launch', 'due_date' => today()]);
        $sale = Sale::create(['customer_id' => $customer->id, 'name' => 'Upsell', 'value' => 1000, 'expected_close_date' => today()]);
        $contract = Contract::create(['customer_id' => $customer->id, 'title' => 'Support', 'status' => 'draft', 'end_date' => today()]);

        $invoice = Invoice::create(['customer_id' => $customer->id, 'status' => 'sent', 'currency' => 'EUR', 'issue_date' => today(), 'due_date' => today()->subDay()]);
        $invoice->payments()->create(['amount' => 10, 'paid_on' => today(), 'method' => 'Card']);
        $invoice->syncItems([['description' => 'Work', 'quantity' => 2, 'unit_price' => 100, 'tax_rate' => 24]]);
        $estimate = Estimate::create(['customer_id' => $customer->id, 'status' => 'sent', 'currency' => 'EUR']);
        $estimate->syncItems([['description' => 'Audit', 'quantity' => 1, 'unit_price' => 300]]);
        $proposal = \App\Models\Proposal::create(['customer_id' => $customer->id, 'sale_id' => $sale->id, 'subject' => 'Pitch', 'status' => 'sent']);
        $proposal->syncItems([['description' => 'Design', 'quantity' => 1, 'unit_price' => 500]]);
        $credit = CreditNote::create(['customer_id' => $customer->id, 'status' => 'issued', 'currency' => 'EUR']);
        $credit->syncItems([['description' => 'Refund', 'quantity' => 1, 'unit_price' => 20]]);
        $credit->invoice()->associate($invoice)->save();
        $item = Item::create(['description' => 'Hosting', 'unit_price' => 15, 'tax_rate' => 0]);
        $expense = Expense::create(['customer_id' => $customer->id, 'project_id' => $project->id, 'expense_date' => today(), 'description' => 'Taxi', 'amount' => 30, 'currency' => 'EUR', 'billable' => true]);

        $task = Task::create(['title' => 'Call back', 'project_id' => $project->id, 'assigned_to' => $admin->id, 'due_date' => today()]);
        $task->timeEntries()->create(['user_id' => $admin->id, 'started_at' => now()->subHour(), 'ended_at' => now()]);
        $task->timeEntries()->create(['user_id' => $admin->id, 'started_at' => now()->subMinutes(5)]);
        $ticket = Ticket::create(['customer_id' => $customer->id, 'contact_id' => $contact->id, 'subject' => 'Help', 'assigned_to' => $admin->id]);
        $ticket->replies()->create(['user_id' => $admin->id, 'body' => 'Looking']);

        $article = Article::create(['title' => 'FAQ', 'body' => '# Hi', 'is_public' => true]);
        $survey = Survey::create(['title' => 'NPS', 'questions' => [
            ['text' => 'Rate', 'type' => 'rating', 'options' => []],
            ['text' => 'Pick', 'type' => 'choice', 'options' => ['A', 'B']],
            ['text' => 'Say', 'type' => 'text', 'options' => []],
        ]]);
        $survey->responses()->create(['answers' => [4, 'A', 'Nice']]);
        foreach (array_keys(Goal::metrics()) as $metric) {
            $goal = Goal::create(['name' => $metric, 'metric' => $metric, 'currency' => 'EUR', 'target' => 5, 'start_date' => today()->startOfMonth(), 'end_date' => today()->endOfMonth()]);
        }

        $announcement = Announcement::create(['title' => 'News', 'body' => 'Hi', 'audience' => 'everyone']);
        $department = Department::create(['name' => 'Support']);
        $ticket->update(['department_id' => $department->id]);
        $customField = CustomField::create([
            'applies_to' => 'customers', 'label' => 'Tier', 'type' => 'select', 'options' => ['Gold'],
        ]);
        $customer->update(['custom_values' => [$customField->id => 'Gold']]);
        $subscription = Subscription::create([
            'customer_id' => $customer->id, 'name' => 'Care plan', 'currency' => 'EUR', 'amount' => 50, 'tax_rate' => 24,
        ]);

        $this->params = [
            'customer' => $customer->id, 'contact' => $contact->id, 'project' => $project->id, 'milestone' => $milestone->id,
            'sale' => $sale->id, 'contract' => $contract->id, 'invoice' => $invoice->id, 'estimate' => $estimate->id, 'proposal' => $proposal->id,
            'creditNote' => $credit->id, 'item' => $item->id, 'expense' => $expense->id, 'task' => $task->id,
            'ticket' => $ticket->id, 'article' => $article->id, 'survey' => $survey->id, 'goal' => $goal->id,
            'announcement' => $announcement->id, 'department' => $department->id,
            'customField' => $customField->id, 'subscription' => $subscription->id,
            'user' => $admin->id, 'slug' => $article->slug,
        ];

        $this->actingAs($admin);
    }

    /**
     * Signed-in GET screens, with route parameters filled from the records above.
     */
    private function screens(): array
    {
        $skip = ['/', 'login', 'recovery', 'recovery/{token}', 'up'];

        return collect(Router::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => in_array('GET', $route->methods(), true))
            ->filter(fn (Route $route) => in_array('auth', $route->gatherMiddleware(), true))
            ->reject(fn (Route $route) => in_array($route->uri(), $skip, true) || str_contains($route->uri(), '/download'))
            ->map(fn (Route $route) => route($route->getName(), array_intersect_key($this->params, array_flip($route->parameterNames()))))
            ->values()
            ->all();
    }

    public function test_every_screen_renders_for_an_admin(): void
    {
        $screens = $this->screens();
        $this->assertGreaterThan(60, count($screens));

        foreach ($screens as $url) {
            $response = $this->get($url);
            $this->assertSame(200, $response->getStatusCode(), "{$url} returned {$response->getStatusCode()}");

            if (!str_ends_with($url, '.csv')) {
                $response->assertDontSee('Jane <b>Doe</b>', false);
            }
        }
    }

    public function test_a_user_with_no_permissions_is_kept_out_of_every_module(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user', 'permissions' => []]));
        $open = ['dashboard', 'search', 'account', 'about', 'calendar'];

        foreach ($this->screens() as $url) {
            $path = trim(parse_url($url, PHP_URL_PATH), '/');
            $status = $this->get($url)->status();

            if (in_array($path, $open, true)) {
                $this->assertSame(200, $status, $url);
            } else {
                $this->assertSame(403, $status, "{$url} returned {$status}");
            }
        }
    }

    public function test_every_public_page_renders(): void
    {
        auth()->logout();

        foreach ([
            Contract::find($this->params['contract'])->signingUrl(),
            Invoice::find($this->params['invoice'])->publicUrl(),
            Estimate::find($this->params['estimate'])->publicUrl(),
            Survey::find($this->params['survey'])->publicUrl(),
            Subscription::find($this->params['subscription'])->publicUrl(),
            route('kb'),
            route('kb.show', $this->params['slug']),
        ] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Jane <b>Doe</b>', false);
        }
    }
}
