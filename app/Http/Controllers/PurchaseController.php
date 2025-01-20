<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use DB;
use Illuminate\Http\Request;
use Log;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Fetch all products
    public function index(Request $request)
    {

        $query = Purchase::query()->with('supplier');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('id', 'like', "%$search%");
        }

        return response()->json($query->get());

    }




    public function store(Request $request)
    {
        Log::info('jon');

        Log::info($request);


        // Validate the request
        $validated = $request->validate([
            'purchaseDate' => 'required|date',
            'dueDate' => 'required|date',
            'supplierId' => 'required|exists:suppliers,id',
            'purchaseItems' => 'required|array',

            'purchaseItems.*.productId' => 'required|exists:products,id',

            'purchaseItems.*.quantity' => 'required|numeric',
            'purchaseItems.*.price' => 'required|numeric',
            'purchaseItems.*.unitId' => 'required|exists:units,id',
        ]);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create Purchase
            $purchase = Purchase::create([
                'supplier_id' => $request->supplierId,
                'purchase_date' => $request->purchaseDate,
                'due_date' => $request->dueDate,
                'subtotal' => 0, // Initial value, will calculate later
                'tax' => $request->tax, // Tax percentage
                'tax_amount' => 0, // Will calculate later
                'discount' => $request->discount, // Discount percentage
                'grand_total' => 0, // Will calculate later
            ]);

            $subtotal = 0;
            $tax_amount = 0;
            $line_items = [];

            // Loop through purchase items and calculate line totals
            foreach ($request->purchaseItems as $item) {
                $line_total = $item['quantity'] * $item['price'];
                $subtotal += $line_total;

                // Save each line item
                $line_items[] = [
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['productId'],
                    'unit_id' => $item['unitId'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'line_total' => $line_total,
                ];
            }

            // Calculate tax amount
            $tax_amount = ($subtotal * $request->tax) / 100;

            // Calculate grand total
            $grand_total = $subtotal + $tax_amount - $request->discount;

            // Update purchase with final calculations
            $purchase->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'grand_total' => $grand_total,
            ]);

            // Save purchase details
            PurchaseDetail::insert($line_items);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase saved successfully',
                'data' => $purchase,
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();

            // Log more details (you can log this to a file or external service if needed)
            Log::error('Error occurred while saving purchase: ', [
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'sql' => DB::getQueryLog(),  // You can log the SQL queries if necessary
            ]);

            // Return detailed error message to the client (consider removing sensitive info in production)
            return response()->json([
                'error' => 'Error saving purchase: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : 'Error details not available'
            ], 500);
        }

    }
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'details.product', 'details.unit'])->find($id);

        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }
        Log::info($purchase);
        return response()->json($purchase);
    }


    public function update(Request $request, $id)
    {
        // Log the request
        Log::info('Updating purchase with ID: ' . $request);

        // Validate the request
        $validated = $request->validate([
            'purchaseDate' => 'required|date',
            'dueDate' => 'required|date',
            'supplierId' => 'required|exists:suppliers,id',
            'purchaseItems' => 'required|array',
            'purchaseItems.*.productId' => 'required|exists:products,id',
            'purchaseItems.*.quantity' => 'required|numeric',
            'purchaseItems.*.price' => 'required|numeric',
            'purchaseItems.*.unitId' => 'required|exists:units,id',
        ]);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Find the existing purchase
            $purchase = Purchase::findOrFail($id);

            // Update the purchase
            $purchase->update([
                'supplier_id' => $request->supplierId,
                'purchase_date' => $request->purchaseDate,
                'due_date' => $request->dueDate,
                'tax' => $request->tax, // Tax percentage
                'discount' => $request->discount, // Discount percentage
            ]);

            $subtotal = 0;
            $tax_amount = 0;
            $line_items = [];

            // Loop through purchase items and calculate line totals
            foreach ($request->purchaseItems as $item) {
                $line_total = $item['quantity'] * $item['price'];
                $subtotal += $line_total;

                $line_items[] = [
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['productId'],
                    'unit_id' => $item['unitId'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'line_total' => $line_total,
                ];
            }

            // Calculate tax amount
            $tax_amount = ($subtotal * $request->tax) / 100;

            // Calculate grand total
            $grand_total = $subtotal + $tax_amount - $request->discount;

            // Update the purchase with the new totals
            $purchase->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'grand_total' => $grand_total,
            ]);

            // Remove old purchase details
            PurchaseDetail::where('purchase_id', $purchase->id)->delete();

            // Insert updated purchase details
            PurchaseDetail::insert($line_items);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase updated successfully',
                'data' => $purchase,
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();

            // Log the error
            Log::error('Error occurred while updating purchase: ', [
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'sql' => DB::getQueryLog(),
            ]);

            // Return error response
            return response()->json([
                'error' => 'Error updating purchase: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : 'Error details not available'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        //
    }
}
