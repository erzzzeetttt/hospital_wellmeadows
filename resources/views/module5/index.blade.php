<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module5css/module5.css') }}">
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Billing & Payments</p>
    </div>
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</header>

<nav class="sub-nav">
    <a href="{{ route('billing.index') }}" class="active">Bills</a>
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

    <section class="summary-cards">
        <div class="summary-card">
            <span>Total Bills</span>
            <h2>{{ $summary->total_bills }}</h2>
            <p>All generated bills</p>
        </div>
        <div class="summary-card">
            <span>Paid</span>
            <h2>{{ $summary->paid_count }}</h2>
            <p>Fully settled bills</p>
        </div>
        <div class="summary-card">
            <span>Unpaid</span>
            <h2>{{ $summary->unpaid_count }}</h2>
            <p>Outstanding bills</p>
        </div>
        <div class="summary-card">
            <span>Partial</span>
            <h2>{{ $summary->partial_count }}</h2>
            <p>Partially paid bills</p>
        </div>
    </section>

    <div class="table-card">
        <div class="table-card-header">
            <div>
                <h3>All Bills</h3>
                <p>Patient billing records</p>
            </div>
            <a href="{{ route('billing.create') }}" class="btn btn-primary">+ Generate Bill</a>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Bill ID</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                    @php $balance = $bill->total_amount - $bill->amount_paid; @endphp
                    <tr>
                        <td>#{{ $bill->bill_id }}</td>
                        <td>{{ $bill->patient_name }}<br><small style="color:#64748b;">{{ $bill->patient_no }}</small></td>
                        <td>{{ $bill->bill_date }}</td>
                        <td>₱{{ number_format($bill->total_amount, 2) }}</td>
                        <td>₱{{ number_format($bill->amount_paid, 2) }}</td>
                        <td>₱{{ number_format($balance, 2) }}</td>
                        <td>
                            @if($bill->status === 'Paid')
                                <span class="badge badge-paid">Paid</span>
                            @elseif($bill->status === 'Unpaid')
                                <span class="badge badge-unpaid">Unpaid</span>
                            @elseif($bill->status === 'Partial')
                                <span class="badge badge-partial">Partial</span>
                            @else
                                <span class="badge badge-cancelled">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('billing.show', $bill->bill_id) }}" class="view-btn">View</a>
                                @if($bill->status !== 'Paid' && $bill->status !== 'Cancelled')
                                    <form action="{{ route('billing.cancel', $bill->bill_id) }}" method="POST"
                                          onsubmit="return confirm('Cancel this bill?')">
                                        @csrf
                                        <button type="submit" class="cancel-btn-sm">Cancel</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="8">No bills found. Click "Generate Bill" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</main>

</body>
</html>
