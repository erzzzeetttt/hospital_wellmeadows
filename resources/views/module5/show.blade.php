<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill #{{ $bill->bill_id }} | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module5css/module5.css') }}">
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Billing & Payments</p>
    </div>
    <a href="{{ route('billing.index') }}">← Back to Bills</a>
</header>

<nav class="sub-nav">
    <a href="{{ route('billing.index') }}">Bills</a>
    <a href="{{ route('billing.create') }}">Generate Bill</a>
    <a href="{{ route('billing.reports') }}">Reports</a>
</nav>

<main class="container">

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    {{-- Bill Summary --}}
    <div class="detail-card">
        <h3>Bill #{{ $bill->bill_id }}</h3>

        <div class="detail-grid">
            <div class="detail-item">
                <label>Patient</label>
                <strong>{{ $bill->patient_name }}</strong>
            </div>
            <div class="detail-item">
                <label>Patient No.</label>
                <strong>{{ $bill->patient_no }}</strong>
            </div>
            <div class="detail-item">
                <label>Bill Date</label>
                <strong>{{ $bill->bill_date }}</strong>
            </div>
            <div class="detail-item">
                <label>Status</label>
                @if($bill->status === 'Paid')
                    <span class="badge badge-paid">Paid</span>
                @elseif($bill->status === 'Unpaid')
                    <span class="badge badge-unpaid">Unpaid</span>
                @elseif($bill->status === 'Partial')
                    <span class="badge badge-partial">Partial</span>
                @else
                    <span class="badge badge-cancelled">Cancelled</span>
                @endif
            </div>
            <div class="detail-item">
                <label>Total Amount</label>
                <strong>₱{{ number_format($bill->total_amount, 2) }}</strong>
            </div>
            <div class="detail-item">
                <label>Amount Paid</label>
                <strong>₱{{ number_format($amountPaid, 2) }}</strong>
            </div>
            <div class="detail-item">
                <label>Balance</label>
                <strong style="color:{{ $balance > 0 ? '#dc2626' : '#166534' }};">
                    ₱{{ number_format($balance, 2) }}
                </strong>
            </div>
        </div>

        <div class="detail-actions">
            @if($bill->status !== 'Paid' && $bill->status !== 'Cancelled')
                <a href="{{ route('billing.payment', $bill->bill_id) }}" class="btn btn-primary">Record Payment</a>
            @endif
            @if($bill->status !== 'Paid' && $bill->status !== 'Cancelled')
                <form action="{{ route('billing.cancel', $bill->bill_id) }}" method="POST"
                      onsubmit="return confirm('Cancel this bill? This cannot be undone.')">
                    @csrf
                    <button type="submit" class="btn btn-danger">Cancel Bill</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Bill Items --}}
    <div class="table-card">
        <div class="table-card-header">
            <div>
                <h3>Bill Items</h3>
                <p>Services and charges included in this bill</p>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item->item_description }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                        <td>₱{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="5">No bill items found.</td>
                    </tr>
                @endforelse
                @if(count($items) > 0)
                    <tr>
                        <td colspan="4" style="text-align:right; font-weight:600; padding:12px 16px;">Total</td>
                        <td style="font-weight:700; color:#2563eb; padding:12px 16px;">
                            ₱{{ number_format($bill->total_amount, 2) }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Payments --}}
    <div class="table-card">
        <div class="table-card-header">
            <div>
                <h3>Payment History</h3>
                <p>Payments recorded against this bill</p>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payment Date</th>
                    <th>Amount Paid</th>
                    <th>Method</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $i => $payment)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $payment->payment_date }}</td>
                        <td>₱{{ number_format($payment->amount_paid, 2) }}</td>
                        <td>{{ $payment->payment_method }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="4">No payments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</main>

</body>
</html>
