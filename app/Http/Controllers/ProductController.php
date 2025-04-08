<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return view('pages.product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $show = false;        
        return view('pages.product.add-edit', compact('show'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code',
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $image = null;
            if ($request->hasFile('image')) {
                $productImg = $request->file('image');
                $productImage = Str::random(10) . '.' . $productImg->getClientOriginalExtension();
                $productImg->storeAs('public/product', $productImage);
                $image = $productImage;
            }
            $data = $request->all();
            $data['image'] = $image;
            $create = Product::create($data);

            return redirect()->route('product.index')->with('success', 'Product added successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to add product: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $show = true;
        $product = Product::findOrFail($id);
        return view('pages.product.add-edit', compact('show', 'product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $show = false;
            $product = Product::findOrFail($id);
            return view('pages.product.add-edit', compact('show', 'product'));
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(['error' => 'Product not found.']);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'An error occurred while fetching the product for editing: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:products,code,' . $id,
            'description' => 'required|string|max:255',
            'price' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        try {
            $product = Product::findOrFail($id);
            $product->update($request->except('image'));

        
            // Handle Pan Card Upload
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::delete('public/product/' . $product->image); // Corrected $jobPost to $Driver
                }
                $productImg = $request->file('image');
                $productImage = Str::random(10) . '.' . $productImg->getClientOriginalExtension();
                $productImg->storeAs('public/product', $productImage);
                $product->image = $productImage;
            }
            $product->update($request->except('image'));
        
            return response()->json([
                'message' => 'Product updated successfully!',
            ]);
        
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Product not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the Product: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $Product = Product::findOrFail($id);
            if ($Product->pan_card_FILE) {
                Storage::delete('public/product/' . $driver->pan_card_FILE); 
            }
                $Product->delete();
                return response()->json([
                    'message' => 'Product deleted successfully!',
                ], 200);
        } catch (ModelNotFoundException $e) {
                return response()->json([
                    'error' => 'Product not found.',
                    'message' => $e->getMessage(),
                ], 404); 
        } catch (Exception $e) {
                return response()->json([
                    'error' => 'An error occurred while deleting the Product: ' . $e->getMessage(),
                    'message' => $e->getMessage(),
                ], 500);
        }
    }
}
