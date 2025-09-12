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
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 0 18px rgba(0, 0, 0, 0.1);
        }

        /* Header with icon and main title */
        header {
            text-align: center;
            margin-bottom: 10px;
            position: relative;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
        }

        .logo img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        h1 {
            font-size: 28px;
            color: #1a73e8;
            margin-bottom: 8px;
            letter-spacing: 1.2px;
        }

        .company {
            display: flex;
            align-items: center;
        }


        .company-info {
            font-size: 15px;
            line-height: 1.5;
            color: #555;
            margin-bottom: 40px;
            text-align: justify;
        }

        .company-info p {
            margin: 4px 0;
        }

        /* Two columns container */
        .two-sections {
            display: flex;
            gap: 5px;
            margin-bottom: 40px;
        }

        .section {
            flex: 1 1 45%;
            background: #f9fbff;
            border-left: 2px solid #1a73e8;
            padding: 15px 20px;
            box-shadow: 0 0 8px rgba(26, 115, 232, 0.1);
        }

        .section h2 {
            margin-top: 0;
            color: #1a73e8;
            font-size: 20px;
            margin-bottom: 15px;
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 6px;
        }

        .section p,
        .section strong {
            font-size: 14.5px;
            line-height: 1.5;
            color: #444;
        }

        .section .info-row {
            margin-top: 15px;
            font-weight: 600;
            font-size: 14px;
            color: #222;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: left;
            font-size: 14.5px;
        }

        th {
            background-color: #1a73e8;
            color: white;
            font-weight: 600;
        }

        tbody tr:nth-child(even) {
            background-color: #f5faff;
        }

        /* Bottom two sections side by side */
        .bottom-sections {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
        }

        .bottom-left,
        .bottom-right {
            flex: 1 1 45%;
            background: #f9fbff;
            border-left: 2px solid #1a73e8;
            padding: 15px 20px;
            box-shadow: 0 0 8px rgba(26, 115, 232, 0.1);
            font-size: 14.5px;
            color: #444;
        }

        .bottom-left h3,
        .bottom-right h3 {
            margin-top: 0;
            color: #1a73e8;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 6px;
        }

        .bottom-left p {
            margin: 0;
        }

        /* Signature with stamp */
        .signature-stamp {
            margin-top: 25px;
            text-align: center;
        }

        .signature-stamp img {
            max-width: 120px;
            margin-top: 10px;
        }

        /* Received & Delivered table */
        .received-delivered {
            width: 100%;
            border-collapse: collapse;
        }

        .received-delivered th,
        .received-delivered td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            font-size: 14.5px;
            vertical-align: top;
        }

        .received-delivered th {
            background-color: #1a73e8;
            color: white;
            font-weight: 600;
            width: 50%;
        }

        .received-delivered td p {
            margin: 6px 0;
        }

        /* Stamp images for Received and Delivered */
        .stamp-small {
            max-width: 100px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        @php
            $logoPath = public_path('assets/images/saavy_logo_pdf.png');
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        @endphp
        <header>
            <h1>DELIVERY CHALLAN</h1>
            <div class="company">
                <div class="company-info" aria-label="Company Information">
                    <p><strong>Saavy Agro Fresh Pvt Ltd</strong></p>
                    <p>Sr No 78 Pandhari Industrial Estate, Shivane Pune 411023</p>
                    <p>9823777232</p>
                    <p>preeti@savvyaqua.com</p>
                    <p>GSTIN: 27AAJCS2170D1ZI</p>

                </div>
                <div class="logo" aria-label="Saavy Agro Fresh Logo">
                    <img src="{{ $logoBase64 }}" alt="Saavy Agro Fresh Logo" />
                </div>
            </div>
        </header>

        <div class="two-sections" aria-label="Delivery Challan For and Shipping Information">
            <section class="section" aria-labelledby="party-info-title">
                <h2 id="party-info-title">Delivery Challan For</h2>
                <p><strong>Party Information</strong></p>
                <p>{{ $customer_name }}</p>
                <p>{{ $customer_address }}</p>
                <span>{{ $c_phone_no }}</span>
                <span>{{ $c_email }}</span>
                <p class="info-row">DC NO: {{ $challan_no }}</p>
                <p class="info-row">Date: {{ $date }}</p>
            </section>

            <section class="section" aria-labelledby="shipping-info-title">
                <h2 id="shipping-info-title">Shipping Information</h2>
                <p>{{ $customer_name }}</p>
                <p>{{ $shipping_address }}</p>
                <span>{{ $name }}</span>
                <span>{{ $phone }}</span>
                {{-- <p>GSTIN: 36AAACL2089B1ZT</p> --}}
                {{-- <p class="info-row"><strong>PO NO:</strong> 6001910 - OP - 4882700424</p> --}}
            </section>
        </div>

        <table aria-label="Items Table">
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
                        <td>{{ $item['balance'] ?? '-' }}</td>
                        <td>Jars</td>
                    </tr>
                @endforeach

            </tbody>
        </table>

        <div class="bottom-sections" aria-label="Terms and Signature Sections">
            <section class="bottom-left" aria-labelledby="terms-title">
                <h3 id="terms-title">Terms and conditions</h3>
                <p>Received Goods in good condition</p>

            </section>

            <section class="bottom-right" aria-labelledby="auth-sign-title">
                <h3 id="auth-sign-title">Authorised Signature</h3>
                <p>For, Saavy Agro Fresh Pvt Ltd</p>
                <div class="signature-stamp">
                    <!-- Replace the src with your actual stamp image URL -->
                    <img src="https://i.imgur.com/3Xq6XqF.png" alt="Authorized Signature Stamp" />
                </div>
            </section>
        </div>

        <table class="received-delivered" aria-label="Received and Delivered By">
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
                    </td>
                    <td>
                        <p>Name:{{ $driver_name }}</p>
                        <p>Date: {{ $in_progress_at }}</p>
                        <p>Comment: ____________________</p>

                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
