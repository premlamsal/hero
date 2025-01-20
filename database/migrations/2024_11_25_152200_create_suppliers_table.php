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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'name');
            $table->string(column: 'address');
            $table->string(column: 'phone')->nullable();
            $table->string(column: 'mobile')->nullable();
            $table->string(column: 'tax_number')->nullable();
            $table->string(column: 'opening_balance')->nullable();
            $table->string(column: 'balance')->nullable()->default(0);
            $table->text(column: 'description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
