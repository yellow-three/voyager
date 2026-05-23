<?php

use Illuminate\Support\Facades\Route;
use YellowThree\VoyagerBlog\Http\Controllers\PostController;
use YellowThree\VoyagerBlog\Http\Controllers\PageController;
use YellowThree\VoyagerBlog\Http\Controllers\CategoryController;
use YellowThree\VoyagerBlog\Http\Livewire\PostList;
use YellowThree\VoyagerBlog\Http\Livewire\PostForm;
use YellowThree\VoyagerBlog\Http\Livewire\PageList;
use YellowThree\VoyagerBlog\Http\Livewire\PageForm;
use YellowThree\VoyagerBlog\Http\Livewire\CategoryList;
use YellowThree\VoyagerBlog\Http\Livewire\CategoryForm;

Route::group([
    'prefix' => 'admin',
    'middleware' => ['web', 'admin.user'],
    'as' => 'voyager.',
], function () {
    // Posts — Livewire UI
    Route::get('/posts', PostList::class)->name('posts.index');
    Route::get('/posts/create', PostForm::class)->name('posts.create');
    Route::get('/posts/{id}/edit', PostForm::class)->name('posts.edit');
    // Posts — API endpoints
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Pages — Livewire UI
    Route::get('/pages', PageList::class)->name('pages.index');
    Route::get('/pages/create', PageForm::class)->name('pages.create');
    Route::get('/pages/{id}/edit', PageForm::class)->name('pages.edit');
    // Pages — API endpoints
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
    Route::put('/pages/{id}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{id}', [PageController::class, 'destroy'])->name('pages.destroy');

    // Categories — Livewire UI
    Route::get('/categories', CategoryList::class)->name('categories.index');
    Route::get('/categories/create', CategoryForm::class)->name('categories.create');
    Route::get('/categories/{id}/edit', CategoryForm::class)->name('categories.edit');
    // Categories — API endpoints
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
});
