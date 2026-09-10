@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Purchase Order #{{ $purchase->purchase_number }}</h4>
        <p class="text-muted small mb-0">View purchase order breakdown and batch items</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Purchases
        </a>
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Purchase
        </a>
    </div>
</div>

<!-- Purchase Header Details Card -->
<div class="card card-custom mb-4">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1"></i> Purchase Details</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Purchase Number</small>
                <span class="fs-6 fw-bold text-primary">{{ $purchase->purchase_number }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Supplier</small>
                <span class="fs-6 fw-semibold text-dark">{{ $purchase->supplier->name ?? '—' }}</span>
                @if($purchase->supplier->phone)
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $purchase->supplier->phone }}</div>
                @endif
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Purchased At</small>
                <span class="fs-6 text-dark">{{ \Carbon\Carbon::parse($purchase->purchased_at)->format('d M Y, h:i A') }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Amount</small>
                <span class="fs-5 fw-bold text-success">₹{{ number_format($purchase->total_amount, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Purchase Items Table Card -->
<div class="card card-custom">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-box-seam me-1"></i> Purchase Item Details</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Product Name</th>
                        <th>SKU Code</th>
                        <th>Batch Barcode</th>
                        <th>Quantity</th>
                        <th>Current Stock</th>
                        <th>Unit Cost (₹)</th>
                        <th>Tax Rate (%)</th>
                        <th class="text-end pe-4">Line Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $index => $item)
                        <tr>
                            <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                            <td class="fw-semibold text-dark">{{ $item->product->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border px-2 py-1">{{ $item->product->sku ?? '—' }}</span></td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    <i class="bi bi-barcode me-1"></i>{{ $item->barcode }}
                                </span>
                            </td>
                            <td class="fw-semibold">{{ number_format($item->quantity, 3) }}</td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fw-semibold">
                                    <i class="bi bi-box me-1"></i>{{ number_format($item->stock_quantity ?? 0, 3) }} {{ $item->product->unit->short_name ?? '' }}
                                </span>
                            </td>
                            <td>₹{{ number_format($item->unit_cost, 2) }}</td>
                            <td>{{ number_format($item->tax_rate, 2) }}%</td>
                            <td class="text-end pe-4 fw-bold text-dark">₹{{ number_format($item->line_total, 2) }}</td>
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
                    <span class="fw-semibold">₹{{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax Amount:</span>
                    <span class="fw-semibold">₹{{ number_format($purchase->tax_amount, 2) }}</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Total Amount:</span>
                    <span class="fw-bold fs-5 text-primary">₹{{ number_format($purchase->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
