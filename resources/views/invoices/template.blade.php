<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Invoice Penjualan Sparepart {{$transaction->kode}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            padding: 20px;
        }

        .bg-header {
            background-color: green;
            width: 350px;
            height: 170px;
            z-index: 1;
            top: 0;
            right: 0;
            position: absolute;
        }

        .bg-footer {
            background-color: green;
            width: 100%;
            height: 60px;
            z-index: 1;
            bottom: 0;
            right: 0;
            /* left: 0; */
            position: absolute;
        }

        .invoice-box {
            z-index: 100;
            position: relative;
            /* max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); */
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .info {
            text-align: right;
            color: white;
        }

        table {
            width: 100%;
            line-height: 1.5;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background: #f5f5f5;
            border-bottom: 1px solid #ddd;
            text-align: left;
            padding: 8px;
        }

        table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }

        /* .total {
            text-align: right;
            margin-top: 20px;
        } */

        .note {
            margin-top: 30px;
            font-size: 13px;
            color: #555;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="bg-header"></div>
    <div class="invoice-box">
        <!-- <div class="header">
            <div class="logo">
                <img src="{{ public_path('logo autohub sm.png') }}" alt="Logo Bengkel" height="60" >
            </div>
            <div class="info">
                <strong>INVOICE</strong><br>
                No: INV-20250413-002<br>
                Tanggal: 13 Apr 2025
            </div>
        </div> -->

        <table style="width: 100%; margin-bottom: 25px;">
            <tr style="border: none;">
                <td>
                    <img src="{{ public_path('logo autohub sm.png') }}" alt="Logo Bengkel" height="90">
                </td>
                <td style="text-align: right; color: white;">
                    <strong>INVOICE PENJUALAN SPAREPART</strong><br>
                    No: {{$transaction->kode}}<br>
                    Tanggal: {{date_format(date_create($transaction->approved_at), 'd M Y')}}<br>
                </td>
            </tr>
        </table>

        <div>
            <strong>Kepada:</strong><br>
            Nama: {{$transaction->customer_name}}<br>
            No WA: {{$transaction->customer_nomor_telepon}}
        </div>

        <h4 style="margin-top: 30px;">Detail Transaksi</h4>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Deskripsi</th>
                    <th>Satuan</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaction_d as $key => $value) : ?>
                    <tr>
                        <td>{{$key+1}}</td>
                        <td>{{$value->sparepart_name}}</td>
                        <td>{{$value->satuan_name}}</td>
                        <td>{{$value->jumlah_unit}}</td>
                        <td>{{"Rp " . number_format($value->harga_unit, 2, ",", ".")}}</td>
                        <td>{{"Rp " . number_format($value->harga_subtotal, 2, ",", ".")}}</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="" style="width: 100%; position: relative;">
            <!-- <div style="width: 100px; height: 100px; background-color: #333; position: absolute; right: 10px;">as</div> -->
            <table width="100%">
                <tr>
                    <td width="60%" style="border: none;"></td>
                    <td style="background: #f5f5f5; font-weight: bold;">Total</td>
                    <td>{{"Rp " . number_format($transaction->total, 2, ",", ".")}}</td>
                </tr>
            </table>
            <!-- <table>
                <tr>
                    <td></td>
                    <td>
                        <table style="width: 30%; position: absolute; right: 10px;">
                            <tr>
                                <td>Subtotal</td>
                                <td>Ini Subtotalnya</td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td>Ini Totalnya</td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table> -->
            <!-- <strong>Grand Total: Rp265.000</strong> -->
        </div>

        <div class="note">
            Catatan: Pastikan kendaraan sudah diuji coba sebelum meninggalkan bengkel.<br>
            Terima kasih atas kepercayaannya 🙏
        </div>

        <!-- <div class="footer">
            Dicetak oleh Sistem Bengkel - 13 Apr 2025, 15:02
        </div> -->
    </div>
    <div class="bg-footer" style="text-align: center; color: white;">
        <p>
            Dicetak oleh Sistem Bengkel Polije Autohub - {{date_format(NOW(), 'd M Y H:i:s')}}
        </p>
    </div>
</body>

</html>