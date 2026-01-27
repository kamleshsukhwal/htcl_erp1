<?php

namespace App\Imports;

use Spatie\SimpleExcel\SimpleExcelReader;

class BoqItemsImport
{
    public function import(string $filePath, int $boqId)
    {
        SimpleExcelReader::create($filePath)
            ->getRows()
            ->each(function ($row) use ($boqId) {
                // import logic
            });
    }
}
