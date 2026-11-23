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

use App\Http\Controllers\InstallController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\ContractsController;
use App\Http\Controllers\CreditNotesController;
use App\Http\Controllers\CustomerNotesController;
use App\Http\Controllers\CustomFieldsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\DocumentEmailsController;
use App\Http\Controllers\EstimatesController;
use App\Http\Controllers\GoalsController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MilestonesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\Portal\PortalController;
use App\Http\Controllers\Portal\PortalLoginController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\ProposalsController;
use App\Http\Controllers\Public\PublicContractsController;
use App\Http\Controllers\Public\KnowledgeBaseController;
use App\Http\Controllers\Public\PayPalController;
use App\Http\Controllers\Public\PublicDocumentsController;
use App\Http\Controllers\Public\PublicSurveysController;
use App\Http\Controllers\Public\StripeController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\SurveysController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TicketRepliesController;
use App\Http\Controllers\TicketsController;
use App\Http\Controllers\TimeEntriesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

// Guest
// The browser installer; InstallOrUpdate answers 404 here once the app is installed.
Route::get('/install', [InstallController::class, 'index'])->name('install');
Route::post('/install', [InstallController::class, 'store'])->middleware('throttle:10,1')->name('install.store');

Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    // WelcomeController
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
    // LoginController
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'perform'])->name('login.perform');
    // RecoveryController
    Route::get('/recovery', [RecoveryController::class, 'index'])->name('recovery');
    Route::post('/recovery', [RecoveryController::class, 'perform'])->name('recovery.perform');

    // The name is fixed: it is the route Laravel's reset notification links to.
    Route::get('/recovery/{token}', [RecoveryController::class, 'reset'])->name('password.reset');
    Route::post('/recovery/reset', [RecoveryController::class, 'update'])->name('password.update');
});

// Public documents: no login, the signed URL is the credential.
Route::middleware(['signed', 'throttle:30,1'])->prefix('p')->name('public.')->group(function () {
    Route::get('/contracts/{contract}', [PublicContractsController::class, 'show'])->name('contracts.show');
    Route::post('/contracts/{contract}', [PublicContractsController::class, 'sign'])->name('contracts.sign');
    Route::get('/invoices/{invoice}', [PublicDocumentsController::class, 'invoice'])->name('invoices.show');
    Route::post('/invoices/{invoice}/pay', [StripeController::class, 'checkout'])->name('invoices.pay');
    Route::post('/invoices/{invoice}/paypal', [PayPalController::class, 'checkout'])->name('invoices.paypal');
    Route::get('/subscriptions/{subscription}', [StripeController::class, 'showSubscription'])->name('subscriptions.show');
    Route::post('/subscriptions/{subscription}', [StripeController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/estimates/{estimate}', [PublicDocumentsController::class, 'estimate'])->name('estimates.show');
    Route::post('/estimates/{estimate}', [PublicDocumentsController::class, 'respondToEstimate'])->name('estimates.respond');
    Route::get('/proposals/{proposal}', [PublicDocumentsController::class, 'proposal'])->name('proposals.show');
    Route::post('/proposals/{proposal}', [PublicDocumentsController::class, 'respondToProposal'])->name('proposals.respond');
    Route::get('/surveys/{survey}', [PublicSurveysController::class, 'show'])->name('surveys.show');
    Route::post('/surveys/{survey}', [PublicSurveysController::class, 'respond'])->name('surveys.respond');
});

// PayPal sends the customer back here after approving; the order id is all it carries.
Route::get('/p/paypal/return', [PayPalController::class, 'return'])->middleware('throttle:30,1')->name('public.paypal.return');

// Public knowledge base: public articles only.
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/kb', [KnowledgeBaseController::class, 'index'])->name('kb');
    Route::get('/kb/{slug}', [KnowledgeBaseController::class, 'show'])->name('kb.show');
});

// Client portal: customer contacts on the "contact" guard, separate from staff.
Route::prefix('portal')->name('portal')->group(function () {
    Route::middleware('guest:contact')->group(function () {
        Route::get('/login', [PortalLoginController::class, 'index'])->name('.login');
        Route::post('/login', [PortalLoginController::class, 'perform'])->name('.login.perform');
    });

    Route::middleware('auth:contact')->group(function () {
        Route::get('/', [PortalController::class, 'index']);
        Route::post('/logout', [PortalLoginController::class, 'logout'])->name('.logout');
        Route::get('/tickets/create', [PortalController::class, 'createTicket'])->name('.tickets.create');
        Route::post('/tickets', [PortalController::class, 'storeTicket'])->name('.tickets.store');
        Route::get('/tickets/{ticket}', [PortalController::class, 'showTicket'])->name('.tickets.show');
        Route::post('/tickets/{ticket}/replies', [PortalController::class, 'replyToTicket'])->name('.tickets.replies.store');
    });
});

// Auth
Route::middleware('auth')->group(function () {
    // LogoutController
    Route::post('/logout', [LogoutController::class, 'perform'])->name('logout.perform');

    // DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CalendarController (filters its own events by permission)
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    // SearchController
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // AccountController
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/theme', [AccountController::class, 'theme'])->name('account.theme');
    Route::post('/account/tokens', [AccountController::class, 'createToken'])->name('account.tokens.create');
    Route::delete('/account/tokens/{token}', [AccountController::class, 'deleteToken'])->name('account.tokens.delete');

    // AboutController
    Route::get('/about', [AboutController::class, 'index'])->name('about');

    Route::middleware(CheckPermission::class . ':customers')->group(function () {
        // CustomersController
        Route::delete('customers/bulk-destroy', [CustomersController::class, 'bulkDestroy'])->name('customers.bulk-destroy');
        Route::resource('customers', CustomersController::class)->names([
            'index' => 'customers',
        ]);

        // ContactsController (nested under customers)
        Route::resource('customers.contacts', ContactsController::class)->names([
            'index' => 'customers.contacts',
            'create' => 'customers.contacts.create',
            'store' => 'customers.contacts.store',
            'show' => 'customers.contacts.show',
            'edit' => 'customers.contacts.edit',
            'update' => 'customers.contacts.update',
            'destroy' => 'customers.contacts.destroy',
        ]);

        // Customer file routes
        Route::post('/customers/{customer}/files', [CustomersController::class, 'uploadFiles'])->name('customers.files.upload');
        Route::get('/customers/{customer}/files/{file}/download', [CustomersController::class, 'downloadFile'])->name('customers.files.download');
        Route::delete('/customers/{customer}/files/{file}', [CustomersController::class, 'deleteFile'])->name('customers.files.delete');

        // Customer notes routes
        Route::post('/customers/{customer}/notes', [CustomerNotesController::class, 'store'])->name('customers.notes.store');
        Route::put('/customers/{customer}/notes/{note}', [CustomerNotesController::class, 'update'])->name('customers.notes.update');
        Route::delete('/customers/{customer}/notes/{note}', [CustomerNotesController::class, 'destroy'])->name('customers.notes.destroy');
    });

    Route::middleware(CheckPermission::class . ':projects')->group(function () {
        // ProjectsController
        Route::delete('projects/bulk-destroy', [ProjectsController::class, 'bulkDestroy'])->name('projects.bulk-destroy');
        Route::resource('projects', ProjectsController::class)->names([
            'index' => 'projects',
        ]);

        // MilestonesController (nested under projects)
        Route::resource('projects.milestones', MilestonesController::class)->names([
            'index' => 'projects.milestones',
            'create' => 'projects.milestones.create',
            'store' => 'projects.milestones.store',
            'show' => 'projects.milestones.show',
            'edit' => 'projects.milestones.edit',
            'update' => 'projects.milestones.update',
            'destroy' => 'projects.milestones.destroy',
        ]);

        // Project file routes
        Route::post('/projects/{project}/files', [ProjectsController::class, 'uploadFiles'])->name('projects.files.upload');
        Route::get('/projects/{project}/files/{file}/download', [ProjectsController::class, 'downloadFile'])->name('projects.files.download');
        Route::delete('/projects/{project}/files/{file}', [ProjectsController::class, 'deleteFile'])->name('projects.files.delete');
    });

    Route::middleware(CheckPermission::class . ':sales')->group(function () {
        // SalesController
        Route::delete('sales/bulk-destroy', [SalesController::class, 'bulkDestroy'])->name('sales.bulk-destroy');
        Route::get('sales/board', [SalesController::class, 'board'])->name('sales.board');
        Route::patch('sales/{sale}/stage', [SalesController::class, 'updateStage'])->name('sales.stage');
        Route::resource('sales', SalesController::class)->names([
            'index' => 'sales',
        ]);
        Route::post('/sales/{sale}/convert-to-contract', [SalesController::class, 'convertToContract'])->name('sales.convert-to-contract');
        Route::post('/sales/{sale}/convert-to-project', [SalesController::class, 'convertToProject'])->name('sales.convert-to-project');
    });

    Route::middleware(CheckPermission::class . ':contracts')->group(function () {
        // ContractsController
        Route::delete('contracts/bulk-destroy', [ContractsController::class, 'bulkDestroy'])->name('contracts.bulk-destroy');
        Route::resource('contracts', ContractsController::class)->names([
            'index' => 'contracts',
        ]);

        // Contract file routes
        Route::post('/contracts/{contract}/files', [ContractsController::class, 'uploadFiles'])->name('contracts.files.upload');
        Route::post('/contracts/{contract}/email', [DocumentEmailsController::class, 'contract'])->name('contracts.email');
        Route::get('/contracts/{contract}/files/{file}/download', [ContractsController::class, 'downloadFile'])->name('contracts.files.download');
        Route::delete('/contracts/{contract}/files/{file}', [ContractsController::class, 'deleteFile'])->name('contracts.files.delete');
    });

    Route::middleware(CheckPermission::class . ':invoices')->group(function () {
        // InvoicesController
        Route::delete('invoices/bulk-destroy', [InvoicesController::class, 'bulkDestroy'])->name('invoices.bulk-destroy');
        Route::resource('invoices', InvoicesController::class)->names([
            'index' => 'invoices',
        ]);

        Route::get('/invoices/{invoice}/print', [InvoicesController::class, 'print'])->name('invoices.print');
        Route::post('/invoices/{invoice}/email', [DocumentEmailsController::class, 'invoice'])->name('invoices.email');

        // PaymentsController (nested under invoices)
        Route::scopeBindings()->group(function () {
            Route::post('/invoices/{invoice}/payments', [PaymentsController::class, 'store'])->name('invoices.payments.store');
            Route::delete('/invoices/{invoice}/payments/{payment}', [PaymentsController::class, 'destroy'])->name('invoices.payments.destroy');
        });
    });

    Route::middleware(CheckPermission::class . ':estimates')->group(function () {
        // EstimatesController
        Route::delete('estimates/bulk-destroy', [EstimatesController::class, 'bulkDestroy'])->name('estimates.bulk-destroy');
        Route::resource('estimates', EstimatesController::class)->names([
            'index' => 'estimates',
        ]);
        Route::get('/estimates/{estimate}/print', [EstimatesController::class, 'print'])->name('estimates.print');
        Route::post('/estimates/{estimate}/email', [DocumentEmailsController::class, 'estimate'])->name('estimates.email');
        Route::post('/estimates/{estimate}/convert-to-invoice', [EstimatesController::class, 'convertToInvoice'])->name('estimates.convert-to-invoice');
    });

    Route::middleware(CheckPermission::class . ':subscriptions')->group(function () {
        // SubscriptionsController
        Route::post('/subscriptions/{subscription}/cancel', [SubscriptionsController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('/subscriptions/{subscription}/email', [DocumentEmailsController::class, 'subscription'])->name('subscriptions.email');
        Route::resource('subscriptions', SubscriptionsController::class)->names([
            'index' => 'subscriptions',
        ]);
    });

    Route::middleware(CheckPermission::class . ':proposals')->group(function () {
        // ProposalsController
        Route::delete('proposals/bulk-destroy', [ProposalsController::class, 'bulkDestroy'])->name('proposals.bulk-destroy');
        Route::resource('proposals', ProposalsController::class)->names([
            'index' => 'proposals',
        ]);
        Route::get('/proposals/{proposal}/print', [ProposalsController::class, 'print'])->name('proposals.print');
        Route::post('/proposals/{proposal}/email', [DocumentEmailsController::class, 'proposal'])->name('proposals.email');
        Route::post('/proposals/{proposal}/convert-to-estimate', [ProposalsController::class, 'convertToEstimate'])->name('proposals.convert-to-estimate');
    });

    Route::middleware(CheckPermission::class . ':items')->group(function () {
        // ItemsController
        Route::resource('items', ItemsController::class)->except('show')->names([
            'index' => 'items',
        ]);
    });

    Route::middleware(CheckPermission::class . ':tasks')->group(function () {
        // TasksController
        Route::delete('tasks/bulk-destroy', [TasksController::class, 'bulkDestroy'])->name('tasks.bulk-destroy');
        Route::resource('tasks', TasksController::class)->names([
            'index' => 'tasks',
        ]);

        // TimeEntriesController (nested under tasks)
        Route::scopeBindings()->group(function () {
            Route::post('/tasks/{task}/timer', [TimeEntriesController::class, 'start'])->name('tasks.timer.start');
            Route::post('/tasks/{task}/timer/stop', [TimeEntriesController::class, 'stop'])->name('tasks.timer.stop');
            Route::post('/tasks/{task}/time-entries', [TimeEntriesController::class, 'store'])->name('tasks.time-entries.store');
            Route::delete('/tasks/{task}/time-entries/{timeEntry}', [TimeEntriesController::class, 'destroy'])->name('tasks.time-entries.destroy');
        });
    });

    Route::middleware(CheckPermission::class . ':tickets')->group(function () {
        // TicketsController
        Route::delete('tickets/bulk-destroy', [TicketsController::class, 'bulkDestroy'])->name('tickets.bulk-destroy');
        Route::resource('tickets', TicketsController::class)->except('edit')->names([
            'index' => 'tickets',
        ]);
        Route::post('/tickets/{ticket}/replies', [TicketRepliesController::class, 'store'])->name('tickets.replies.store');
    });

    Route::middleware(CheckPermission::class . ':credit_notes')->group(function () {
        // CreditNotesController
        Route::delete('credit-notes/bulk-destroy', [CreditNotesController::class, 'bulkDestroy'])->name('credit-notes.bulk-destroy');
        Route::resource('credit-notes', CreditNotesController::class)->parameters(['credit-notes' => 'creditNote'])->names([
            'index' => 'credit-notes',
        ]);
        Route::get('/credit-notes/{creditNote}/print', [CreditNotesController::class, 'print'])->name('credit-notes.print');
        Route::post('/credit-notes/{creditNote}/invoice', [CreditNotesController::class, 'apply'])->name('credit-notes.apply');
        Route::delete('/credit-notes/{creditNote}/invoice', [CreditNotesController::class, 'unapply'])->name('credit-notes.unapply');
    });

    Route::middleware(CheckPermission::class . ':expenses')->group(function () {
        // ExpensesController
        Route::delete('expenses/bulk-destroy', [ExpensesController::class, 'bulkDestroy'])->name('expenses.bulk-destroy');
        Route::resource('expenses', ExpensesController::class)->except('show')->names([
            'index' => 'expenses',
        ]);
        Route::post('/expenses/{expense}/bill', [ExpensesController::class, 'bill'])->name('expenses.bill');
    });

    Route::middleware(CheckPermission::class . ':articles')->group(function () {
        // ArticlesController
        Route::resource('articles', ArticlesController::class)->names([
            'index' => 'articles',
        ]);
    });

    Route::middleware(CheckPermission::class . ':surveys')->group(function () {
        // SurveysController
        Route::resource('surveys', SurveysController::class)->names([
            'index' => 'surveys',
        ]);
    });

    // ReportsController
    Route::middleware(CheckPermission::class . ':reports')->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('/reports/payments.csv', [ReportsController::class, 'paymentsCsv'])->name('reports.payments-csv');
        Route::resource('goals', GoalsController::class)->except('show')->names([
            'index' => 'goals',
        ]);
    });

    // Setup routes (Admin only)
    Route::middleware(AdminMiddleware::class)->prefix('setup')->group(function () {
        // SettingsController (Localization)
        Route::get('/localization', [SettingsController::class, 'index'])->name('setup.localization');
        Route::put('/localization', [SettingsController::class, 'update'])->name('setup.localization.update');
        Route::get('/billing', [SettingsController::class, 'billing'])->name('setup.billing');
        Route::put('/billing', [SettingsController::class, 'updateBilling'])->name('setup.billing.update');
        // AnnouncementsController
        Route::resource('announcements', AnnouncementsController::class)->except('show')->names([
            'index' => 'setup.announcements',
            'create' => 'setup.announcements.create',
            'store' => 'setup.announcements.store',
            'edit' => 'setup.announcements.edit',
            'update' => 'setup.announcements.update',
            'destroy' => 'setup.announcements.destroy',
        ]);
        // ActivityController
        Route::get('/activity', [ActivityController::class, 'index'])->name('setup.activity');
        // CustomFieldsController
        Route::resource('custom-fields', CustomFieldsController::class)->except('show')
            ->parameters(['custom-fields' => 'customField'])->names([
                'index' => 'setup.custom-fields',
                'create' => 'setup.custom-fields.create',
                'store' => 'setup.custom-fields.store',
                'edit' => 'setup.custom-fields.edit',
                'update' => 'setup.custom-fields.update',
                'destroy' => 'setup.custom-fields.destroy',
            ]);
        // DepartmentsController
        Route::resource('departments', DepartmentsController::class)->except('show')->names([
            'index' => 'setup.departments',
            'create' => 'setup.departments.create',
            'store' => 'setup.departments.store',
            'edit' => 'setup.departments.edit',
            'update' => 'setup.departments.update',
            'destroy' => 'setup.departments.destroy',
        ]);
        // UsersController
        Route::delete('users/bulk-destroy', [UsersController::class, 'bulkDestroy'])->name('setup.users.bulk-destroy');
        // Users are created through the modal on the index page, so there is no
        // create screen and no UsersController::create() to route to.
        Route::resource('users', UsersController::class)->except(['show', 'create'])->names([
            'index' => 'setup.users',
            'store' => 'setup.users.store',
            'edit' => 'setup.users.edit',
            'update' => 'setup.users.update',
            'destroy' => 'setup.users.destroy',
        ]);
    });
});
