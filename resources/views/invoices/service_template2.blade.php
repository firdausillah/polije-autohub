<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Service & Sparepart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-size: 14px;
        }

        .invoice-container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            background-color: #d1e7dd;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            color: #0f5132;
            margin-bottom: 10px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="mt-4 mb-4 invoice-container">
        <!-- Header -->
        <div class="mb-4 text-center">
            <h4 class="text-success">Bengkel Hijau Motor</h4>
            <small>Jl. Mekanik No. 12 - 0821-1234-5678</small><br>
            <small>Invoice #: INV-20240604</small><br>
            <small>Tanggal: 04 Juni 2025</small>
        </div>

        <!-- Service Section -->
        <div class="mb-3">
            <div class="section-title">Jasa Service</div>
            <table class="table table-sm table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>Nama Jasa</th>
                        <th class="text-end">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Service Mesin</td>
                        <td class="text-end">Rp 150.000</td>
                    </tr>
                    <tr>
                        <td>Ganti Oli</td>
                        <td class="text-end">Rp 50.000</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sparepart Section -->
        <div class="mb-3">
            <div class="section-title">Sparepart</div>
            <table class="table table-sm table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>Nama Barang</th>
                        <th>Qty</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Busi</td>
                        <td>2</td>
                        <td class="text-end">Rp 40.000</td>
                    </tr>
                    <tr>
                        <td>Filter Oli</td>
                        <td>1</td>
                        <td class="text-end">Rp 25.000</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Ringkasan -->
        <div class="pt-3 border-top">
            <table class="table table-borderless">
                <tr>
                    <td width="60%"></td>
                    <td>Subtotal Service</td>
                    <td class="text-end">Rp 200.000</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Subtotal Sparepart</td>
                    <td class="text-end">Rp 65.000</td>
                </tr>
                <tr>
                    <td></td>
                    <td><strong>Total</strong></td>
                    <td class="text-end"><strong>Rp 265.000</strong></td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>