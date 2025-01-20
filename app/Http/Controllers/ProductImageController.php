<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    // Fetch all images for a specific product
    public function index($productId)
    {
        $images = ProductImage::where('product_id', $productId)->get();
        return response()->json($images, 200);
    }

    // Upload multiple images for a specific product
    public function upload(Request $request, $productId)
    {
        // Validate the request
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            // Store the image in the public storage directory
            $path = $image->store('product_images', 'public');

            // Save the image details in the database
            $uploadedImage = ProductImage::create([
                'product_id' => $productId,
                'location' => $path,
                'type' => $image->getClientMimeType(),
                'name' => $image->getClientOriginalName(),
            ]);

            $uploadedImages[] = $uploadedImage;
        }

        return response()->json($uploadedImages, 201);
    }

    // Delete a specific image
    public function destroy($productId, $imageId)
    {
        $image = ProductImage::where('product_id', $productId)
            ->where('id', $imageId)
            ->firstOrFail();

        // Delete the image file from storage
        Storage::disk('public')->delete($image->location);

        // Delete the record from the database
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }
}
