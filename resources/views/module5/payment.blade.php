<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment | WellMeadows</title>
    <link rel="stylesheet" href="{{ asset('css/module5css/module5.css') }}">
</head>
<body>

<header class="header">
    <div>
        <h2>WellMeadows Hospital</h2>
        <p>Billing & Payments</p>
    </div>
    <a href="{{ route('billing.show', $bill->bill_id) }}">← Back to Bill</a>
</header>

<nav class="sub-nav">
    <a href="{{ route('billing.index') }}">Bills</a>
    <a href="{{ route('billing.create') }}">Generate Bill</a>
    <a href="{{ route('billing.reports') }}">Reports</a>
</nav>

<main class="container" style="max-width:700px;">

    @if (session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Bill Summary --}}
    <div class="detail-card" style="margin-bottom:20px;">
        <h3>Bill Summary</h3>
        <div class="detail-grid" style="grid-template-columns: repeat(2,1fr);">
            <div class="detail-item">
                <label>Patient</label>
                <strong>{{ $bill->patient_name }}</strong>
            </div>
            <div class="detail-item">
                <label>Bill Date</label>
                <strong>{{ $bill->bill_date }}</strong>
            </div>
            <div class="detail-item">
                <label>Total Amount</label>
                <strong>₱{{ number_format($bill->total_amount, 2) }}</strong>
            </div>
            <div class="detail-item">
                <label>Already Paid</label>
                <strong>₱{{ number_format($amountPaid, 2) }}</strong>
            </div>
            <div class="detail-item">
                <label>Balance Remaining</label>
                <strong style="color:#dc2626;">₱{{ number_format($balance, 2) }}</strong>
            </div>
        </div>
    </div>

    {{-- Payment Form --}}
    <div class="form-card">
        <div class="form-card-header">
            <div>
                <h3>Record Payment</h3>
                <p>Enter payment details for Bill #{{ $bill->bill_id }}</p>
            </div>
        </div>

        <form action="{{ route('billing.storePayment') }}" method="POST">
            @csrf
            <input type="hidden" name="bill_id" value="{{ $bill->bill_id }}">

            <div class="form-grid">
                <div class="form-group">
                    <label>Payment Date *</label>
                    <input type="date" name="payment_date"
                           value="{{ old('payment_date', date('Y-m-d')) }}" required>
                </div>

                <div class="form-group">
                    <label>Amount Paid (₱) *</label>
                    <input type="number" name="amount_paid" min="0.01" step="0.01"
                           max="{{ number_format($balance, 2, '.', '') }}"
                           value="{{ old('amount_paid') }}"
                           placeholder="0.00" required>
                    <small style="font-size:12px; color:#64748b;">
                        Maximum: ₱{{ number_format($balance, 2) }}
                    </small>
                </div>

                <div class="form-group full">
                    <label>Payment Method *</label>
                    <select name="payment_method" required>
                        <option value="">Select method</option>
                        @foreach(['Cash', 'Credit Card', 'Insurance', 'Bank Transfer'] as $method)
                            <option value="{{ $method }}"
                                {{ old('payment_method') === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="actions">
                <a href="{{ route('billing.show', $bill->bill_id) }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-primary">Record Payment</button>
            </div>
        </form>
    </div>

</main>

</body>
</html>
