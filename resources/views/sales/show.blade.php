@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sale Invoice #{{ $sale->invoice_number }}</h4>
        <p class="text-muted small mb-0">View sale invoice breakdown and line items</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Sales
        </a>
        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Sale
        </a>
    </div>
</div>

<!-- Sale Header Details Card -->
<div class="card card-custom mb-4">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-1"></i> Sale Details</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Invoice Number</small>
                <span class="fs-6 fw-bold text-primary">{{ $sale->invoice_number }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Customer</small>
                <span class="fs-6 fw-semibold text-dark">{{ $sale->customer->name ?? '—' }}</span>
                @if($sale->customer->phone)
                    <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $sale->customer->phone }}</div>
                @endif
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Sold At</small>
                <span class="fs-6 text-dark">{{ \Carbon\Carbon::parse($sale->sold_at)->format('d M Y, h:i A') }}</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Amount</small>
                <span class="fs-5 fw-bold text-success">₹{{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <hr class="my-3">

        <div class="row g-3">
            <div class="col-md-4">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Payment Method</small>
                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 text-capitalize">
                    <i class="bi bi-credit-card me-1"></i>{{ $sale->payment_method }}
                </span>
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Payment Status</small>
                @if($sale->payment_status === 'paid')
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Paid</span>
                @elseif($sale->payment_status === 'partial')
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">Partial</span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">Unpaid</span>
                @endif
            </div>
            <div class="col-md-4">
                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.75rem;">Order Status</small>
                @if($sale->status === 'completed')
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Completed</span>
                @elseif($sale->status === 'in_progress')
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1">In Progress</span>
                @elseif($sale->status === 'pending')
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">Pending</span>
                @elseif($sale->status === 'cancelled')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">Cancelled</span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border px-2.5 py-1">{{ ucfirst($sale->status) }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sale Items Table Card -->
<div class="card card-custom">
    <div class="card-header-custom">
        <h6 class="fw-bold mb-0"><i class="bi bi-bag-check me-1"></i> Sale Item Details</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Product Name</th>
                        <th>SKU Code</th>
                        <th>Quantity</th>
                        <th>Unit Price (₹)</th>
                        <th>Tax Rate (%)</th>
                        <th class="text-end pe-4">Line Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                        <tr>
                            <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                            <td class="fw-semibold text-dark">{{ $item->product->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border px-2 py-1">{{ $item->product->sku ?? '—' }}</span></td>
                            <td class="fw-semibold">{{ number_format($item->quantity, 3) }}</td>
                            <td>₹{{ number_format($item->unit_price, 2) }}</td>
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
                    <span class="fw-semibold">₹{{ number_format($sale->subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tax Amount:</span>
                    <span class="fw-semibold">₹{{ number_format($sale->tax_amount, 2) }}</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Total Amount:</span>
                    <span class="fw-bold fs-5 text-primary">₹{{ number_format($sale->total_amount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
