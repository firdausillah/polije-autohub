<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Service & Sparepart</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

</head>

<body style="position: relative; background: #fffafa;">
    <div style="position: relative;">
        <div id="laundry"></div>
        <svg viewBox="50 90 100 10" xmlns="http://www.w3.org/2000/svg" class="semi-circle">
            <ellipse cx="100" cy="50" rx="100" ry="50" class="curvy"></ellipse>
        </svg>
        <div style="display: flex; justify-content: center; padding-bottom: 2rem !important; width: 100%; top: 20px; position: absolute; z-index: 999;">
            <div class="container-invoice" style="width: 480px; background-color: #ccc; margin: 20px auto; padding: 2rem 4rem; border-radius: 10px; background-color: #ffffff;box-shadow: 0 1px 2px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.07), 0 4px 8px rgba(0,0,0,0.07), 0 8px 16px rgba(0,0,0,0.07),0 16px 32px rgba(0,0,0,0.07), 0 32px 64px rgba(0,0,0,0.07); margin-bottom: 20px;">

                <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                    <img src="https://ryonotaonline.ptryodigitalprinting.com/assets/RDP.png" width="32" height="38" alt="logo" border="0" />
                    <p style="font-size: 21px; color: #ff0000; letter-spacing: -1px; font-family: 'Open Sans', sans-serif; line-height: 1; vertical-align: top; text-align: right; font-weight: bold;">Invoice</p>
                </div>

                <div>
                    <p class="text-order" style="font-size: 12px; font-family: 'Open Sans'; margin: 0; text-align: right;">No Nota N1903250091 <br> 19-03-2025</p>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3 style="font-family: 'Open Sans'; margin: 0; margin-bottom: 2rem;">Ryo Digital Printing Jember</h3>
                    <p style="font-size: 12px; font-family: 'Open Sans';">Halo, IFAN. <br> Terima Kasih Telah Menggunakan Jasa <br> Kami.</p>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
                    <div>
                        <h4 style="font-family: 'Open Sans'; font-size: 12px; font-weight: bold; color: #5B5B5B;">BILLING INFORMATION</h4>
                        <p style="font-size: 12px; font-family: 'Open Sans'; margin: 0; text-align: left;">IFAN <br> jember <br> No: +6282141138880</p>
                    </div>
                    <div>
                        <h4 style="font-family: 'Open Sans'; font-size: 12px; font-weight: bold; text-align: right; color: #5B5B5B;">PAYMENT METHOD</h4>
                        <p style="font-size: 12px; font-family: 'Open Sans'; margin: 0; text-align: right;">
                            Transfer <br> Nama Bank: BCA <br> Status Pembayaran: <u style="color: #ff0000;">LUNAS</u><br>
                            Pengambilan: 19-03-2025 19:00:00 </p>
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <!-- Order Details -->
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullPadding">
                        <tbody>
                            <tr>
                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 10px 7px 0;" width="52%" align="left">
                                    Nama Barang
                                </th>
                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #5b5b5b; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;" align="center">
                                    Jumlah
                                </th>
                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;" align="right">
                                    Harga
                                </th>
                                <th style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33; font-weight: normal; line-height: 1; vertical-align: top; padding: 0 0 7px;" align="right">
                                    Total Harga
                                </th>
                            </tr>
                            <tr>
                                <td height="1" style="background: #bebebe;" colspan="4"></td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; vertical-align: top; padding:10px 0;" class="article">
                                    <p style="color: #ff0000;line-height: 18px;">FRONLITE banner 280 gr</p>
                                    <p style="color: #2E2E2E;">
                                        Panjang : 1.5 M2 , Lebar : 1 M2<br> Keterangan : - </p>
                                </td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;" align="center">5</td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33;  line-height: 18px;  vertical-align: top; padding:10px 0;" align="right">Rp. 25,500</td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33;  line-height: 18px;  vertical-align: top; padding:10px 0;" align="right">Rp. 127,500</td>
                            </tr>
                            <tr>
                                <td height="1" colspan="4" style="border-bottom:1px solid #e4e4e4"></td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; vertical-align: top; padding:10px 0;" class="article">
                                    <p style="color: #ff0000;line-height: 18px;">DESAIN </p>
                                    <p style="color: #2E2E2E;">
                                        Keterangan : - </p>
                                </td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e;  line-height: 18px;  vertical-align: top; padding:10px 0;" align="center">2</td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33;  line-height: 18px;  vertical-align: top; padding:10px 0;" align="right">Rp. 10,000</td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #1e2b33;  line-height: 18px;  vertical-align: top; padding:10px 0;" align="right">Rp. 20,000</td>
                            </tr>
                            <tr>
                                <td height="1" colspan="4" style="border-bottom:1px solid #e4e4e4"></td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- /Order Details -->
                </div>

                <div>
                    <!-- Table Total -->
                    <table width="100%" border="0" cellpadding="0" cellspacing="0" align="center" class="fullPadding">
                        <tbody>
                            <tr>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                    Sub Total
                                </td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;" width="80">
                                    Rp. 147,500 </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                    Diskon
                                </td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;" width="80">
                                    Rp. -0 </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                    Total Harga
                                </td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; white-space:nowrap;" width="80">
                                    Rp. 147,500 </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                    Bayar
                                </td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #646a6e; line-height: 22px; vertical-align: top; text-align:right; ">
                                    Rp. 105,000 </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                    <strong>Kembali</strong>
                                </td>
                                <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #000; line-height: 22px; vertical-align: top; text-align:right; ">
                                    <strong>Rp. 0</strong>
                                </td>
                            </tr>
                            <!-- <tr>
                  <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #b0b0b0; line-height: 22px; vertical-align: top; text-align:right; "><small>TAX</small></td>
                  <td style="font-size: 12px; font-family: 'Open Sans', sans-serif; color: #b0b0b0; line-height: 22px; vertical-align: top; text-align:right; ">
                    <small>$72.40</small>
                  </td>
                </tr> -->
                        </tbody>
                    </table>
                    <!-- /Table Total -->
                </div>

                <div>
                    <p style="font-size: 12px; color: #5b5b5b; font-family: 'Open Sans', sans-serif; line-height: 18px; vertical-align: top; text-align: left;">
                        Terima Kasih.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>