@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Create Purchase Order</h4>
        <p class="text-muted small mb-0">Record a new inventory purchase entry</p>
    </div>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Purchases
    </a>
</div>

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    <div class="card card-custom mb-4">
        <div class="card-header-custom">
            <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1"></i> Purchase Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Purchased At Date <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="purchased_at" class="form-control" 
                           value="{{ old('purchased_at', now()->format('Y-m-d\TH:i')) }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom mb-4">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="bi bi-list-check me-1"></i> Purchase Line Items</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                <i class="bi bi-plus-lg me-1"></i> Add Item Row
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 35%;" class="ps-4">Product <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Quantity <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Unit Cost (₹) <span class="text-danger">*</span></th>
                            <th style="width: 15%;">Tax</th>
                            <th style="width: 15%;">Line Total (₹)</th>
                            <th style="width: 5%;" class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr class="item-row">
                            <td class="ps-4">
                                <select name="items[0][product_id]" class="form-select product-select" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-cost="{{ $product->purchase_price }}"
                                                data-tax="{{ $product->tax->rate ?? 0 }}">
                                            {{ $product->name }} (SKU: {{ $product->sku }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" step="0.001" min="0.001" name="items[0][quantity]" class="form-control qty-input" value="1" required>
                            </td>
                            <td>
                                <input type="number" step="0.01" min="0" name="items[0][unit_cost]" class="form-control cost-input" value="0.00" required>
                            </td>
                            <td>
                                <select name="items[0][tax_rate]" class="form-select tax-input">
                                    <option value="0" data-rate="0">No Tax</option>
                                    @foreach($taxes as $tax)
                                        <option value="{{ $tax->rate }}" data-rate="{{ $tax->rate }}">{{ $tax->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control line-total" value="0.00" readonly tabindex="-1">
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" disabled>
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-3 border-top">
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-semibold" id="subtotalDisplay">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Tax:</span>
                        <span class="fw-semibold" id="taxDisplay">₹0.00</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold fs-5">Grand Total:</span>
                        <span class="fw-bold fs-5 text-primary" id="grandTotalDisplay">₹0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ route('purchases.index') }}" class="btn btn-light px-4">Cancel</a>
        <button type="submit" class="btn btn-primary px-5 fw-semibold">
            <i class="bi bi-check-lg me-1"></i> Save Purchase Order
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowIndex = 1;

        const productsOptions = `
            <option value="">Select Product</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" 
                        data-cost="{{ $product->purchase_price }}"
                        data-tax="{{ $product->tax->rate ?? 0 }}">
                    {{ addslashes($product->name) }} (SKU: {{ addslashes($product->sku) }})
                </option>
            @endforeach
        `;

        const taxesOptions = `
            <option value="0" data-rate="0">No Tax</option>
            @foreach($taxes as $tax)
                <option value="{{ $tax->rate }}" data-rate="{{ $tax->rate }}">{{ addslashes($tax->name) }}</option>
            @endforeach
        `;

        document.getElementById('addRowBtn').addEventListener('click', function () {
            const tbody = document.getElementById('itemsTableBody');
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.innerHTML = `
                <td class="ps-4">
                    <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                        ${productsOptions}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.001" min="0.001" name="items[${rowIndex}][quantity]" class="form-control qty-input" value="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_cost]" class="form-control cost-input" value="0.00" required>
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
            rowIndex++;
            updateRemoveButtons();
            calculateTotals();
        });

        document.getElementById('itemsTableBody').addEventListener('click', function (e) {
            if (e.target.closest('.remove-row-btn')) {
                const row = e.target.closest('tr');
                row.remove();
                updateRemoveButtons();
                calculateTotals();
            }
        });

        document.getElementById('itemsTableBody').addEventListener('change', function (e) {
            if (e.target.classList.contains('product-select')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const row = e.target.closest('tr');
                if (selectedOption && selectedOption.dataset.cost) {
                    row.querySelector('.cost-input').value = parseFloat(selectedOption.dataset.cost).toFixed(2);
                    const taxRate = parseFloat(selectedOption.dataset.tax || 0);
                    const taxSelect = row.querySelector('.tax-input');
                    if (taxSelect) {
                        const match = Array.from(taxSelect.options).find(opt => parseFloat(opt.dataset.rate || 0) === taxRate);
                        taxSelect.value = match ? match.value : '0';
                    }
                }
                calculateTotals();
            }
            if (e.target.classList.contains('tax-input')) {
                calculateTotals();
            }
        });

        document.getElementById('itemsTableBody').addEventListener('input', function (e) {
            if (e.target.classList.contains('qty-input') || 
                e.target.classList.contains('cost-input') || 
                e.target.classList.contains('tax-input')) {
                calculateTotals();
            }
        });

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('#itemsTableBody .item-row');
            rows.forEach(row => {
                const btn = row.querySelector('.remove-row-btn');
                btn.disabled = (rows.length === 1);
            });
        }

        function calculateTotals() {
            let subtotal = 0;
            let totalTax = 0;

            document.querySelectorAll('#itemsTableBody .item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const taxRate = parseFloat(row.querySelector('.tax-input').value) || 0;

                const lineSub = qty * cost;
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
