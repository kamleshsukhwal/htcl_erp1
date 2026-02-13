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
                    'boq_item_id' => $item['boq_id'],
                    'supplied_qty' => $item['qty'],
                    //'boq_item_id' => $item['boq_id'],
                ]);

                // 🔥 Update BOQ Progress
                BoqItemProgress::updateOrCreate(
                    ['boq_item_id' => $item['boq_id']],
                    ['supplied_qty' => DB::raw('supplied_qty + '.$item['qty'])]
                );
            }
        });

        return response()->json(['message'=>'DC IN saved']);
    }

      /*
    |--------------------------------------------------------------------------
    | LIST ALL DC IN (INDEX)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = DcIn::with(['items.boqItem']);

        // Optional Filters
        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->purchase_order_id) {
            $query->where('purchase_order_id', $request->purchase_order_id);
        }

        if ($request->from_date) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        $dcList = $query->latest()->paginate(10);

        return response()->json([
            'count' => $dcList->total(),
            'data' => $dcList
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW SINGLE DC IN
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $dc = DcIn::with([
            'items.boqItem'
        ])->findOrFail($id);

        return response()->json([
            'data' => $dc
        ]);
    }

}