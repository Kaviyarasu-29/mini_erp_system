<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\Tax;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('tax')->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products', 'taxes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function new()
    {
        return $this->create();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_number' => ['nullable', 'string', 'max:255', 'unique:purchases,purchase_number'],
            'purchased_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (empty($validated['purchase_number'])) {
            $validated['purchase_number'] = CodeGeneratorService::generateMonthBasedCode(
                Purchase::class,
                'purchase_number',
                $validated['purchased_at'],
                'purchased_at'
            );
        }

        DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $cost = $itemData['unit_cost'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $cost;
                $lineTax = $lineSubtotal * ($rate / 100);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $purchase = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'purchase_number' => $validated['purchase_number'],
                'purchased_at' => $validated['purchased_at'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $cost = $itemData['unit_cost'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $cost;
                $lineTax = $lineSubtotal * ($rate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $barcode = CodeGeneratorService::generateBarcode(PurchaseItem::class, 'barcode', 'BC', 3);

                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'barcode' => $barcode,
                    'quantity' => $qty,
                    'stock_quantity' => $qty,
                    'unit_cost' => $cost,
                    'tax_rate' => $rate,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                ]);

                Product::where('id', $itemData['product_id'])->increment('stock_quantity', $qty);

                StockLog::create([
                    'product_id' => $itemData['product_id'],
                    'area' => 'purchase',
                    'mode' => '+',
                    'quantity' => $qty,
                    'ref_1' => $purchase->id,
                    'ref_2' => $purchaseItem->id,
                    'ref_3' => $purchase->supplier_id,
                ]);
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product.unit']);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('tax')->where('is_active', true)->orderBy('name')->get();
        $taxes = Tax::where('is_active', true)->orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'taxes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_number' => ['required', 'string', 'max:255', 'unique:purchases,purchase_number,'.$purchase->id],
            'purchased_at' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($purchase, $validated) {
            foreach ($purchase->items as $oldItem) {
                Product::where('id', $oldItem->product_id)->decrement('stock_quantity', $oldItem->quantity);
            }

            $purchase->items()->delete();
            StockLog::where('area', 'purchase')->where('ref_1', $purchase->id)->delete();

            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $cost = $itemData['unit_cost'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $cost;
                $lineTax = $lineSubtotal * ($rate / 100);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_number' => $validated['purchase_number'],
                'purchased_at' => $validated['purchased_at'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $cost = $itemData['unit_cost'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $cost;
                $lineTax = $lineSubtotal * ($rate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $barcode = CodeGeneratorService::generateBarcode(PurchaseItem::class, 'barcode', 'BC', 3);

                $purchaseItem = PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $itemData['product_id'],
                    'barcode' => $barcode,
                    'quantity' => $qty,
                    'stock_quantity' => $qty,
                    'unit_cost' => $cost,
                    'tax_rate' => $rate,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                ]);

                Product::where('id', $itemData['product_id'])->increment('stock_quantity', $qty);

                StockLog::create([
                    'product_id' => $itemData['product_id'],
                    'area' => 'purchase',
                    'mode' => '+',
                    'quantity' => $qty,
                    'ref_1' => $purchase->id,
                    'ref_2' => $purchaseItem->id,
                    'ref_3' => $purchase->supplier_id,
                ]);
            }
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                Product::where('id', $item->product_id)->decrement('stock_quantity', $item->quantity);
            }
            StockLog::where('area', 'purchase')->where('ref_1', $purchase->id)->delete();
            $purchase->items()->delete();
            $purchase->delete();
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }
}
