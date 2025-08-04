<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Clear supplier related cache
     */
    private function clearSupplierCache()
    {
        cache()->forget('suppliers_with_count');
    }

    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        $cacheKey = 'suppliers_with_count' . ($search ? '_search_' . md5($search) : '');
        
        $suppliers = cache()->remember($cacheKey, 300, function () use ($search) {
            $query = Supplier::withCount('products');
            
            if (!empty($search)) {
                $query->where('contact_person', 'like', "%{$search}%");
            }
            
            return $query->orderBy('contact_person')->get();
        });
        
        if ($request->ajax()) {
            return response()->json([
                'suppliers' => $suppliers,
                'count' => $suppliers->count(),
                'search' => $search
            ]);
        }
        
        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_person' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'contact_number' => [
                'required',
                'string',
                'regex:/^[0-9\-\+\(\)\s]+$/',
            ],
        ], [
            'contact_person.regex' => 'Contact person name can only contain letters and spaces.',
            'contact_number.regex' => 'Contact number can only contain numbers, dashes, plus signs, parentheses, and spaces.',
        ]);

        $validated['contact_person'] = ucwords(strtolower(trim($validated['contact_person'])));

        Supplier::create($validated);

        $this->clearSupplierCache();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier added successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'contact_person' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/',
            ],
            'contact_number' => [
                'required',
                'string',
                'regex:/^[0-9\-\+\(\)\s]+$/',
            ],
        ], [
            'contact_person.regex' => 'Contact person name can only contain letters and spaces.',
            'contact_number.regex' => 'Contact number can only contain numbers, dashes, plus signs, parentheses, and spaces.',
        ]);

        $validated['contact_person'] = ucwords(strtolower(trim($validated['contact_person'])));

        $supplier->update($validated);

        $this->clearSupplierCache();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Cannot delete supplier. It has products associated with it.');
        }

        $supplier->delete();

        $this->clearSupplierCache();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Get products for a specific supplier
     */
    public function getProducts(Supplier $supplier)
    {
        $products = $supplier->products()
            ->whereNull('deleted_at')
            ->with(['category', 'brand'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'supplier' => $supplier->contact_person,
            'products' => $products
        ]);
    }
} 