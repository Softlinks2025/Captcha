<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WithdrawalRequestController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\AgentPlanController;

// Admin Panel Routes

Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::redirect('/', '/admin/dashboard');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::resource('withdrawal-requests', WithdrawalRequestController::class);
        Route::get('withdrawal-requests/export/csv', [WithdrawalRequestController::class, 'exportToCsv'])->name('withdrawal-requests.export-csv');
        Route::resource('subscription_plans', SubscriptionPlanController::class);
        
        // Joining Fees Routes
        Route::post('agent-plans/joining-fees', [\App\Http\Controllers\Admin\AgentPlanController::class, 'storeJoiningFee'])
        ->name('admin.agent-plans.joining-fees.store');
        Route::put('agent-plans/joining-fees/{id}', [\App\Http\Controllers\Admin\AgentPlanController::class, 'updateJoiningFee'])
        ->name('admin.agent-plans.joining-fees.update');
        Route::delete('agent-plans/joining-fees/{id}', [\App\Http\Controllers\Admin\AgentPlanController::class, 'destroyJoiningFee'])
        ->name('admin.agent-plans.joining-fees.destroy');

            
        // Agent Plans Resource Route
        Route::resource('agent-plans', AgentPlanController::class)->parameters([
            'agent-plans' => 'plan'
        ]);

        Route::get('users/export-csv', [UserController::class, 'exportCsv'])->name('users.export-csv');
        Route::resource('users', UserController::class);
        Route::get('agents/export-csv', [AgentController::class, 'exportCsv'])->name('agents.export-csv');
        Route::resource('agents', AgentController::class);
        Route::get('tickets/export-csv', [TicketController::class, 'exportCsv'])->name('tickets.export-csv');
        Route::get('/all-withdrawal-requests', [AdminController::class, 'allWithdrawalRequests'])->name('all-withdrawal-requests');
        Route::post('agent-withdrawal-requests/{id}/approve', [\App\Http\Controllers\Admin\AgentWithdrawalRequestController::class, 'approve'])->name('agent-withdrawal-requests.approve');
        Route::post('agent-withdrawal-requests/{id}/decline', [\App\Http\Controllers\Admin\AgentWithdrawalRequestController::class, 'decline'])->name('agent-withdrawal-requests.decline');
        Route::get('subscription_plans', [SubscriptionPlanController::class, 'index'])->name('subscription_plans.index');
        Route::get('subscription_plans/{subscription_plan}', [SubscriptionPlanController::class, 'show'])->name('subscription_plans.show');
        Route::get('subscription_plans/create', [SubscriptionPlanController::class, 'create'])->name('subscription_plans.create');
        Route::get('subscription_plans/{subscription_plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('subscription_plans.edit');
        Route::delete('subscription_plans/{subscription_plan}', [SubscriptionPlanController::class, 'destroy'])->name('subscription_plans.destroy');
        Route::get('push-notification', [\App\Http\Controllers\Admin\PushNotificationController::class, 'index'])->name('push-notification.index');
        Route::post('push-notification/send', [\App\Http\Controllers\Admin\PushNotificationController::class, 'send'])->name('push-notification.send');
        Route::get('push-notification/search-users', [\App\Http\Controllers\Admin\PushNotificationController::class, 'searchUsers'])->name('push-notification.search-users');
        Route::get('push-notification/search-agents', [\App\Http\Controllers\Admin\PushNotificationController::class, 'searchAgents'])->name('push-notification.search-agents');
        Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::put('tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
        Route::get('agent-plan-purchase-requests', [\App\Http\Controllers\Admin\AgentPlanPurchaseRequestController::class, 'index'])->name('agent-plan-purchase-requests.index');
        Route::post('agent-plan-purchase-requests/{id}/approve', [\App\Http\Controllers\Admin\AgentPlanPurchaseRequestController::class, 'approve'])->name('agent-plan-purchase-requests.approve');
        Route::post('agent-plan-purchase-requests/{id}/reject', [\App\Http\Controllers\Admin\AgentPlanPurchaseRequestController::class, 'reject'])->name('agent-plan-purchase-requests.reject');
        Route::resource('subadmins', \App\Http\Controllers\SubadminController::class)->only(['index', 'create', 'store', 'edit', 'update']);
        Route::delete('subadmins/{id}', [\App\Http\Controllers\SubadminController::class, 'destroy'])->name('subadmins.destroy');
        Route::get('subadmins/{id}/change-password', [\App\Http\Controllers\SubadminController::class, 'showChangePasswordForm'])->name('subadmins.change-password');
        Route::post('subadmins/{id}/change-password', [\App\Http\Controllers\SubadminController::class, 'updatePassword'])->name('subadmins.change-password.update');
    }); 