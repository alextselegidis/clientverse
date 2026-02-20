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

use App\Http\Controllers\Api\V1\ContactsApiV1Controller;
use App\Http\Controllers\Api\V1\ContractsApiV1Controller;
use App\Http\Controllers\Api\V1\CustomerNotesApiV1Controller;
use App\Http\Controllers\Api\V1\CustomersApiV1Controller;
use App\Http\Controllers\Api\V1\MeApiV1Controller;
use App\Http\Controllers\Api\V1\MilestonesApiV1Controller;
use App\Http\Controllers\Api\V1\ProjectsApiV1Controller;
use App\Http\Controllers\Api\V1\SalesApiV1Controller;
use App\Http\Controllers\Api\V1\TagsApiV1Controller;
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

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Current user endpoint
    Route::get('/me', MeApiV1Controller::class);

    // Orion resource routes
    Orion::resource('customers', CustomersApiV1Controller::class);
    Orion::resource('contacts', ContactsApiV1Controller::class);
    Orion::resource('projects', ProjectsApiV1Controller::class);
    Orion::resource('sales', SalesApiV1Controller::class);
    Orion::resource('contracts', ContractsApiV1Controller::class);
    Orion::resource('milestones', MilestonesApiV1Controller::class);
    Orion::resource('tags', TagsApiV1Controller::class);
    Orion::resource('customer-notes', CustomerNotesApiV1Controller::class);
});
