<?php

namespace YellowThree\Voyager\Events\Listeners;

use YellowThree\Voyager\Events\BreadAdded;
use YellowThree\Voyager\Facades\Voyager;

class AddBreadPermission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Create Permission for a given BREAD.
     *
     * @param BreadAdded $event
     *
     * @return void
     */
    public function handle(BreadAdded $bread)
    {
        if (config('voyager.bread.add_permission') && file_exists(base_path('routes/web.php'))) {
            // Create permission
            //
            // Permission::generateFor(Str::snake($bread->dataType->slug));
            $role = Voyager::model('Role')->where('name', config('voyager.bread.default_role'))->firstOrFail();

            // Get permission for added table
            $permissions = Voyager::model('Permission')->where(['table_name' => $bread->dataType->name])->get()->pluck('id')->all();

            // Assign permission to admin
            $role->permissions()->attach($permissions);
        }
    }
}
