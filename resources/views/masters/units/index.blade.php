@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Units of Measurement</h4>
        <p class="text-muted small mb-0">Manage measurement units (e.g. Kg, Pcs, Ltr, Box)</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addUnitModal">
        <i class="bi bi-plus-circle"></i> Add Unit
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Unit Name</th>
                        <th>Short Code</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $index => $unit)
                        <tr>
                            <td class="ps-4 text-muted">{{ $units->firstItem() + $index }}</td>
                            <td class="fw-semibold text-dark">{{ $unit->name }}</td>
                            <td><span class="badge bg-secondary-subtle text-secondary border px-2 py-1">{{ $unit->short_name }}</span></td>
                            <td>
                                @if($unit->is_active)
                                    <span class="badge badge-status-active px-2.5 py-1">Active</span>
                                @else
                                    <span class="badge badge-status-inactive px-2.5 py-1">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary"
                                            onclick="openEditModal({{ $unit->id }}, '{{ addslashes($unit->name) }}', '{{ addslashes($unit->short_name) }}', {{ $unit->is_active ? 'true' : 'false' }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('{{ route('masters.units.destroy', $unit) }}', '{{ addslashes($unit->name) }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No measurement units found. Click "Add Unit" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($units->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $units->links() }}
        </div>
    @endif
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('masters.units.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Kilogram, Piece, Liter" required>
                    </div>
                    <div class="mb-3">
                        <label for="short_name" class="form-label fw-semibold">Short Name / Symbol <span class="text-danger">*</span></label>
                        <input type="text" name="short_name" class="form-control" placeholder="e.g. Kg, Pcs, Ltr" required>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="is_active">Is Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editUnitForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label fw-semibold">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" id="edit_name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_short_name" class="form-label fw-semibold">Short Name / Symbol <span class="text-danger">*</span></label>
                        <input type="text" id="edit_short_name" name="short_name" class="form-control" required>
                    </div>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label fw-semibold" for="edit_is_active">Is Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(id, name, shortName, isActive) {
        const form = document.getElementById('editUnitForm');
        form.action = `/masters/units/${id}`;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_short_name').value = shortName;
        document.getElementById('edit_is_active').checked = isActive;
        
        const modal = new bootstrap.Modal(document.getElementById('editUnitModal'));
        modal.show();
    }
</script>
@endpush
