<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\BoqItemProgress;
use App\Models\DcIn;
use App\Models\DcInItem;
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

   class DcInController extends Controller
{
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            $dc = DcIn::create([
                'dc_number' => 'DC'.time(),
                'vendor_id' => $request->vendor_id,
                'purchase_order_id' => $request->po_id,
                'delivery_channel' => $request->delivery_channel,
                'delivery_date' => now(),
            ]);

            foreach ($request->items as $item) {

                DcInItem::create([
                    'dc_in_id' => $dc->id,
                    'boq_id' => $item['boq_id'],
                    'supplied_qty' => $item['qty'],
                ]);

                // 🔥 Update BOQ Progress
                BoqItemProgress::updateOrCreate(
                    ['boq_id' => $item['boq_id']],
                    ['supplied_qty' => DB::raw('supplied_qty + '.$item['qty'])]
                );
            }
        });

        return response()->json(['message'=>'DC IN saved']);
    }
}


