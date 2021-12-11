<?php

namespace App\Models;

use App\Models\Base\History as BaseHistory;

class History extends BaseHistory
{
    protected $fillable = [
        'name',
        'address'
    ];

    public function in_reports()
    {
        return $this->hasMany(Report::class, 'in_location_id');
    }

    public function out_reports()
    {
        return $this->hasMany(Report::class, 'out_location_id');
    }
}
