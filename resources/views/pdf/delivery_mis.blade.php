<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SAAVY AGRO FRESH - Fuel Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .header img {
            height: 55px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 2px 0;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            white-space: normal;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .highlight {
            background-color: #fff799;
            font-weight: bold;
        }

        .plant-bg {
            background-color: #fff799;
            font-weight: bold;
        }

        .total-row td {
            background-color: #fff799;
            font-weight: bold;
        }

        .footer-note {
            text-align: center;
            font-size: 10px;
            margin-top: 10px;
        }
    </style>

</head>

<body>

    @php
        $logoPath = public_path('assets/images/saavy_logo_pdf.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    @endphp

    <div class="header">
        <img src="{{ $logoBase64 }}" alt="Saavy Logo" />
        <h2>SAAVY AGRO FRESH PVT. LTD.</h2>
        <p>PAN India Service</p>
        <p>S. No. 78, Pandhari Industrial Estate, Shivane, Pune - 411023.</p>
        <p>Mob: 9823777232 | Email: sales@savvyaqua.com</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Date</th>
                <th rowspan="2">Plant</th>
                <th colspan="3">Driver</th>
                <th colspan="2">Customer</th>
                <th rowspan="2">Order ID</th>
                <th colspan="2">Jar</th>
                <th rowspan="2">Batch No</th>
                <th rowspan="2">Delivery Status</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>No</th>
                <th>Name</th>
                <th>Code</th>
                <th>Quantity</th>
                <th>Empty Collected</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $order->created_at->format('d-m-Y') }}</td>
                    <td>{{ $order->drivers->plant->name ?? '-' }}</td>
                    <td>{{ $order->drivers->driver_code ?? '-' }}</td>
                    <td>{{ $order->drivers->name ?? '-' }}</td>
                    <td>{{ $order->drivers->vehicle_no ?? '-' }}</td>
                    <td>{{ $order->customers->name ?? '-' }}</td>
                    <td>{{ $order->customers->code ?? '-' }}</td>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->jar_quantity }}</td>
                    <td>{{ $order->empty_jars }}</td>
                    <td>{{ $order->batch_no }}</td>
                    <td>{{ ucfirst($order->delivery_status) }}</td>
                    <td>{{ $order->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer-note">
        <strong>Note:</strong> This is a computer-generated report. No seal or signature is required.
    </p>

</body>

</html>
