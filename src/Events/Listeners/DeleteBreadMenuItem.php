<?php

namespace YellowThree\Voyager\Events\Listeners;

use YellowThree\Voyager\Events\BreadDeleted;
use YellowThree\Voyager\Facades\Voyager;

class DeleteBreadMenuItem
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
     * Delete a MenuItem for a given BREAD.
     *
     * @param BreadDeleted $bread
     *
     * @return void
     */
    public function handle(BreadDeleted $bread)
    {
        if (config('voyager.bread.add_menu_item')) {
            $menuItem = Voyager::model('MenuItem')->where('route', 'voyager.'.$bread->dataType->slug.'.index');

            if ($menuItem->exists()) {
                $menuItem->delete();
            }
        }
    }
}
