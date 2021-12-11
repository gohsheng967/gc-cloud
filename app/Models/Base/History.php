<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class History
 * 
 * @property int $id
 * @property string $name
 * @property string $address
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|Report[] $reports
 *
 * @package App\Models\Base
 */
class History extends Model
{
	protected $table = 'histories';

	public function reports()
	{
		return $this->hasMany(Report::class, 'out_location_id');
	}
}
