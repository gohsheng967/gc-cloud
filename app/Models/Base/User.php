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
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon $email_verified_at
 * @property string $password
 * @property string $remember_token
 * @property string $image
 * @property int $role
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|Report[] $reports
 *
 * @package App\Models\Base
 */
class User extends Model
{
	protected $table = 'users';

	protected $casts = [
		'role' => 'int'
	];

	protected $dates = [
		'email_verified_at'
	];

	public function reports()
	{
		return $this->hasMany(Report::class);
	}
}
