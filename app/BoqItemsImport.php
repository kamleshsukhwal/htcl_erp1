<?php

namespace App;

use Spatie\SimpleExcel\SimpleExcelReader;
use App\Models\BoqItem;

class BoqItemsImport
{
    public function import($filePath)
    {
        SimpleExcelReader::create($filePath)
            ->getRows()
            ->each(function (array $row) {
                BoqItem::create([
                    'item_code' => $row['item_code'],
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'rate' => $row['rate'],
                    'amount' => $row['quantity'] * $row['rate'],
                ]);
            });
    }
}
