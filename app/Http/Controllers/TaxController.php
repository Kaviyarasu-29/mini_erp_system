<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxes = Tax::latest()->paginate(10);

        return view('masters.taxes.index', compact('taxes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Tax::create($validated);

        return redirect()
            ->route('masters.taxes.index')
            ->with('success', 'Tax created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tax $tax)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $tax->update($validated);

        return redirect()
            ->route('masters.taxes.index')
            ->with('success', 'Tax updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tax $tax)
    {
        if ($tax->products()->exists()) {
            return redirect()
                ->route('masters.taxes.index')
                ->with('error', 'Cannot delete tax associated with products.');
        }

        $tax->delete();

        return redirect()
            ->route('masters.taxes.index')
            ->with('success', 'Tax deleted successfully.');
    }
}
