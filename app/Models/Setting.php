<?php

namespace App\Models;

use App\Models\Base\Setting as BaseSetting;

class Setting extends BaseSetting
{
    protected $fillable = [
        'key_app',
        'timezone'
    ];
}
