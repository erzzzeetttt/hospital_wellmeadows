<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Bill | WellMeadows</title>
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
    <a href="{{ route('billing.index') }}">Bills</a>
    <a href="{{ route('billing.create') }}" class="active">Generate Bill</a>
    <a href="{{ route('billing.reports') }}">Reports</a>
</nav>

<main class="container">

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

    <div class="form-card">
        <div class="form-card-header">
            <div>
                <h3>Generate New Bill</h3>
                <p>Select patient, enter bill date and add line items</p>
            </div>
        </div>

        <form action="{{ route('billing.store') }}" method="POST" id="bill-form">
            @csrf

            <h4>Bill Details</h4>
            <div class="form-grid">
                <div class="form-group">
                    <label>Patient *</label>
                    <select name="patient_no" required>
                        <option value="">Select patient</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->patient_no }}"
                                {{ old('patient_no') == $patient->patient_no ? 'selected' : '' }}>
                                {{ $patient->patient_no }} — {{ $patient->first_name }} {{ $patient->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Bill Date *</label>
                    <input type="date" name="bill_date" value="{{ old('bill_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <h4>Bill Items</h4>
            <p style="font-size:13px; color:#64748b; margin-bottom:12px;">Add one or more services, procedures, or charges.</p>

            <div id="items-container">
                <div class="item-row" id="item-row-0">
                    <div class="form-group">
                        <label>Description *</label>
                        <input type="text" name="items[0][description]" placeholder="e.g. Consultation Fee" required>
                    </div>
                    <div class="form-group">
                        <label>Quantity *</label>
                        <input type="number" name="items[0][quantity]" min="1" value="1"
                               class="qty-input" data-index="0" required>
                    </div>
                    <div class="form-group">
                        <label>Unit Price (₱) *</label>
                        <input type="number" name="items[0][unit_price]" min="0" step="0.01" placeholder="0.00"
                               class="price-input" data-index="0" required>
                    </div>
                    <div class="form-group">
                        <label>Subtotal</label>
                        <input type="text" id="subtotal-0" readonly placeholder="0.00"
                               style="background:#f8fafc; color:#475569;">
                    </div>
                    <div class="form-group" style="align-self:flex-end;">
                        <button type="button" class="btn btn-cancel" style="width:100%;"
                                onclick="removeItem('item-row-0')">Remove</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-cancel" style="margin-top:10px; font-size:13px;"
                    id="add-item-btn">+ Add Item</button>

            <div class="total-row">
                <span>Total Amount:</span>
                <strong id="grand-total">₱0.00</strong>
            </div>

            <div class="actions">
                <a href="{{ route('billing.index') }}" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-primary">Generate Bill</button>
            </div>
        </form>
    </div>

</main>

<script>
(function () {
    let itemIndex = 1;

    function calcSubtotal(index) {
        const qty   = parseFloat(document.querySelector(`[name="items[${index}][quantity]"]`)?.value) || 0;
        const price = parseFloat(document.querySelector(`[name="items[${index}][unit_price]"]`)?.value) || 0;
        const sub   = document.getElementById(`subtotal-${index}`);
        if (sub) sub.value = (qty * price).toFixed(2);
        calcTotal();
    }

    function calcTotal() {
        let total = 0;
        document.querySelectorAll('[id^="subtotal-"]').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('grand-total').textContent = '₱' + total.toFixed(2);
    }

    window.removeItem = function (id) {
        const el = document.getElementById(id);
        if (el && document.querySelectorAll('.item-row').length > 1) {
            el.remove();
            calcTotal();
        }
    };

    document.getElementById('add-item-btn').addEventListener('click', function () {
        const container = document.getElementById('items-container');
        const div = document.createElement('div');
        div.className = 'item-row';
        div.id = `item-row-${itemIndex}`;
        div.innerHTML = `
            <div class="form-group">
                <label>Description *</label>
                <input type="text" name="items[${itemIndex}][description]" placeholder="e.g. Lab Test" required>
            </div>
            <div class="form-group">
                <label>Quantity *</label>
                <input type="number" name="items[${itemIndex}][quantity]" min="1" value="1"
                       class="qty-input" data-index="${itemIndex}" required>
            </div>
            <div class="form-group">
                <label>Unit Price (₱) *</label>
                <input type="number" name="items[${itemIndex}][unit_price]" min="0" step="0.01" placeholder="0.00"
                       class="price-input" data-index="${itemIndex}" required>
            </div>
            <div class="form-group">
                <label>Subtotal</label>
                <input type="text" id="subtotal-${itemIndex}" readonly placeholder="0.00"
                       style="background:#f8fafc; color:#475569;">
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="button" class="btn btn-cancel" style="width:100%;"
                        onclick="removeItem('item-row-${itemIndex}')">Remove</button>
            </div>`;
        container.appendChild(div);

        const idx = itemIndex;
        div.querySelector('.qty-input').addEventListener('input', () => calcSubtotal(idx));
        div.querySelector('.price-input').addEventListener('input', () => calcSubtotal(idx));
        itemIndex++;
    });

    // Delegate events for first row
    document.querySelectorAll('.qty-input, .price-input').forEach(el => {
        el.addEventListener('input', () => calcSubtotal(parseInt(el.dataset.index)));
    });
})();
</script>

</body>
</html>
