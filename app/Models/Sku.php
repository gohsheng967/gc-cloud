<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sku extends Model
{
    protected $table = 'sku';
    protected $guarded = [];


    public function batch(){
        return $this->hasMany(Batch::class);
    }
}
