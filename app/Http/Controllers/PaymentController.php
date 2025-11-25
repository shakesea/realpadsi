<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MidtransService;

class PaymentController extends Controller
{
    public function createSnap(Request $request)
    {
        MidtransService::init();

        $orderId = 'TRX-' . time();

        // ====== PERBAIKI ITEM DETAILS DI SINI ======
        $items = [];
        foreach ($request->items as $item) {

            // Ambil harga dari database (wajib!)
            $menu = \App\Models\Menu::where('ID_Menu', $item['id'])->first();

            if (!$menu) continue;

            $items[] = [
                'id' => $item['id'],
                'price' => $menu->Harga,     // harga asli
                'quantity' => $item['qty'],  // qty
                'name' => $menu->Nama        // nama produk
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->total
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => 'Kasir',
            ]
        ];

        \Log::info('PARAMETER MIDTRANS FINAL', $params);

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return response()->json([
            'snapToken' => $snapToken,
            'order_id' => $orderId
        ]);
    }
}


