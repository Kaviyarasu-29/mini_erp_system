<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockLog;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['customer', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('sales.create', compact('customers', 'products'));
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
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_number' => ['nullable', 'string', 'max:255', 'unique:sales,invoice_number'],
            'sold_at' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_status' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        if (empty($validated['invoice_number'])) {
            $validated['invoice_number'] = CodeGeneratorService::generateMonthBasedCode(
                Sale::class,
                'invoice_number',
                $validated['sold_at'],
                'sold_at'
            );
        }

        DB::transaction(function () use ($validated) {
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $price = $itemData['unit_price'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($rate / 100);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $sale = Sale::create([
                'customer_id' => $validated['customer_id'],
                'invoice_number' => $validated['invoice_number'],
                'sold_at' => $validated['sold_at'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'status' => $validated['status'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $price = $itemData['unit_price'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($rate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $rate,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                ]);

                Product::where('id', $itemData['product_id'])->decrement('stock_quantity', $qty);

                StockLog::create([
                    'product_id' => $itemData['product_id'],
                    'area' => 'sale',
                    'mode' => '-',
                    'quantity' => $qty,
                    'ref_1' => $sale->id,
                    'ref_2' => $saleItem->id,
                    'ref_3' => $sale->customer_id,
                ]);
            }
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale recorded successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        $sale->load(['customer', 'items.product']);
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('sales.edit', compact('sale', 'customers', 'products'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_number' => ['required', 'string', 'max:255', 'unique:sales,invoice_number,' . $sale->id],
            'sold_at' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_status' => ['required', 'string', 'max:50'],
            'status' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($sale, $validated) {
            foreach ($sale->items as $oldItem) {
                Product::where('id', $oldItem->product_id)->increment('stock_quantity', $oldItem->quantity);
            }

            $sale->items()->delete();
            StockLog::where('area', 'sale')->where('ref_1', $sale->id)->delete();

            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $price = $itemData['unit_price'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($rate / 100);

                $subtotal += $lineSubtotal;
                $taxAmount += $lineTax;
            }

            $sale->update([
                'customer_id' => $validated['customer_id'],
                'invoice_number' => $validated['invoice_number'],
                'sold_at' => $validated['sold_at'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $subtotal + $taxAmount,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'status' => $validated['status'],
            ]);

            foreach ($validated['items'] as $itemData) {
                $qty = $itemData['quantity'];
                $price = $itemData['unit_price'];
                $rate = $itemData['tax_rate'] ?? 0;

                $lineSubtotal = $qty * $price;
                $lineTax = $lineSubtotal * ($rate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $rate,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                ]);

                Product::where('id', $itemData['product_id'])->decrement('stock_quantity', $qty);

                StockLog::create([
                    'product_id' => $itemData['product_id'],
                    'area' => 'sale',
                    'mode' => '-',
                    'quantity' => $qty,
                    'ref_1' => $sale->id,
                    'ref_2' => $saleItem->id,
                    'ref_3' => $sale->customer_id,
                ]);
            }
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
            }
            StockLog::where('area', 'sale')->where('ref_1', $sale->id)->delete();
            $sale->items()->delete();
            $sale->delete();
        });

        return redirect()
            ->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }
}
