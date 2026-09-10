@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Purchases</h4>
        <p class="text-muted small mb-0">Record and track inventory purchases from suppliers</p>
    </div>
    <a href="{{ route('purchases.new') }}" class="btn btn-primary d-flex align-items-center gap-2">
        <i class="bi bi-cart-plus"></i> Create Purchase Order
    </a>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Purchase No.</th>
                        <th>Supplier</th>
                        <th>Purchased Date</th>
                        <th>Items</th>
                        <th>Subtotal</th>
                        <th>Tax Amount</th>
                        <th>Total Amount</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-bold text-primary">{{ $purchase->purchase_number }}</span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $purchase->supplier->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($purchase->purchased_at)->format('d M Y, h:i A') }}</td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">
                                    {{ $purchase->items->count() }} item(s)
                                </span>
                            </td>
                            <td>₹{{ number_format($purchase->subtotal, 2) }}</td>
                            <td>₹{{ number_format($purchase->tax_amount, 2) }}</td>
                            <td class="fw-bold text-dark">₹{{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="text-end text-nowrap pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-outline-primary" title="Edit Purchase">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('{{ route('purchases.destroy', $purchase) }}', 'Purchase #{{ $purchase->purchase_number }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No purchases found. Click "Create Purchase Order" to start.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($purchases->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $purchases->links() }}
        </div>
    @endif
</div>
@endsection
