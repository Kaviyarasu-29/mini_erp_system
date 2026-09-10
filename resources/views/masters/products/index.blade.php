@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Products & Inventory</h4>
        <p class="text-muted small mb-0">Manage master products, pricing, SKU codes, and total stock</p>
    </div>
    <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addProductModal">
        <i class="bi bi-plus-circle"></i> Add Product
    </button>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">SKU Code</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Purchase Price (₹)</th>
                        <th>Selling Price (₹)</th>
                        <th>Current Stock Qty</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="ps-4">
                                <span class="fw-semibold text-dark">{{ $product->sku }}</span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $product->name }}</td>
                            <td>
                                <div>{{ $product->category->name ?? '—' }}</div>
                                @if($product->subcategory)
                                    <small class="text-muted">{{ $product->subcategory->name }}</small>
                                @endif
                            </td>
                            <td>{{ $product->unit->short_name ?? ($product->unit->name ?? '—') }}</td>
                            <td>₹{{ number_format($product->purchase_price, 2) }}</td>
                            <td class="fw-semibold text-success">₹{{ number_format($product->selling_price, 2) }}</td>
                            <td>
                                <span class="badge {{ $product->stock_quantity > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-secondary-subtle text-secondary border' }} px-2.5 py-1">
                                    {{ number_format($product->stock_quantity, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-status-active px-2.5 py-1">Active</span>
                                @else
                                    <span class="badge badge-status-inactive px-2.5 py-1">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap pe-4">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary"
                                            onclick="openEditModal({{ json_encode($product) }})"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete('{{ route('masters.products.destroy', $product) }}', '{{ addslashes($product->name) }}')"
                                            title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No products found. Click "Add Product" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $products->links() }}
        </div>
    @endif
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('masters.products.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Wireless Mouse" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control" placeholder="SKU-WM-001" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Subcategory (Optional)</label>
                            <select name="subcategory_id" class="form-select">
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Unit of Measure <span class="text-danger">*</span></label>
                            <select name="unit_id" class="form-select" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tax Rate (Optional)</label>
                            <select name="tax_id" class="form-select">
                                <option value="">No Tax / Exempt</option>
                                @foreach($taxes as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Purchase Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="purchase_price" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Selling Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="selling_price" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold" for="is_active">Is Active Product</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editProductForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                            <input type="text" id="edit_name" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">SKU Code <span class="text-danger">*</span></label>
                            <input type="text" id="edit_sku" name="sku" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select id="edit_category_id" name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Subcategory (Optional)</label>
                            <select id="edit_subcategory_id" name="subcategory_id" class="form-select">
                                <option value="">Select Subcategory</option>
                                @foreach($subcategories as $sub)
                                    <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Unit of Measure <span class="text-danger">*</span></label>
                            <select id="edit_unit_id" name="unit_id" class="form-select" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Tax Rate (Optional)</label>
                            <select id="edit_tax_id" name="tax_id" class="form-select">
                                <option value="">No Tax / Exempt</option>
                                @foreach($taxes as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Purchase Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" id="edit_purchase_price" name="purchase_price" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Selling Price (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" id="edit_selling_price" name="selling_price" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <label class="form-check-label fw-semibold" for="edit_is_active">Is Active Product</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(product) {
        const form = document.getElementById('editProductForm');
        form.action = `/masters/products/${product.id}`;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_sku').value = product.sku;
        document.getElementById('edit_category_id').value = product.category_id;
        document.getElementById('edit_subcategory_id').value = product.subcategory_id || '';
        document.getElementById('edit_unit_id').value = product.unit_id;
        document.getElementById('edit_tax_id').value = product.tax_id || '';
        document.getElementById('edit_purchase_price').value = product.purchase_price;
        document.getElementById('edit_selling_price').value = product.selling_price;
        document.getElementById('edit_is_active').checked = product.is_active;
        
        const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
        modal.show();
    }
</script>
@endpush
