<?php

use Illuminate\Support\Facades\Route;
use YellowThree\VoyagerMenu\Http\Controllers\MenuController;
use YellowThree\VoyagerMenu\Http\Livewire\MenuList;
use YellowThree\VoyagerMenu\Http\Livewire\MenuBuilder;

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin.user'],
    'as' => 'voyager.',
], function () {
    // Menus — Livewire UI
    Route::get('/menus', MenuList::class)->name('menus.index');
    Route::get('/menus/create', MenuBuilder::class)->name('menus.create');
    Route::get('/menus/{id}/edit', MenuBuilder::class)->name('menus.edit');
    // Menus — API endpoints
    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::put('/menus/{id}', [MenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{id}', [MenuController::class, 'destroy'])->name('menus.destroy');
});
