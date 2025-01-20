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
        Schema::create('sales', function (Blueprint $table) {

            $table->id();

            $table->timestamp('invoice_date');

            $table->string('due_date');

            $table->string('sale_reference')->nullable();

            $table->decimal('subtotal', 8, 2);

            $table->decimal('discount', 8, 2)->nullable();

            $table->decimal('tax', 8, 2)->nullable();

            $table->decimal('tax_amount', 8, 2)->nullable();

            $table->decimal('grand_total', 8, 2);

            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // Assumes customers table exists

            $table->unsignedBigInteger(column: 'user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');


            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
