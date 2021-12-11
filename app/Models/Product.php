<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'product';

    protected $guarded =[];

    public function category(){
        return $this->hasOne(Category::class, 'category_id');
    }

    public function skuCode(){
        return $this->belongsTo(Sku::class, 'sku_id');
    }

    public function batchNo(){
        return $this->belongsTo(Batch::class, 'batch_id');
    }
}
