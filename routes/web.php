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
use App\Http\Controllers\CustomerNotesController;
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

    // SalesController
    Route::delete('sales/bulk-destroy', [SalesController::class, 'bulkDestroy'])->name('sales.bulk-destroy');
    Route::resource('sales', SalesController::class)->names([
        'index' => 'sales',
    ]);
    Route::post('/sales/{sale}/convert-to-contract', [SalesController::class, 'convertToContract'])->name('sales.convert-to-contract');
    Route::post('/sales/{sale}/convert-to-project', [SalesController::class, 'convertToProject'])->name('sales.convert-to-project');

    // ContractsController
    Route::delete('contracts/bulk-destroy', [ContractsController::class, 'bulkDestroy'])->name('contracts.bulk-destroy');
    Route::resource('contracts', ContractsController::class)->names([
        'index' => 'contracts',
    ]);

    // Contract file routes
    Route::post('/contracts/{contract}/files', [ContractsController::class, 'uploadFiles'])->name('contracts.files.upload');
    Route::get('/contracts/{contract}/files/{file}/download', [ContractsController::class, 'downloadFile'])->name('contracts.files.download');
    Route::delete('/contracts/{contract}/files/{file}', [ContractsController::class, 'deleteFile'])->name('contracts.files.delete');

    // Setup routes (Admin only)
    Route::middleware(AdminMiddleware::class)->prefix('setup')->group(function () {
        // SettingsController (Localization)
        Route::get('/localization', [SettingsController::class, 'index'])->name('setup.localization');
        Route::put('/localization', [SettingsController::class, 'update'])->name('setup.localization.update');
        // UsersController
        Route::delete('users/bulk-destroy', [UsersController::class, 'bulkDestroy'])->name('setup.users.bulk-destroy');
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
