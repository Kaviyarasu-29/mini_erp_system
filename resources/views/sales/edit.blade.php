@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Edit Sale Invoice #{{ $sale->invoice_number }}</h4>
        <p class="text-muted small mb-0">Modify customer sale invoice details and line items</p>
    </div>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sales
    </a>
</div>

<form action="{{ route('sales.update', $sale) }}" method="POST" id="saleForm">
    @csrf
    @method('PUT')
    <div class="card card-custom mb-4">
        <div class="card-header-custom">
            <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1"></i> Sale Invoice Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Sold At Date <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="sold_at" class="form-control" 
                           value="{{ old('sold_at', \Carbon\Carbon::parse($sale->sold_at)->format('Y-m-d\TH:i')) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Invoice Number</label>
                    <input type="text" name="invoice_number" class="form-control bg-light" value="{{ old('invoice_number', $sale->invoice_number) }}" readonly tabindex="-1">
                    <div class="form-text">Auto-generated invoice number.</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash" {{ old('payment_method', $sale->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ old('payment_method', $sale->payment_method) == 'card' ? 'selected' : '' }}>Credit/Debit Card</option>
                        <option value="bank_transfer" {{ old('payment_method', $sale->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="upi" {{ old('payment_method', $sale->payment_method) == 'upi' ? 'selected' : '' }}>UPI / Digital</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                    <select name="payment_status" class="form-select" required>
                        <option value="paid" {{ old('payment_status', $sale->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partial" {{ old('payment_status', $sale->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="unpaid" {{ old('payment_status', $sale->payment_status) == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Order Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select" required>
                        <option value="pending" {{ old('status', $sale->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ old('status', $sale->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ old('status', $sale->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $sale->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom mb-4">
        <div class="card-header-custom bg-white border-bottom py-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <label for="barcodeScannerInput" class="form-label fw-semibold mb-0 small text-uppercase text-secondary">
                    <i class="bi bi-upc-scan me-1 text-primary"></i> Scan Purchase Item Barcode
                </label>
                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1 fw-normal small">
                    <i class="bi bi-keyboard me-1"></i> Press Enter to Add
                </span>
            </div>
            <div class="input-group" style="max-width: 550px;">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-upc-scan"></i></span>
                <input type="text" id="barcodeScannerInput" class="form-control ps-2" placeholder="Scan purchase item barcode (e.g. BC001)..." autofocus autocomplete="off">
                <button type="button" class="btn btn-primary px-3 fw-semibold" id="scanAddBtn">
                    <i class="bi bi-plus-lg me-1"></i> Add Line
                </button>
            </div>
            <div id="barcodeErrorAlert" class="text-danger small mt-2 d-none fw-semibold">
                <i class="bi bi-exclamation-circle me-1"></i> Barcode not found! Please check barcode and try again.
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;" class="ps-4">Product <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Quantity <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Unit Price (₹) <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Tax</th>
                            <th style="width: 15%;">Line Total (₹)</th>
                            <th style="width: 5%;" class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        @foreach($sale->items as $index => $item)
                            <tr class="item-row" data-product-id="{{ $item->product_id }}" data-barcode="{{ $item->barcode ?? ($item->product->sku ?? '') }}">
                                <td class="ps-4">
                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                    <input type="hidden" name="items[{{ $index }}][barcode]" value="{{ $item->barcode }}">
                                    <div class="fw-bold text-dark">{{ $item->product->name ?? '—' }}</div>
                                    <small class="text-muted"><i class="bi bi-barcode me-1"></i>Barcode: {{ $item->barcode ?? ($item->product->sku ?? '—') }}</small>
                                </td>
                                <td>
                                    <input type="number" step="0.001" min="0.001" name="items[{{ $index }}][quantity]" class="form-control qty-input" value="{{ $item->quantity }}" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" name="items[{{ $index }}][unit_price]" class="form-control price-input" value="{{ $item->unit_price }}" required>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][tax_rate]" class="form-select tax-input">
                                        <option value="0" data-rate="0" {{ $item->tax_rate == 0 ? 'selected' : '' }}>No Tax</option>
                                        @foreach($taxes as $tax)
                                            <option value="{{ $tax->rate }}" data-rate="{{ $tax->rate }}" {{ number_format($item->tax_rate, 2) == number_format($tax->rate, 2) ? 'selected' : '' }}>
                                                {{ $tax->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control line-total" value="{{ number_format($item->line_total, 2, '.', '') }}" readonly tabindex="-1">
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-3 border-top">
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold" id="subtotalDisplay">₹{{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Tax:</span>
                        <span class="fw-semibold" id="taxDisplay">₹{{ number_format($sale->tax_amount, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold fs-5">Grand Total:</span>
                        <span class="fw-bold fs-5 text-primary" id="grandTotalDisplay">₹{{ number_format($sale->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('sales.index') }}" class="btn btn-light px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-5 fw-semibold">
            <i class="bi bi-check-lg me-1"></i> Update Sale Invoice
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowIndex = {{ count($sale->items) }};

        const barcodeMap = {};

        @foreach($purchaseItems as $pItem)
            @if($pItem->barcode && $pItem->product)
                barcodeMap["{{ $pItem->barcode }}"] = {
                    productId: {{ $pItem->product_id }},
                    productName: "{{ addslashes($pItem->product->name) }}",
                    sku: "{{ addslashes($pItem->product->sku) }}",
                    barcode: "{{ $pItem->barcode }}",
                    sellingPrice: {{ $pItem->product->selling_price ?? 0 }},
                    taxRate: {{ $pItem->product->tax->rate ?? 0 }}
                };
            @endif
        @endforeach

        @foreach($products as $prod)
            barcodeMap["{{ $prod->sku }}"] = {
                productId: {{ $prod->id }},
                productName: "{{ addslashes($prod->name) }}",
                sku: "{{ addslashes($prod->sku) }}",
                barcode: "{{ addslashes($prod->sku) }}",
                sellingPrice: {{ $prod->selling_price ?? 0 }},
                taxRate: {{ $prod->tax->rate ?? 0 }}
            };
        @endforeach

        const barcodeInput = document.getElementById('barcodeScannerInput');
        const scanAddBtn = document.getElementById('scanAddBtn');
        const errorAlert = document.getElementById('barcodeErrorAlert');

        const taxesOptions = `
            <option value="0" data-rate="0">No Tax</option>
            @foreach($taxes as $tax)
                <option value="{{ $tax->rate }}" data-rate="{{ $tax->rate }}">{{ addslashes($tax->name) }}</option>
            @endforeach
        `;

        async function processBarcodeScan() {
            const code = barcodeInput.value.trim();
            if (!code) return;

            scanAddBtn.disabled = true;
            barcodeInput.disabled = true;
            errorAlert.classList.add('d-none');

            try {
                const response = await fetch(`/sales/scan/${encodeURIComponent(code)}`);
                const result = await response.json();

                if (response.ok && result.success) {
                    const itemData = result.data;

                    const existingRow = Array.from(document.querySelectorAll('#itemsTableBody .item-row')).find(row => {
                        return row.dataset.barcode == itemData.barcode;
                    });

                    if (existingRow) {
                        const qtyInput = existingRow.querySelector('.qty-input');
                        qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
                    } else {
                        addScannedRow(itemData);
                    }

                    calculateTotals();
                    barcodeInput.value = '';
                } else {
                    errorAlert.textContent = result.message || `Barcode or SKU "${code}" not found!`;
                    errorAlert.classList.remove('d-none');
                    barcodeInput.value = '';
                }
            } catch (err) {
                console.error(err);
                errorAlert.textContent = `Error scanning barcode "${code}". Please try again.`;
                errorAlert.classList.remove('d-none');
            } finally {
                scanAddBtn.disabled = false;
                barcodeInput.disabled = false;
                barcodeInput.focus();
            }
        }

        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processBarcodeScan();
            }
        });

        scanAddBtn.addEventListener('click', function () {
            processBarcodeScan();
        });

        function addScannedRow(itemData) {
            const tbody = document.getElementById('itemsTableBody');
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.dataset.productId = itemData.productId;
            tr.dataset.barcode = itemData.barcode;
            tr.innerHTML = `
                <td class="ps-4">
                    <input type="hidden" name="items[${rowIndex}][product_id]" value="${itemData.productId}">
                    <input type="hidden" name="items[${rowIndex}][barcode]" value="${itemData.barcode}">
                    <div class="fw-bold text-dark">${itemData.productName}</div>
                    <small class="text-muted"><i class="bi bi-barcode me-1"></i>Barcode: ${itemData.barcode}</small>
                </td>
                <td>
                    <input type="number" step="0.001" min="0.001" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_price]" class="form-control price-input" value="${itemData.sellingPrice.toFixed(2)}" required>
                </td>
                <td>
                    <select name="items[${rowIndex}][tax_rate]" class="form-select tax-input">
                        ${taxesOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control line-total" value="0.00" readonly tabindex="-1">
                </td>
                <td class="text-end pe-4">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);

            const taxSelect = tr.querySelector('.tax-input');
            if (taxSelect) {
                const taxRate = parseFloat(itemData.taxRate || 0);
                const match = Array.from(taxSelect.options).find(opt => parseFloat(opt.dataset.rate || 0) === taxRate);
                if (match) {
                    taxSelect.value = match.value;
                }
            }

            rowIndex++;
        }

        document.getElementById('itemsTableBody').addEventListener('change', function (e) {
            if (e.target.classList.contains('tax-input')) {
                calculateTotals();
            }
        });

        document.getElementById('itemsTableBody').addEventListener('click', function (e) {
            if (e.target.closest('.remove-row-btn')) {
                const row = e.target.closest('tr');
                row.remove();
                calculateTotals();
            }
        });

        document.getElementById('itemsTableBody').addEventListener('input', function (e) {
            if (e.target.classList.contains('qty-input') || 
                e.target.classList.contains('price-input') || 
                e.target.classList.contains('tax-input')) {
                calculateTotals();
            }
        });

        function calculateTotals() {
            let subtotal = 0;
            let totalTax = 0;

            document.querySelectorAll('#itemsTableBody .item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const taxRate = parseFloat(row.querySelector('.tax-input').value) || 0;

                const lineSub = qty * price;
                const lineTax = lineSub * (taxRate / 100);
                const lineTotal = lineSub + lineTax;

                row.querySelector('.line-total').value = lineTotal.toFixed(2);

                subtotal += lineSub;
                totalTax += lineTax;
            });

            const grandTotal = subtotal + totalTax;

            document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('taxDisplay').textContent = '₹' + totalTax.toFixed(2);
            document.getElementById('grandTotalDisplay').textContent = '₹' + grandTotal.toFixed(2);
        }

        calculateTotals();
    });
</script>
@endpush
