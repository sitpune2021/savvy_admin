<!DOCTYPE html>
<html lang="en">
@php
    $totalDriverRows = 0;
    foreach ($plants as $plant) {
        $totalDriverRows += count($plant['drivers']);
    }
@endphp

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
                <th rowspan="2">SR.NO</th>
                <th rowspan="2">MONTH</th>
                <th rowspan="2">YEAR</th>
                <th rowspan="2">NAME</th>
                <th rowspan="2">CASH PAID ltr</th>
                <th rowspan="2">CREDIT PAID ltr</th>
                <th rowspan="2">Total DIESEL</th>
                <th rowspan="2">Total Reading</th>
                <th colspan="3">AMOUNT</th>
                <th rowspan="2">Plant Name</th>
                <th rowspan="2">Total</th>
                <th rowspan="2">All Amt</th>
                <th rowspan="2">Average</th>
            </tr>
            <tr>
                <th>CASH PAID</th>
                <th>CREDIT PAID</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @php
                $sr = 1;
                $allAmtRendered = false;
            @endphp

            @foreach ($plants as $plant)
                @php
                    $plantRowspan = count($plant['drivers']);
                    $firstPlantRow = true;
                @endphp

                @foreach ($plant['drivers'] as $driver)
                    @php
                        $liters = $driver['liters'];
                        $amount = $driver['amount'];
                        $avg = $liters > 0 ? $amount / $liters : 0;
                    @endphp

                    <tr>
                        {{-- SR --}}
                        <td>{{ $sr++ }}</td>

                        {{-- MONTH --}}
                        <td>{{ \Carbon\Carbon::parse($startDate)->format('M') }}</td>

                        {{-- YEAR --}}
                        <td>{{ \Carbon\Carbon::parse($startDate)->format('Y') }}</td>

                        {{-- DRIVER NAME --}}
                        <td>{{ $driver['name'] }}</td>

                        {{-- CASH PAID LTR --}}
                        <td>0</td>

                        {{-- CREDIT PAID LTR --}}
                        <td>{{ number_format($liters, 2) }}</td>

                        {{-- TOTAL DIESEL --}}
                        <td class="highlight">{{ number_format($liters, 2) }} ltr</td>

                        {{-- TOTAL READING --}}
                        <td>-</td>

                        {{-- AMOUNT CASH --}}
                        <td>0</td>

                        {{-- AMOUNT CREDIT --}}
                        <td>{{ number_format($amount, 2) }}</td>

                        {{-- AMOUNT TOTAL --}}
                        <td>{{ number_format($amount, 2) }}</td>

                        {{-- PLANT NAME (MERGED PER PLANT) --}}
                        @if ($firstPlantRow)
                            <td rowspan="{{ $plantRowspan }}" class="plant-bg">
                                {{ $plant['name'] }}
                            </td>

                            {{-- TOTAL (PLANT-WISE) --}}
                            <td rowspan="{{ $plantRowspan }}">
                                {{ number_format($plant['total_amount'], 2) }}
                            </td>

                            @php $firstPlantRow = false; @endphp
                        @endif

                        {{-- ALL AMT (MERGED FOR ENTIRE TABLE) --}}
                        @if (!$allAmtRendered)
                            <td rowspan="{{ $totalDriverRows }}" class="highlight">
                                {{ number_format($grandTotal, 2) }}
                            </td>
                            @php $allAmtRendered = true; @endphp
                        @endif

                        {{-- AVERAGE --}}
                        <td>{{ number_format($avg, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- GRAND TOTAL ROW --}}
            <tr class="total-row">
                <td colspan="13" align="right">GRAND TOTAL</td>
                <td colspan="2">{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tbody>






    </table>

    <p class="footer-note">
        <strong>Note:</strong> This is a computer-generated report. No seal or signature is required.
    </p>

</body>

</html>
