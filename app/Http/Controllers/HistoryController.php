<?php

namespace App\Http\Controllers;

use App\Models\ServiceDChecklist;
use App\Models\ServiceDServices;
use App\Models\ServiceDSparepart;
use App\Models\ServiceSchedule;
use Illuminate\Http\Request;
use Mpdf\Mpdf;

class HistoryController extends Controller
{
    public function service($transaksi)
    {

        $mpdf = new Mpdf([
            'tempDir' => storage_path('app/mpdf-temp')
            // 'format' => 'A4',
            // 'orientation' => 'P',
        ]);

        $data = [
            'transaction' => ServiceSchedule::find($transaksi),
            'transaction_d_service' => ServiceDServices::where(['service_schedule_id' => $transaksi])->get(),
            'transaction_d_sparepart' => ServiceDSparepart::where(['service_schedule_id' => $transaksi])->get(),
            'transaction_d_checklist' => ServiceDChecklist::where(['service_schedule_id' => $transaksi])->get()
        ];
        // dd($data);

        // return view('history.template', $data);
        $html = view('history.service_template', $data)->render();

        $mpdf->WriteHTML($html);

        // stream tanpa download
        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
