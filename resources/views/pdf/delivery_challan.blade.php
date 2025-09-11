<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Delivery Challan</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
        }

        .header {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <div class="header">Delivery Challan</div>

    <p><strong>Challan No:</strong> {{ $challan_no }}</p>
    <p><strong>Date:</strong> {{ $date }}</p>
    <p><strong>Customer:</strong> {{ $customer_name }}</p>
    <p><strong>Address:</strong> {{ $customer_address }}</p>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>develivered_qty</th>
                <th>return_qty</th>
                <th>balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['develivered_qty'] }}</td>
                    <td>{{ $item['return_qty'] }}</td>
                    <td>{{ $item['balance'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Delivered by: {{ $driver_name }}</p>
</body>

</html>
