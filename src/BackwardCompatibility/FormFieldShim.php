<?php

namespace YellowThree\Voyager\BackwardCompatibility;

use YellowThree\Voyager\Facades\Voyager;

class FormFieldShim
{
    /**
     * Boot the FormField auto-discovery shim.
     *
     * @return void
     */
    public static function boot(): void
    {
        if (function_exists('app_path') && is_dir(app_path('FormFields'))) {
            foreach (glob(app_path('FormFields/*.php')) as $file) {
                $class = 'App\\FormFields\\' . basename($file, '.php');
                if (class_exists($class)) {
                    Voyager::addFormField($class);
                    trigger_error(
                        "app/FormFields/ auto-discovery is deprecated in v3. Use plugin system instead.",
                        E_USER_DEPRECATED
                    );
                }
            }
        }
    }
}
