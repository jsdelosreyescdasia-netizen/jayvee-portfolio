<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PublicStorageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::post('/contact-us', [ContactMessageController::class, 'store'])->name('contact.store');
Route::get('/cms-storage/{path}', PublicStorageController::class)->where('path', '.*')->name('storage.public');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::prefix('admin')->name('admin.')->middleware('admin.session')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SiteSettingController::class, 'update'])->name('settings.update');
    Route::resource('messages', AdminContactMessageController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::resource('pages', PageController::class)->only(['index', 'edit', 'update']);
    Route::resource('items', ContentItemController::class)->except(['show']);
});

Route::get('/{slug}', [PublicSiteController::class, 'page'])->name('page');
