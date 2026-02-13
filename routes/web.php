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

use App\Http\Controllers\AboutController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\ContractsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MilestonesController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RecoveryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Route;

// Guest
Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    // WelcomeController
    Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
    // LoginController
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'perform'])->name('login.perform');
    // RecoveryController
    Route::get('/recovery', [RecoveryController::class, 'index'])->name('recovery');
    Route::post('/recovery', [RecoveryController::class, 'perform'])->name('recovery.perform');
});

// Auth
Route::middleware('auth')->group(function () {
    // LogoutController
    Route::post('/logout', [LogoutController::class, 'perform'])->name('logout.perform');

    // DashboardController
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // AccountController
    Route::get('/account', [AccountController::class, 'index'])->name('account');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');

    // AboutController
    Route::get('/about', [AboutController::class, 'index'])->name('about');

    // CustomersController
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

    // ProjectsController
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

    // SalesController
    Route::resource('sales', SalesController::class)->names([
        'index' => 'sales',
    ]);
    Route::post('/sales/{sale}/convert-to-contract', [SalesController::class, 'convertToContract'])->name('sales.convert-to-contract');
    Route::post('/sales/{sale}/convert-to-project', [SalesController::class, 'convertToProject'])->name('sales.convert-to-project');

    // ContractsController
    Route::resource('contracts', ContractsController::class)->names([
        'index' => 'contracts',
    ]);

    // Setup routes (Admin only)
    Route::middleware(AdminMiddleware::class)->prefix('setup')->group(function () {
        // SettingsController (Localization)
        Route::get('/localization', [SettingsController::class, 'index'])->name('setup.localization');
        Route::put('/localization', [SettingsController::class, 'update'])->name('setup.localization.update');
        // UsersController
        Route::resource('users', UsersController::class)->except(['show'])->names([
            'index' => 'setup.users',
            'create' => 'setup.users.create',
            'store' => 'setup.users.store',
            'edit' => 'setup.users.edit',
            'update' => 'setup.users.update',
            'destroy' => 'setup.users.destroy',
        ]);
    });
});
