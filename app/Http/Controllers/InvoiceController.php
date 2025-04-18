<?php

namespace App\Http\Controllers;

use App\Models\SparepartDSale;
use App\Models\SparepartSale;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class InvoiceController extends Controller
{
    public function preview($transaksi)
    {

        // dd($transaksi);
        $mpdf = new Mpdf([
            // 'format' => 'A4',
            // 'orientation' => 'P',
        ]);

        $data = [
            'transaction' => SparepartSale::find($transaksi),
            'transaction_d' => SparepartDSale::where(['sparepart_sale_id' => $transaksi])->get()
        ];
        // dd($data);

        // return view('invoices.template', $data);
        $html = view('invoices.template', $data)->render();

        $mpdf->WriteHTML($html);

        // stream tanpa download
        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
