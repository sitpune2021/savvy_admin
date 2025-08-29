{{-- @php
    $statusClasses = [
        'cancelled' => 'bg-danger-subtle text-danger',
        'pending' => 'bg-warning-subtle text-warning',
        'completed' => 'bg-success-subtle text-success',
        'in_progress' => 'bg-info-subtle text-info',
    ];
@endphp
@foreach ($allPendingOrders as $order)
    <tr>
        <td>
            <a href="{{ url('order/' . $order->id) }}" class="fw-medium link-primary">#{{ $order->id }}
                @if (auth()->user()?->vendor?->id === null &&
                        $order->drivers?->vendor_id != null &&
                        auth()->user()?->plantManager?->id == null)
                    <i class="ri-user-shared-line"></i>
                @endif
                @if ($order->type == 'additional')
                    <i class="ri-shopping-cart-line"></i>
                @endif
            </a>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span style="white-space: pre-wrap;">{{ $order->customers->name }}</span>
                </div>
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span style="white-space: pre-wrap;">{{ $order->shipping->shipping_address }}</span>
                </div>
            </div>
        </td>
        <td>{{ $order?->drivers?->name }}</td>
        <td>{{ $order->develivered_qty }}</td>
        <td>
            <span class="badge {{ $statusClasses[$order->status] ?? 'bg-secondary-subtle text-secondary' }} p-2">
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </span>
        </td>
        <td>{{ $order->created_at->format('d-m-Y') }}</td>
    </tr>
@endforeach --}}
