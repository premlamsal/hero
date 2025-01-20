<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Log;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // Fetch all products
    public function index(Request $request)
    {

        $query = Product::query()->with(['unit', 'category', 'images']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%$search%");
            // ->orWhere('description', 'like', "%$search%");
        }

        return response()->json($query->get());


        // $products = Product::with(['unit', 'category', 'images'])->get();
        // return response()->json($products);
    }

    // Create a new product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:products',
            'cp' => 'required|numeric',
            'sp' => 'required|numeric',
            'opening_stock' => 'required|numeric',
            'low_stock_quantity' => 'required|numeric',
            'hsn_code' => 'required|string|max:255',
            'bar_code' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'required|exists:units,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Store product
        $product = Product::create($request->only([
            'name',
            'sku',
            'cp',
            'sp',
            'opening_stock',
            'low_stock_quantity',
            'hsn_code',
            'bar_code',
            'description',
            'unit_id',
            'category_id'
        ]));


        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    // Show a single product
    public function show($id)
    {
        $product = Product::with(['unit', 'category', 'images'])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    // Update a product
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        Log::info($request->name);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products')->ignore($product->id),
            ],
            'cp' => 'required|numeric',
            'sp' => 'required|numeric',
            'opening_stock' => 'required|numeric',
            'low_stock_quantity' => 'required|numeric',
            'hsn_code' => 'required|string|max:255',
            'bar_code' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_id' => 'required|exists:units,id',
            'category_id' => 'required|exists:categories,id',
        ]);



        // Update product
        $product->update($request->only([
            'name',
            'sku',
            'cp',
            'sp',
            'opening_stock',
            'low_stock_quantity',
            'hsn_code',
            'bar_code',
            'description',
            'unit_id',
            'category_id'
        ]));


        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Delete images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->location);
            $image->delete();
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
