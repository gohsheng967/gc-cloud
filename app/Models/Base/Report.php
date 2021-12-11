<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\History;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Report
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $date
 * @property Carbon $in_time
 * @property Carbon $out_time
 * @property Carbon $total_hour
 * @property int $in_location_id
 * @property int $out_location_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property History $history
 * @property User $user
 *
 * @package App\Models\Base
 */
class Report extends Model
{
    protected $table = 'reports';

    protected $casts = [
        'user_id' => 'int',
        'in_location_id' => 'int',
        'out_location_id' => 'int'
    ];

    protected $dates = [
        'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
