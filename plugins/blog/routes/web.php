<?php

use Illuminate\Support\Facades\Route;
use YellowThree\VoyagerBlog\Http\Controllers\PostController;
use YellowThree\VoyagerBlog\Http\Controllers\PageController;
use YellowThree\VoyagerBlog\Http\Controllers\CategoryController;

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin.user'],
    'as' => 'voyager.',
], function () {
    // Posts — Livewire UI
    Route::get('/posts', fn() => \Livewire\Livewire::mount('blog-post-list'))->name('posts.index');
    Route::get('/posts/create', fn() => \Livewire\Livewire::mount('blog-post-form'))->name('posts.create');
    Route::get('/posts/{id}/edit', fn(int $id) => \Livewire\Livewire::mount('blog-post-form', ['id' => $id]))->name('posts.edit');
    // Posts — API endpoints
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Pages — Livewire UI
    Route::get('/pages', fn() => \Livewire\Livewire::mount('blog-page-list'))->name('pages.index');
    Route::get('/pages/create', fn() => \Livewire\Livewire::mount('blog-page-form'))->name('pages.create');
    Route::get('/pages/{id}/edit', fn(int $id) => \Livewire\Livewire::mount('blog-page-form', ['id' => $id]))->name('pages.edit');
    // Pages — API endpoints
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
    Route::put('/pages/{id}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{id}', [PageController::class, 'destroy'])->name('pages.destroy');

    // Categories — Livewire UI
    Route::get('/categories', fn() => \Livewire\Livewire::mount('blog-category-list'))->name('categories.index');
    Route::get('/categories/create', fn() => \Livewire\Livewire::mount('blog-category-form'))->name('categories.create');
    Route::get('/categories/{id}/edit', fn(int $id) => \Livewire\Livewire::mount('blog-category-form', ['id' => $id]))->name('categories.edit');
    // Categories — API endpoints
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});
