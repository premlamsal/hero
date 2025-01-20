<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade'); // Reference to the purchases table

            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Assumes products table exists

            $table->decimal('quantity', 8, 2);

            $table->decimal('line_total', 8, 2);

            $table->decimal('price', 8, 2); // Adjust precision/scale based on your needs

            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade'); // Assumes units table exists

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
