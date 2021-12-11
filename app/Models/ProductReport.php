<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReport extends Model
{
    protected $table = 'product_report';


    public function product_scan()
    {
        return $this->belongsTo(Product::class, 'scan_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
