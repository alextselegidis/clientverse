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

use App\Http\Controllers\Api\V1\ArticlesApiV1Controller;
use App\Http\Controllers\Api\V1\ContactsApiV1Controller;
use App\Http\Controllers\Api\V1\ContractsApiV1Controller;
use App\Http\Controllers\Api\V1\CreditNotesApiV1Controller;
use App\Http\Controllers\Api\V1\CustomerNotesApiV1Controller;
use App\Http\Controllers\Api\V1\CustomersApiV1Controller;
use App\Http\Controllers\Api\V1\EstimatesApiV1Controller;
use App\Http\Controllers\Api\V1\ExpensesApiV1Controller;
use App\Http\Controllers\Api\V1\InvoicesApiV1Controller;
use App\Http\Controllers\Api\V1\ItemsApiV1Controller;
use App\Http\Controllers\Api\V1\MeApiV1Controller;
use App\Http\Controllers\Api\V1\MilestonesApiV1Controller;
use App\Http\Controllers\Api\V1\PaymentsApiV1Controller;
use App\Http\Controllers\Api\V1\ProjectsApiV1Controller;
use App\Http\Controllers\Api\V1\ProposalsApiV1Controller;
use App\Http\Controllers\Api\V1\SalesApiV1Controller;
use App\Http\Controllers\Api\V1\SubscriptionsApiV1Controller;
use App\Http\Controllers\Api\V1\TagsApiV1Controller;
use App\Http\Controllers\Api\V1\TasksApiV1Controller;
use App\Http\Controllers\Api\V1\TicketsApiV1Controller;
use App\Http\Controllers\Public\StripeController;
use App\Http\Middleware\CheckApiIncludes;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::prefix('v1')->as('api.v1.')->middleware(['auth:sanctum', CheckApiIncludes::class])->group(function () {
    // Current user endpoint
    Route::get('/me', MeApiV1Controller::class);

    // Orion resource routes
    Orion::resource('customers', CustomersApiV1Controller::class)->middleware(CheckPermission::class . ':customers');
    Orion::resource('contacts', ContactsApiV1Controller::class)->middleware(CheckPermission::class . ':customers');
    Orion::resource('projects', ProjectsApiV1Controller::class)->middleware(CheckPermission::class . ':projects');
    Orion::resource('sales', SalesApiV1Controller::class)->middleware(CheckPermission::class . ':sales');
    Orion::resource('contracts', ContractsApiV1Controller::class)->middleware(CheckPermission::class . ':contracts');
    Orion::resource('milestones', MilestonesApiV1Controller::class)->middleware(CheckPermission::class . ':projects');
    Orion::resource('tags', TagsApiV1Controller::class)->middleware(CheckPermission::class . ':customers');
    Orion::resource('customer-notes', CustomerNotesApiV1Controller::class)->middleware(CheckPermission::class . ':customers');
    Orion::resource('tasks', TasksApiV1Controller::class)->middleware(CheckPermission::class . ':tasks');
    Orion::resource('tickets', TicketsApiV1Controller::class)->withoutBatch()->middleware(CheckPermission::class . ':tickets');
    Orion::resource('items', ItemsApiV1Controller::class)->middleware(CheckPermission::class . ':items');
    Orion::resource('expenses', ExpensesApiV1Controller::class)->middleware(CheckPermission::class . ':expenses');
    Orion::resource('articles', ArticlesApiV1Controller::class)->middleware(CheckPermission::class . ':articles');

    // Money documents are read only: their totals, balances and numbers are kept by the web pages.
    $readOnly = ['index', 'search', 'show'];
    Orion::resource('invoices', InvoicesApiV1Controller::class)->only($readOnly)->middleware(CheckPermission::class . ':invoices');
    Orion::resource('payments', PaymentsApiV1Controller::class)->only($readOnly)->middleware(CheckPermission::class . ':invoices');
    Orion::resource('estimates', EstimatesApiV1Controller::class)->only($readOnly)->middleware(CheckPermission::class . ':estimates');
    Orion::resource('credit-notes', CreditNotesApiV1Controller::class)->only($readOnly)->middleware(CheckPermission::class . ':credit_notes');
    Orion::resource('proposals', ProposalsApiV1Controller::class)->only($readOnly)->middleware(CheckPermission::class . ':proposals');
    Orion::resource('subscriptions', SubscriptionsApiV1Controller::class)->only($readOnly)->middleware(CheckPermission::class . ':subscriptions');
});

// Stripe's payment notifications: no token, the Stripe-Signature header is the credential.
Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('api.stripe.webhook');
