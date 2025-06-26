<?php

namespace App\Events;

use App\Models\AdminSetting;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingsUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $setting;

    public function __construct(AdminSetting $setting)
    {
        $this->setting = $setting;
    }
}
