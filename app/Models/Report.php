<?php

namespace App\Models;

use App\Models\Base\Report as BaseReport;

class Report extends BaseReport
{
    protected $fillable = [
        'user_id',
        'date',
        'in_time',
        'out_time',
        'total_hour',
        'in_location_id',
        'out_location_id'
    ];

    protected $dates = [
        'date'
    ];

    protected $casts = [
        'date'  => 'date:Y-m-d',
    ];

    public function in_history()
    {
        return $this->belongsTo(History::class, 'in_location_id');
    }

    public function out_history()
    {
        return $this->belongsTo(History::class, 'out_location_id');
    }
}
