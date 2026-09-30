<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\{
    ActivityLogsController,
    ApplicationController,
    LoginController,
    LoginHistoryController,
    MemberController,
    RoleController,
    RolePermissionController,
    PermissionController,
    TeamsController,
    UserRoleController,
    UserController,
};

Route::middleware(['web'])->name('web.')->group(function () {
    // Auth
    Route::get('/signin', [LoginController::class, 'signin'])->name('signin');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Checking Session
    Route::middleware(['check.session'])->group(function () {
        // Default
        Route::get('/', [LoginController::class, 'index'])->name('index');

        // Data
        Route::get('/application/data', [ApplicationController::class, 'data'])->name('application.data')->middleware('permission:security.application');
        Route::get('/role/data', [RoleController::class, 'data'])->name('role.data')->middleware('permission:security.role');
        Route::get('/permission/data', [PermissionController::class, 'data'])->name('permission.data')->middleware('permission:security.permission');
        Route::get('/role-permission/data', [RolePermissionController::class, 'data'])->name('role-permission.data')->middleware('permission:security.role_permission');
        Route::get('/user/data', [UserController::class, 'data'])->name('user.data')->middleware('permission:security.user');
        Route::get('/user-role/data', [UserRoleController::class, 'data'])->name('user-role.data')->middleware('permission:security.user_role');
        Route::get('/login-history/data', [LoginHistoryController::class, 'data'])->name('login-history.data')->middleware('permission:monitoring.login_history');
        Route::get('/activity-logs/data', [ActivityLogsController::class, 'data'])->name('activity-logs.data')->middleware('permission:monitoring.activity_logs');
        Route::get('/teams/data', [TeamsController::class, 'data'])->name('teams.data')->middleware('permission:security.teams');
        Route::get('/member/data', [MemberController::class, 'data'])->name('member.data')->middleware('permission:security.member');

        // Custom
        Route::get('/role-permission/select', [RolePermissionController::class, 'getRolePermission'])->name('role-permission.select')->middleware('permission:security.role_permission');
        Route::get('/login-history', [LoginHistoryController::class, 'index'])->name('login-history.index')->middleware('permission:monitoring.login_history');
        Route::get('/activity-logs', [ActivityLogsController::class, 'index'])->name('activity-logs.index')->middleware('permission:monitoring.activity_logs');

        // Resources
        Route::resource('application', ApplicationController::class)->middleware('permission:security.application');
        Route::resource('role', RoleController::class)->middleware('permission:security.role');
        Route::resource('permission', PermissionController::class)->middleware('permission:security.permission');
        Route::resource('role-permission', RolePermissionController::class)->middleware('permission:security.role_permission');
        Route::resource('user-role', UserRoleController::class)->middleware('permission:security.user_role');
        Route::resource('user', UserController::class)->middleware('permission:security.user');
        Route::resource('teams', TeamsController::class)->middleware('permission:security.teams');
        Route::resource('member', MemberController::class)->middleware('permission:security.member');
    });

});
