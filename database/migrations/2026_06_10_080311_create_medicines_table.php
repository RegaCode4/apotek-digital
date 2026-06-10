<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->index();
            $table->string('generic_name', 200)->nullable();
            $table->string('category', 50)->nullable()->index();
            $table->string('manufacturer', 100)->nullable();
            $table->string('unit', 20)->default('tablet');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0)->index();
            $table->integer('min_stock')->default(10);
            $table->date('expiry_date')->nullable()->index();
            $table->boolean('requires_prescription')->default(false)->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
