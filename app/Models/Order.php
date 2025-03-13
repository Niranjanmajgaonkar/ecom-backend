<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'customer_name', 'address', 'mobile', 'pincode', 'city', 'state',
        'product_name', 'p_id', 'p_price', 'p_qut', 'p_total', 'order_id',
        'status', 'email','userid',
    ];
}
