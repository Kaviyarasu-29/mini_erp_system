@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sales Orders & Invoices</h4>
        <p class="text-muted small mb-0">Record and track customer sales transactions</p>
    </div>
    <a href="{{ route('sales.new') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-bag-plus"></i> Record New Sale
    </a>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Invoice No.</th>
                        <th>Customer</th>
                        <th>Sold Date</th>
                        <th>Items</th>
                        <th>Payment Method</th>
                        <th>Payment Status</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-primary">{{ $sale->invoice_number }}</span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $sale->customer->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale->sold_at)->format('d M Y, h:i A') }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    {{ $sale->items->count() }} item(s)
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 text-capitalize">
                                    <i class="bi bi-credit-card me-1"></i>{{ $sale->payment_method }}
                                </span>
                            </td>
                            <td>
                                @if($sale->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Paid</span>
                                @elseif($sale->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">Partial</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">Unpaid</span>
                                @endif
                            </td>
                            <td>
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
                            </td>
                            <td class="fw-bold text-dark">₹{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-end text-nowrap pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-sm btn-outline-primary" title="Edit Sale">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('{{ route('sales.destroy', $sale) }}', 'Invoice #{{ $sale->invoice_number }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No sales records found. Click "Record New Sale" to start.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $sales->links() }}
        </div>
    @endif
</div>
@endsection
