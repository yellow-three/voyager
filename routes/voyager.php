<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use YellowThree\Voyager\Events\Routing;
use YellowThree\Voyager\Events\RoutingAdmin;
use YellowThree\Voyager\Events\RoutingAdminAfter;
use YellowThree\Voyager\Events\RoutingAfter;
use YellowThree\Voyager\Facades\Voyager;
use YellowThree\Voyager\Http\Controllers\VoyagerAuthController;
use YellowThree\Voyager\Http\Controllers\VoyagerController;
use YellowThree\Voyager\Http\Controllers\VoyagerBaseController;
use YellowThree\Voyager\Http\Controllers\VoyagerBreadController;
use YellowThree\Voyager\Http\Controllers\VoyagerDatabaseController;
use YellowThree\Voyager\Http\Controllers\VoyagerMenuController;
use YellowThree\Voyager\Http\Controllers\VoyagerMediaController;
use YellowThree\Voyager\Http\Controllers\VoyagerSettingsController;
use YellowThree\Voyager\Http\Controllers\VoyagerCompassController;
use YellowThree\Voyager\Http\Controllers\VoyagerUserController;
use YellowThree\Voyager\Http\Controllers\VoyagerRoleController;
use YellowThree\Voyager\Http\Controllers\ActivityLogController;
use YellowThree\Voyager\Http\Controllers\UpgradeController;

/*
|--------------------------------------------------------------------------
| Voyager Routes
|--------------------------------------------------------------------------
|
| This file defines all Voyager admin routes.
| - UI routes (GET) → Controllers render Blade views with <livewire:voyager::⚡component />
| - API routes (POST/PUT/DELETE) → Controllers handle CRUD + AJAX operations
| - BREAD routes → Dynamic per DataType, generated from database
|
| Route naming: All routes prefixed with 'voyager.' (e.g., 'voyager.dashboard')
|
*/

Route::group(['as' => 'voyager.'], function () {
    event(new Routing());

    // =========================================================================
    // 1. AUTH ROUTES (no admin.user middleware)
    // =========================================================================

    // GET / POST login – Livewire-backed SFC: voyager::⚡login
    Route::get('login', [VoyagerAuthController::class, 'login'])->name('login');
    Route::post('login', [VoyagerAuthController::class, 'postLogin'])->name('postlogin');

    // =========================================================================
    // 2. ASSET ROUTE (no admin.user middleware)
    // =========================================================================
    Route::get('voyager-assets', [VoyagerController::class, 'assets'])->name('voyager_assets');

    // =========================================================================
    // 3. ADMIN-PROTECTED ROUTES (middleware: admin.user)
    // =========================================================================
    Route::group(['middleware' => 'admin.user'], function () {
        event(new RoutingAdmin());

        // --------------------------------------------------------------------
        // 3a. DASHBOARD, LOGOUT, UPLOAD
        //     Dashboard UI → voyager::⚡dashboard (SFC)
        // --------------------------------------------------------------------
        Route::get('/', [VoyagerController::class, 'index'])->name('dashboard');
        Route::post('logout', [VoyagerController::class, 'logout'])->name('logout');
        Route::post('upload', [VoyagerController::class, 'upload'])->name('upload');

        // --------------------------------------------------------------------
        // 3b. PROFILE
        //     UI → voyager::⚡profile (SFC)
        // --------------------------------------------------------------------
        Route::get('profile', [VoyagerUserController::class, 'profile'])->name('profile');

        // --------------------------------------------------------------------
        // 3c. USERS & ROLES (Livewire-backed CRUD)
        //     List → voyager::⚡user-list / ⚡role-list (SFC)
        //     Form → voyager::⚡user-form  / ⚡role-form  (SFC)
        // --------------------------------------------------------------------
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [VoyagerUserController::class, 'index'])->name('users.index');
            Route::get('{id}/edit', [VoyagerUserController::class, 'edit'])->name('users.edit');
            Route::put('{id}', [VoyagerUserController::class, 'update'])->name('users.update');
            Route::post('/', [VoyagerUserController::class, 'store'])->name('users.store');
        });

        Route::group(['prefix' => 'roles'], function () {
            Route::get('/', [VoyagerRoleController::class, 'index'])->name('roles.index');
            Route::get('create', [VoyagerRoleController::class, 'create'])->name('roles.create');
            Route::get('{id}/edit', [VoyagerRoleController::class, 'edit'])->name('roles.edit');
            Route::post('/', [VoyagerRoleController::class, 'store'])->name('roles.store');
            Route::put('{id}', [VoyagerRoleController::class, 'update'])->name('roles.update');
        });

        // --------------------------------------------------------------------
        // 3d. BREAD DYNAMIC ROUTES
        //     Automatically generated from DataType records.
        //     Each DataType slug gets full CRUD + order/action/restore/relation.
        //     UI (browse, read, edit, add, order) → Controller-rendered Blade views
        //     API (store, update, destroy, action, remove_media) → Controller methods
        // --------------------------------------------------------------------
        try {
            foreach (Voyager::model('DataType')::all() as $dataType) {
                $breadController = $dataType->controller
                    ? '\\' . ltrim($dataType->controller, '\\')
                    : VoyagerBaseController::class;

                Route::get($dataType->slug . '/order', [$breadController, 'order'])->name($dataType->slug . '.order');
                Route::post($dataType->slug . '/action', [$breadController, 'action'])->name($dataType->slug . '.action');
                Route::post($dataType->slug . '/order', [$breadController, 'update_order'])->name($dataType->slug . '.update_order');
                Route::get($dataType->slug . '/{id}/restore', [$breadController, 'restore'])->name($dataType->slug . '.restore');
                Route::get($dataType->slug . '/relation', [$breadController, 'relation'])->name($dataType->slug . '.relation');
                Route::post($dataType->slug . '/remove', [$breadController, 'remove_media'])->name($dataType->slug . '.media.remove');
                Route::resource($dataType->slug, $breadController, ['parameters' => [$dataType->slug => 'id']]);
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Custom routes hasn't been configured because: " . $e->getMessage(), 1);
        } catch (\Exception $e) {
            // do nothing, might just be because table not yet migrated.
        }

        // --------------------------------------------------------------------
        // 3e. MENU ROUTES
        //     Builder UI → voyager::menus.builder (Legacy JS)
        //     Item CRUD API → VoyagerMenuController
        // --------------------------------------------------------------------
        Route::group([
            'as'     => 'menus.',
            'prefix' => 'menus/{menu}',
        ], function () {
            Route::get('builder', [VoyagerMenuController::class, 'builder'])->name('builder');
            Route::post('order', [VoyagerMenuController::class, 'order_item'])->name('order_item');

            Route::group([
                'as'     => 'item.',
                'prefix' => 'item',
            ], function () {
                Route::delete('{id}', [VoyagerMenuController::class, 'delete_menu'])->name('destroy');
                Route::post('/', [VoyagerMenuController::class, 'add_item'])->name('add');
                Route::put('/', [VoyagerMenuController::class, 'update_item'])->name('update');
            });
        });

        // --------------------------------------------------------------------
        // 3f. SETTINGS
        //     UI → voyager::⚡settings-manager (SFC)
        //     CRUD API → VoyagerSettingsController
        // --------------------------------------------------------------------
        Route::group([
            'as'     => 'settings.',
            'prefix' => 'settings',
        ], function () {
            Route::get('/', [VoyagerSettingsController::class, 'index'])->name('index');
            Route::post('/', [VoyagerSettingsController::class, 'store'])->name('store');
            Route::put('/', [VoyagerSettingsController::class, 'update'])->name('update');
            Route::delete('{id}', [VoyagerSettingsController::class, 'delete'])->name('delete');
            Route::get('{id}/move_up', [VoyagerSettingsController::class, 'move_up'])->name('move_up');
            Route::get('{id}/move_down', [VoyagerSettingsController::class, 'move_down'])->name('move_down');
            Route::put('{id}/delete_value', [VoyagerSettingsController::class, 'delete_value'])->name('delete_value');
        });

        // --------------------------------------------------------------------
        // 3g. MEDIA MANAGER
        //     UI → voyager::media.index (Legacy Vue) or voyager::⚡media-manager (SFC)
        //     CRUD API → VoyagerMediaController
        // --------------------------------------------------------------------
        Route::group([
            'as'     => 'media.',
            'prefix' => 'media',
        ], function () {
            Route::get('/', [VoyagerMediaController::class, 'index'])->name('index');
            Route::post('files', [VoyagerMediaController::class, 'files'])->name('files');
            Route::post('new_folder', [VoyagerMediaController::class, 'new_folder'])->name('new_folder');
            Route::post('delete_file_folder', [VoyagerMediaController::class, 'delete'])->name('delete');
            Route::post('move_file', [VoyagerMediaController::class, 'move'])->name('move');
            Route::post('rename_file', [VoyagerMediaController::class, 'rename'])->name('rename');
            Route::post('upload', [VoyagerMediaController::class, 'upload'])->name('upload');
            Route::post('crop', [VoyagerMediaController::class, 'crop'])->name('crop');
        });

        // --------------------------------------------------------------------
        // 3h. BREAD BUILDER (Tools)
        //     UI → voyager::tools.bread.index / tools.bread.edit-add
        //     CRUD API → VoyagerBreadController
        // --------------------------------------------------------------------
        Route::group([
            'as'     => 'bread.',
            'prefix' => 'bread',
        ], function () {
            Route::get('/', [VoyagerBreadController::class, 'index'])->name('index');
            Route::get('{table}/create', [VoyagerBreadController::class, 'create'])->name('create');
            Route::post('/', [VoyagerBreadController::class, 'store'])->name('store');
            Route::get('{table}/edit', [VoyagerBreadController::class, 'edit'])->name('edit');
            Route::put('{id}', [VoyagerBreadController::class, 'update'])->name('update');
            Route::delete('{id}', [VoyagerBreadController::class, 'destroy'])->name('delete');
            Route::post('relationship', [VoyagerBreadController::class, 'addRelationship'])->name('relationship');
            Route::get('delete_relationship/{id}', [VoyagerBreadController::class, 'deleteRelationship'])->name('delete_relationship');
        });

        // --------------------------------------------------------------------
        // 3i. DATABASE MANAGER
        //     UI → voyager::⚡database-manager (SFC)
        //     CRUD API → VoyagerDatabaseController
        // --------------------------------------------------------------------
        Route::resource('database', VoyagerDatabaseController::class);

        // --------------------------------------------------------------------
        // 3j. COMPASS
        //     UI → voyager::⚡compass (SFC)
        //     POST API → VoyagerCompassController
        // --------------------------------------------------------------------
        Route::group([
            'as'     => 'compass.',
            'prefix' => 'compass',
        ], function () {
            Route::get('/', [VoyagerCompassController::class, 'index'])->name('index');
            Route::post('/', [VoyagerCompassController::class, 'index'])->name('post');
        });

        // =====================================================================
        // 4. V3 API ROUTES (admin-protected, for external integrations)
        // =====================================================================

        // --------------------------------------------------------------------
        // 4a. ACTIVITY LOG (API-only, JSON responses)
        // --------------------------------------------------------------------
        Route::group([
            'as'     => 'activity-log.',
            'prefix' => 'api/activity-log',
        ], function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('{id}', [ActivityLogController::class, 'show'])->name('show');
        });

        // --------------------------------------------------------------------
        // 4b. UPGRADE WIZARD (API-only, JSON responses)
        // --------------------------------------------------------------------
        Route::group([
            'as'     => 'upgrade.',
            'prefix' => 'api/upgrade',
        ], function () {
            Route::get('status', [UpgradeController::class, 'status'])->name('status');
            Route::post('run', [UpgradeController::class, 'run'])->name('run');
        });

        // =====================================================================
        // 5. LIVEWIRE SFC PAGE ROUTES
        //    Direct routes to pages backed by Livewire SFC components.
        //    These use simple controllers that return Blade wrappers
        //    containing `<livewire:voyager::⚡component />`.
        // =====================================================================

        // Activity Log page (UI, not API)
        Route::view('activity-log', 'voyager::activity-log')->name('activity-log');
        Route::view('upgrade-wizard', 'voyager::upgrade-wizard')->name('upgrade-wizard');
        Route::view('plugins', 'voyager::plugins')->name('plugins');
        Route::view('themes', 'voyager::themes')->name('themes');
        Route::view('cache', 'voyager::cache')->name('cache');
        Route::view('queue', 'voyager::queue')->name('queue');
        Route::view('maintenance', 'voyager::maintenance')->name('maintenance');

        event(new RoutingAdminAfter());
    });

    event(new RoutingAfter());
});
