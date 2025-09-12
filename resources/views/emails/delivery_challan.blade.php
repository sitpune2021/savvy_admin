<p>Dear {{ $data['customer_name'] }},</p>

<p>Please find attached the delivery challan for your recent order with us.</p>

<p><strong>Details:</strong></p>
<ul>
    <li><strong>Challan No.:</strong> {{ $data['challan_no'] }}</li>
    <li><strong>Order No.:</strong> {{ $data['order_id'] }}</li>
    <li><strong>Delivery Date:</strong> {{ \Carbon\Carbon::parse($data['in_progress_at'])->format('d-m-Y') }}</li>
</ul>

<p>This is a system-generated email. Please do not reply to this mail.<br>
    For any queries, kindly write to us at <a href="mailto:preeti@savvyaqua.com">preeti@savvyaqua.com</a>.</p>

<p>Thank you for choosing Saavy Agro Fresh Pvt Ltd.</p>

<p>Warm Regards,<br>{{ config('app.name') }}</p>
