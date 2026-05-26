<?php

use Illuminate\Support\Facades\Route;
use YellowThree\VoyagerMenu\Http\Controllers\MenuController;

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin.user'],
    'as' => 'voyager.',
], function () {
    Route::get('/menus', fn() => \Livewire\Livewire::mount('menu-list'))->name('menus.index');
    Route::get('/menus/create', fn() => \Livewire\Livewire::mount('menu-builder'))->name('menus.create');
    Route::get('/menus/{id}/edit', fn(int $id) => \Livewire\Livewire::mount('menu-builder', ['id' => $id]))->name('menus.edit');

    Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
    Route::put('/menus/{id}', [MenuController::class, 'update'])->name('menus.update');
    Route::delete('/menus/{id}', [MenuController::class, 'destroy'])->name('menus.destroy');
});
