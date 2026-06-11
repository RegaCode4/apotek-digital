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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->string('buyer_name', 100);
            $table->foreignId('cashier_id')->constrained('users');
            $table->enum('payment_method', ['cash', 'transfer', 'bpjs', 'insurance'])->default('cash')->index();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->string('bpjs_claim_no', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sale_date')->useCurrent()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
