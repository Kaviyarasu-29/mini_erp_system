@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Taxes</h4>
        <p class="text-muted small mb-0">Manage tax rates (e.g. GST 5%, VAT 18%)</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addTaxModal">
        <i class="bi bi-plus-circle"></i> Add Tax
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Tax Name</th>
                        <th>Rate (%)</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $index => $tax)
                        <tr>
                            <td class="ps-4 text-muted">{{ $taxes->firstItem() + $index }}</td>
                            <td class="fw-semibold text-dark">{{ $tax->name }}</td>
                            <td><span class="badge bg-primary-subtle text-primary border px-2 py-1">{{ number_format($tax->rate, 2) }}%</span></td>
                            <td>
                                @if($tax->is_active)
                                    <span class="badge badge-status-active px-2.5 py-1">Active</span>
                                @else
                                    <span class="badge badge-status-inactive px-2.5 py-1">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary"
                                            onclick="openEditModal({{ $tax->id }}, '{{ addslashes($tax->name) }}', '{{ $tax->rate }}', {{ $tax->is_active ? 'true' : 'false' }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('{{ route('masters.taxes.destroy', $tax) }}', '{{ addslashes($tax->name) }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No taxes found. Click "Add Tax" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($taxes->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $taxes->links() }}
        </div>
    @endif
</div>

<!-- Add Tax Modal -->
<div class="modal fade" id="addTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('masters.taxes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Tax</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Tax Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. GST 18%, Standard Tax" required>
                    </div>
                    <div class="mb-3">
                        <label for="rate" class="form-label fw-semibold">Tax Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="rate" class="form-control" placeholder="18.00" required>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="is_active">Is Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Tax</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tax Modal -->
<div class="modal fade" id="editTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editTaxForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Tax</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label fw-semibold">Tax Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_rate" class="form-label fw-semibold">Tax Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" id="edit_rate" name="rate" class="form-control" required>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label fw-semibold" for="edit_is_active">Is Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Tax</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(id, name, rate, isActive) {
        const form = document.getElementById('editTaxForm');
        form.action = `/masters/taxes/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_rate').value = rate;
        document.getElementById('edit_is_active').checked = isActive;
        
        const modal = new bootstrap.Modal(document.getElementById('editTaxModal'));
        modal.show();
    }
</script>
@endpush
