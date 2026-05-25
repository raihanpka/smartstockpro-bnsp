<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function __construct(private int $warehouseId) {}

    public function model(array $row)
    {
        return new Product([
            'name' => $row['name'],
            'sku' => $row['sku'],
            'stock' => $row['stock'] ?? 0,
            'min_stock' => $row['min_stock'] ?? 10,
            'category_id' => $row['category_id'],
            'warehouse_id' => $this->warehouseId,
        ]);
    }
}
