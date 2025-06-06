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
            padding-top: 200px;
        }

        .bg-header {
            background-color: #6fc534;
            width: 600px;
            height: 340px;
            z-index: 1;
            top: -180px;
            right: -100px;
            position: absolute;
            border-radius: 100px;
        }

        .bg-footer {
            background-color: #6fc534;
            width: 380px;
            height: 120px;
            z-index: 1;
            bottom: -80px;
            left: -60px;
            position: absolute;
            border-radius: 30px;
        }

        .invoice-box {
            z-index: 100;
            position: relative;
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

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        .table th {
            background: #6fc534;
            color: white;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
        }

        .table tbody tr:nth-child(even) {
            background: #f5fcf0;
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <div class="bg-header"></div>
    <div class="invoice-box">

        <table style="width: 100%; margin-bottom: 50px;">
            <tr style="border: none;">
                <td>
                    <img src="{{ public_path('logo autohub sm.png') }}" alt="Logo Bengkel" height="90">
                </td>
                <td style="text-align: right; color: white;">
                    <strong style="font-size: 3rem;">INVOICE</strong><br>
                    <strong>PENJUALAN SPAREPART</strong><br>
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

        <table class="table">
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
            <table width="100%">
                <tr>
                    <td width="50%" style="border: none;"></td>
                    <td>Subtotal</td>
                    <td>{{"Rp " . number_format($transaction->sub_total, 2, ",", ".")}}</td>
                </tr>
                <tr>
                    <td width="50%" style="border: none;"></td>
                    <td>Total Discount</td>
                    <td>{{"Rp " . number_format($transaction->discount_total, 2, ",", ".")}}</td>
                </tr>
                <tr>
                    <td width="50%" style="border: none;"></td>
                    <td style="background: #f5f5f5; font-weight: bold;">Total</td>
                    <td>{{"Rp " . number_format($transaction->total, 2, ",", ".")}}</td>
                </tr>
            </table>
        </div>

        <div class="note">
            Catatan: Pastikan kendaraan sudah diuji coba sebelum meninggalkan bengkel.<br>
            Terima kasih atas kepercayaannya 🙏
        </div>

        <div class="footer">
            <p>
                Dicetak oleh Sistem Bengkel Polije Autohub - {{date_format(NOW(), 'd M Y H:i:s')}}
            </p>
        </div>
    </div>
    <div class="bg-footer" style="text-align: center; color: white;">
    </div>
</body>

</html>