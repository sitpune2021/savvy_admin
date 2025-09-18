<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Delivery Challan - Saavy Agro Fresh Pvt Ltd</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fa;
            margin: 0;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 15px 20px;
        }

        header {
            text-align: center;
            margin-bottom: 8px;
            position: relative;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 8px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        h1 {
            font-size: 20px;
            color: #1a73e8;
            margin-bottom: 6px;
            letter-spacing: 1.2px;
        }

        .company-info {
            font-size: 12px;
            line-height: 1.5;
            color: #555;
            margin-bottom: 20px;
            text-align: justify;
        }

        .company-info p {
            margin: 2.5px 0;
        }

        /* Replaced flexbox with table for two sections */
        .two-sections-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .section-cell {
            vertical-align: top;
            border-left: 2px solid #1a73e8;
            background: #f9fbff;
            padding: 15px 20px;
        }

        .section-cell h2 {
            margin-top: 0;
            color: #1a73e8;
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 6px;
        }

        .section-cell p,
        .section-cell strong,
        .section-cell span {
            font-size: 11px;
            line-height: 1.5;
            color: #444;
            display: block;
            margin-bottom: 3px;
        }

        .info-row {
            margin-top: 12px;
            font-weight: 500;
            font-size: 11px;
            color: #222;
        }

        /* Table styles */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.items-table th,
        table.items-table td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
            font-size: 12px;
        }

        table.items-table th {
            background-color: #1a73e8;
            color: white;
            font-weight: 600;
        }

        table.items-table tbody tr:nth-child(even) {
            background-color: #f5faff;
        }

        /* Bottom sections replaced with table */
        .bottom-sections-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
        }

        .bottom-cell {
            vertical-align: top;
            border-left: 2px solid #1a73e8;
            background: #f9fbff;
            padding: 15px 20px;
            font-size: 12px;
            color: #444;
        }

        .bottom-cell h3 {
            margin-top: 0;
            color: #1a73e8;
            font-size: 16px;
            margin-bottom: 10px;
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 4px;
        }

        .bottom-cell p {
            margin: 0;
        }

        .signature-stamp {
            margin-top: 15px;
            text-align: center;
        }

        .signature-stamp img {
            width: 100px;
            height: auto;
            margin-top: 8px;
        }

        /* Received & Delivered table */
        table.received-delivered {
            width: 100%;
            border-collapse: collapse;
        }

        table.received-delivered th,
        table.received-delivered td {
            border: 1px solid #ddd;
            padding: 10px 13px;
            font-size: 12px;
            vertical-align: top;
        }

        table.received-delivered th {
            background-color: #1a73e8;
            color: white;
            font-weight: 500;
            width: 50%;
        }

        table.received-delivered td p {
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        @php
            $logoPath = public_path('assets/images/saavy_logo_pdf.png');
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            $stampPath = public_path('assets/images/signature_stamp.jpg');
            $stampBase64 = file_exists($stampPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($stampPath))
                : 'https://i.imgur.com/3Xq6XqF.png'; // fallback to URL if file missing
        @endphp
        <header>
            <h1>DELIVERY CHALLAN</h1>
            <table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 20px;">
                <tr>
                    <td class="company-info">
                        <p><strong>Saavy Agro Fresh Pvt Ltd</strong></p>
                        <p>Sr No 78 Pandhari Industrial Estate, Shivane Pune 411023</p>
                        <p>9823777232</p>
                        <p><a href="mailto:preeti@savvyaqua.com">preeti@savvyaqua.com</a></p>
                        <p>GSTIN: 27AAJCS2170D1ZI</p>
                    </td>
                    <td style="width: 80px; vertical-align: top; text-align: right;">
                        <img src="{{ $logoBase64 }}" alt="Saavy Agro Fresh Logo"
                            style="width: 80px; height: auto; display: block;" />
                    </td>
                </tr>
            </table>
        </header>

        <table class="two-sections-table" aria-label="Delivery Challan For and Shipping Information" cellspacing="0"
            cellpadding="0">
            <tr>
                <td class="section-cell" width="48%" aria-labelledby="party-info-title">
                    <h2 id="party-info-title">Delivery Challan For</h2>
                    <p><strong>Party Information</strong></p>
                    <p>{{ $customer_name }}</p>
                    <p>{{ $customer_address }}</p>
                    <span>{{ $c_phone_no }}</span>
                    <span><a href="mailto:{{ $c_email }}">{{ $c_email }}</a></span>
                    <p class="info-row">DC NO: {{ $challan_no }}</p>
                    <p class="info-row">Date: {{ $date }}</p>
                </td>
                <td width="4%"></td>
                <td class="section-cell" width="48%" aria-labelledby="shipping-info-title">
                    <h2 id="shipping-info-title">Shipping Information</h2>
                    <p>{{ $customer_name }}</p>
                    <p>{{ $shipping_address }}</p>
                    <span>{{ $name }}</span>
                    <span>{{ $phone }}</span>
                </td>
            </tr>
        </table>

        <table class="items-table" aria-label="Items Table" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Sl No.</th>
                    <th>Item Name</th>
                    <th>HSN/SAC Code</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item['product_name'] }}</td>
                        <td>{{ $item['product_code'] }}</td>
                        <td>{{ $item['develivered_qty'] ?? '-' }}</td>
                        <td>Jars</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="bottom-sections-table" aria-label="Terms and Signature Sections" cellspacing="0" cellpadding="0">
            <tr>
                <td class="bottom-cell" width="48%" aria-labelledby="terms-title">
                    <h3 id="terms-title">Terms and conditions</h3>
                    <p>Received Goods in good condition</p>
                </td>
                <td width="4%"></td>
                <td class="bottom-cell" width="48%" aria-labelledby="auth-sign-title">
                    <h3 id="auth-sign-title">Authorised Signature</h3>
                    <p>For, Saavy Agro Fresh Pvt Ltd</p>
                    <div class="signature-stamp">
                        <img src="{{ $stampBase64 }}" alt="Authorized Signature Stamp" />
                    </div>
                </td>
            </tr>
        </table>

        <table class="received-delivered" aria-label="Received and Delivered By" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>Received By</th>
                    <th>Delivered By</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <p>Name: ____________________</p>
                        <p>Date: ____________________</p>
                        <p>Comment: ____________________</p>
                        @foreach ($items as $i => $item)
                        <p>Balance: {{ $item['balance'] ?? '-' }}</p>
                        <p>Delivered Jar: {{ $item['develivered_qty'] ?? '-' }} </p>
                        <p>Return Jar: {{ $item['return_qty'] ?? '-' }}</p>
                        @endforeach
                    </td>
                    <td>
                        <p>Name: {{ $driver_name }}</p>
                        <p>Date: {{ $in_progress_at }}</p>
                        <p>Comment: ____________________</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
