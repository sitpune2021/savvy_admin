<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>SAAVY AGRO FRESH - Delivery Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .header img {
            height: 50px;
        }

        .header-content {
            flex: 1;
            text-align: center;
        }

        .header-content h2 {
            margin: 0;
            font-size: 20px;
        }

        .header-content p {
            margin: 0;
            font-size: 13px;
            line-height: 1.4;
        }

        .info {
            margin: 20px 0;
            padding-left: 10px;
            font-size: 14px;
        }

        .info label {
            font-weight: bold;
            margin-right: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-left: 10px;
            font-size: 13px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.3;
            white-space: nowrap;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        td.date-cell {
            letter-spacing: 0.15em;
        }

        .dates {
            display: flex;
            gap: 20px;
            box-direction: column;
            align-items: center;
        }

        .dates p {
            margin: 0;
        }
    </style>
</head>

<body>

    @php
        $logoPath = public_path('assets/images/saavy_logo_pdf.png');
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    @endphp

    <div class="header" style="text-align: center; margin-bottom: 10px;">
        <img src="{{ $logoBase64 }}" alt="Saavy Logo" style="height: 50px; margin-bottom: 10px;" />
        <h2 style="margin: 0; font-size: 20px;">SAAVY AGRO FRESH PVT. LTD.</h2>
        <p style="margin: 0; font-size: 13px; line-height: 1.4;">
            PAN India Service<br />
            S. No. 78, Pandhari Industrial Estate, Shivane, Pune - 411023.<br />
            Mob: 9823777232, Email: sales@savvyaqua.com
        </p>
    </div>


    <div class="info">
        <p><label>Name of Customer:</label> {{ $customer_name ?? '__________' }}</p>
        <p><label>Address:</label> {{ $shipping_name ?? '__________' }}</p>
        <p><label>Customer ID No:</label> {{ $customer_zohi_id ?? '__________' }}</p>
        <p>
            <label>Start Date:</label>
            {{ isset($start_date) ? \Carbon\Carbon::parse($start_date)->format('d/m/y') : '__________' }}
            &nbsp;&nbsp;&nbsp;
            <label>End Date:</label>
            {{ isset($end_date) ? \Carbon\Carbon::parse($end_date)->format('d/m/y') : '__________' }}
        </p>

    </div>




    <table>
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Date</th>
                <th>Delivered Jar</th>
                <th>Jars Return</th>
                <th>Jars Balance</th>
                <th>Accepted By</th>
                <th>Delivered By</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cards as $index => $c)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="date-cell">
                        {{ $c->created_at ? $c->created_at->format('d/m/y') : '__________' }}
                    </td>
                    <td>{{ $c->order?->develivered_qty ?? '__________' }}</td>
                    <td>{{ $c->order?->return_qty ?? '__________' }}</td>
                    <td>{{ $c->balance ?? '__________' }}</td>
                    <td>{{ $c->acceptBy?->name ?? '__________' }}</td>
                    <td>{{ $c->order?->drivers?->name ?? '__________' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="info">
        @php
            $totalDelivered = $cards->sum(function ($c) {
                return $c->order?->develivered_qty ?? 0;
            });

            $lastBalance = $cards->last()?->balance ?? 0;

        @endphp
        <p><label>Total Delivered Jar:</label> {{ $totalDelivered ?? '__________' }}</p>
        <p><label>Total Stock At Site :</label> {{ $lastBalance ?? '__________' }}</p>
    </div>

    <p style="text-align: center; margin-top: 20px; font-size: 13px;">
        <strong>Note:</strong> This is a computer-generated Delivery Challan. No seal or signature is required
    </p>

</body>

</html>
