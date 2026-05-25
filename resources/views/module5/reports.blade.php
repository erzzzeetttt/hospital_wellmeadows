<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module5css/module5.css') }}">
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Billing & Reports</p>
    </div>
    <a href="{{ route('admin.dashboard') }}">Back to Dashboard</a>
</header>

<nav class="sub-nav">
    <a href="{{ route('billing.index') }}">Bills</a>
    <a href="{{ route('billing.create') }}">Generate Bill</a>
    <a href="{{ route('billing.reports') }}" class="active">Reports</a>
</nav>

<main class="container">

    <section class="summary-cards">
        <div class="summary-card">
            <span>Total Revenue</span>
            <h2>₱{{ number_format($totals->total_revenue, 2) }}</h2>
            <p>All bills combined</p>
        </div>
        <div class="summary-card">
            <span>Collected Revenue</span>
            <h2>₱{{ number_format($totals->paid_revenue, 2) }}</h2>
            <p>From fully paid bills</p>
        </div>
        <div class="summary-card">
            <span>Total Bills</span>
            <h2>{{ $totals->total_bills }}</h2>
            <p>All generated bills</p>
        </div>
        <div class="summary-card">
            <span>Unpaid Bills</span>
            <h2>{{ $totals->unpaid_count }}</h2>
            <p>Requires collection</p>
        </div>
    </section>

    {{-- Status Breakdown --}}
    <div class="table-card">
        <div class="table-card-header">
            <div>
                <h3>Bills by Status</h3>
                <p>Breakdown of all bills by current status</p>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($statusSummary as $row)
                    <tr>
                        <td>
                            @if($row->status === 'Paid')
                                <span class="badge badge-paid">Paid</span>
                            @elseif($row->status === 'Unpaid')
                                <span class="badge badge-unpaid">Unpaid</span>
                            @elseif($row->status === 'Partial')
                                <span class="badge badge-partial">Partial</span>
                            @else
                                <span class="badge badge-cancelled">{{ $row->status }}</span>
                            @endif
                        </td>
                        <td>{{ $row->count }}</td>
                        <td>₱{{ number_format($row->total, 2) }}</td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="3">No billing data available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Recent Bills --}}
    <div class="table-card">
        <div class="table-card-header">
            <div>
                <h3>Recent Bills</h3>
                <p>Last 10 generated bills</p>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Bill ID</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBills as $bill)
                    <tr>
                        <td>#{{ $bill->bill_id }}</td>
                        <td>{{ $bill->patient_name }}</td>
                        <td>{{ $bill->bill_date }}</td>
                        <td>₱{{ number_format($bill->total_amount, 2) }}</td>
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
                            <a href="{{ route('billing.show', $bill->bill_id) }}" class="view-btn">View</a>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="6">No bills found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</main>

</body>
</html>
