@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Suppliers</h4>
        <p class="text-muted small mb-0">Manage vendors and supplier contact information</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
        <i class="bi bi-plus-circle"></i> Add Supplier
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Supplier Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $supplier)
                        <tr>
                            <td class="ps-4 text-muted">{{ $suppliers->firstItem() + $index }}</td>
                            <td class="fw-semibold text-dark">{{ $supplier->name }}</td>
                            <td>{{ $supplier->email ?? '—' }}</td>
                            <td>{{ $supplier->phone ?? '—' }}</td>
                            <td class="text-truncate" style="max-width: 200px;">{{ $supplier->address ?? '—' }}</td>
                            <td>
                                @if($supplier->is_active)
                                    <span class="badge badge-status-active px-2.5 py-1">Active</span>
                                @else
                                    <span class="badge badge-status-inactive px-2.5 py-1">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary"
                                            onclick="openEditModal({{ $supplier->id }}, '{{ addslashes($supplier->name) }}', '{{ addslashes($supplier->email ?? '') }}', '{{ addslashes($supplier->phone ?? '') }}', '{{ addslashes($supplier->address ?? '') }}', {{ $supplier->is_active ? 'true' : 'false' }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('{{ route('masters.suppliers.destroy', $supplier) }}', '{{ addslashes($supplier->name) }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No suppliers found. Click "Add Supplier" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($suppliers->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('masters.suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Acme Traders Ltd" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="sales@acme.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="+1 987 654 321">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label fw-semibold">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Industrial Zone, City"></textarea>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="is_active">Is Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editSupplierForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label fw-semibold">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" id="edit_email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="text" id="edit_phone" name="phone" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label fw-semibold">Address</label>
                        <textarea id="edit_address" name="address" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label fw-semibold" for="edit_is_active">Is Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(id, name, email, phone, address, isActive) {
        const form = document.getElementById('editSupplierForm');
        form.action = `/masters/suppliers/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone;
        document.getElementById('edit_address').value = address;
        document.getElementById('edit_is_active').checked = isActive;
        
        const modal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
        modal.show();
    }
</script>
@endpush
