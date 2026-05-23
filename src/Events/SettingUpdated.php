<?php

namespace YellowThree\Voyager\Events;

use Illuminate\Queue\SerializesModels;
use YellowThree\Voyager\Models\Setting;

class SettingUpdated
{
    use SerializesModels;

    public $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }
}
