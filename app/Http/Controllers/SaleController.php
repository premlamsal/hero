<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Sale;
use App\Models\SaleDetail;
use DB;
use Illuminate\Http\Request;
use Log;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Fetch all products
    public function index(Request $request)
    {

        $query = Sale::query()->with('customer');

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
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date',
            'customerId' => 'required|exists:customers,id',
            'invoiceItems' => 'required|array',

            'invoiceItems.*.productId' => 'required|exists:products,id',

            'invoiceItems.*.quantity' => 'required|numeric',
            'invoiceItems.*.price' => 'required|numeric',
            'invoiceItems.*.unitId' => 'required|exists:units,id',
        ]);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Create Sale
            $sale = Sale::create([
                'customer_id' => $request->customerId,
                'invoice_date' => $request->invoiceDate,
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

            // Loop through invoice items and calculate line totals
            foreach ($request->invoiceItems as $item) {
                $line_total = $item['quantity'] * $item['price'];
                $subtotal += $line_total;

                // Save each line item
                $line_items[] = [
                    'sale_id' => $sale->id,
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

            // Update sale with final calculations
            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'grand_total' => $grand_total,
            ]);

            // Save sale details
            SaleDetail::insert($line_items);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale saved successfully',
                'data' => $sale,
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();

            // Log more details (you can log this to a file or external service if needed)
            Log::error('Error occurred while saving sale: ', [
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'sql' => DB::getQueryLog(),  // You can log the SQL queries if necessary
            ]);

            // Return detailed error message to the client (consider removing sensitive info in production)
            return response()->json([
                'error' => 'Error saving sale: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : 'Error details not available'
            ], 500);
        }

    }
    public function show($id)
    {
        $sale = Sale::with(['customer', 'details.product', 'details.unit'])->find($id);

        if (!$sale) {
            return response()->json(['message' => 'Sale not found'], 404);
        }
        Log::info($sale);
        return response()->json($sale);
    }


    public function update(Request $request, $id)
    {
        // Log the request
        Log::info('Updating sale with ID: ' . $request);

        // Validate the request
        $validated = $request->validate([
            'invoiceDate' => 'required|date',
            'dueDate' => 'required|date',
            'customerId' => 'required|exists:customers,id',
            'invoiceItems' => 'required|array',
            'invoiceItems.*.productId' => 'required|exists:products,id',
            'invoiceItems.*.quantity' => 'required|numeric',
            'invoiceItems.*.price' => 'required|numeric',
            'invoiceItems.*.unitId' => 'required|exists:units,id',
        ]);

        // Start database transaction
        DB::beginTransaction();

        try {
            // Find the existing sale
            $sale = Sale::findOrFail($id);

            // Update the sale
            $sale->update([
                'customer_id' => $request->customerId,
                'invoice_date' => $request->invoiceDate,
                'due_date' => $request->dueDate,
                'tax' => $request->tax, // Tax percentage
                'discount' => $request->discount, // Discount percentage
            ]);

            $subtotal = 0;
            $tax_amount = 0;
            $line_items = [];

            // Loop through invoice items and calculate line totals
            foreach ($request->invoiceItems as $item) {
                $line_total = $item['quantity'] * $item['price'];
                $subtotal += $line_total;

                $line_items[] = [
                    'sale_id' => $sale->id,
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

            // Update the sale with the new totals
            $sale->update([
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'grand_total' => $grand_total,
            ]);

            // Remove old sale details
            SaleDetail::where('sale_id', $sale->id)->delete();

            // Insert updated sale details
            SaleDetail::insert($line_items);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale updated successfully',
                'data' => $sale,
            ]);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollback();

            // Log the error
            Log::error('Error occurred while updating sale: ', [
                'message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'sql' => DB::getQueryLog(),
            ]);

            // Return error response
            return response()->json([
                'error' => 'Error updating sale: ' . $e->getMessage(),
                'details' => config('app.debug') ? $e->getTraceAsString() : 'Error details not available'
            ], 500);
        }
    }


    public function destroy(string $id)
    {
        //
    }
}
