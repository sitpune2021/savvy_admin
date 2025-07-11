<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>SAAVY AGRO FRESH - Delivery Card</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 40px;
    }
    .header {
      text-align: center;
      margin-bottom: 10px;
      position: relative;
    }
    .header h2 {
      margin: 0;
    }
    .header img {
      position: absolute;
      left: 0;
      top: 0;
      height: 40px;
    }
    .info {
      margin: 20px 0;
    }
    .info label {
      font-weight: bold;
      margin-right: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #000;
      padding: 6px;
      text-align: center;
    }
    th {
      background-color: #f0f0f0;
    }
  </style>
</head>
<body>

  <div class="header">
    {{-- Use absolute path for PDF generation --}}
    <img src="{{ public_path('logo.png') }}" alt="Saavy Logo" />
    <h2>SAAVY AGRO FRESH PVT. LTD.</h2>
    <p>
      PAN India Service<br />
      S. No. 78, Pandhari Industrial Estate, Shivane, Pune - 411023.<br />
      Mob: 9823777232, Email: sales@savvyaqua.com
    </p>
  </div>

  <div class="info">
    <p><label>Name of Customer:</label> {{ $customer_name ?? '__________' }}</p>
    <p><label>Address:</label> {{ $shipping_name ?? '__________' }}</p>
    <p><label>Customer ID No:</label> {{ $customer_zohi_id ?? '__________' }}</p>
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
        <th>Remarks</th>
      </tr>
    </thead>
    <tbody>
      @foreach($cards as $index => $c)
      <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $c->created_at->format('Y-M-d') ?? '__________' }}</td>
        <td>{{ $c->order?->develivered_qty ?? '__________' }}</td>
        <td>{{ $c->order?->return_qty ?? '__________' }}</td>
        <td>{{ $c->balance ?? '__________' }}</td>
        <td>{{ $c->acceptBy?->name ?? '__________' }}</td>
        <td>{{ $c->order?->drivers?->name ?? '__________' }}</td>
        <td>_______</td>
      </tr>
      @endforeach
    </tbody>
  </table>

</body>
</html>
