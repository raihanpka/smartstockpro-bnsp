<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /** Gudang asal */
    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /** Gudang tujuan */
    public function destinationWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /** User yang mengajukan transfer */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
