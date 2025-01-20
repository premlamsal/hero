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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->timestamp('purchase_date');

            $table->string('purchase_reference')->nullable();

            $table->timestamp('due_date');

            $table->decimal('subtotal', 8, 2);

            $table->decimal('discount', 8, 2)->nullable();

            $table->decimal('tax', 8, 2)->nullable();

            $table->decimal('tax_amount', 8, 2)->nullable();

            $table->decimal('grand_total', 8, 2);


            $table->foreignId('supplier_id')->constrained()->onDelete('cascade'); // Assumes supplier table exists
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
