<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "HDFC Savings", "Cash Wallet", "PayTM"
            $table->enum('type', ['asset', 'liability'])->default('asset');
            $table->enum('subtype', [
                'cash', 
                'bank_account', 
                'digital_wallet', 
                'credit_card', 
                'loan'
            ]);
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->nullable(); // For credit cards
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('color', 7)->default('#3B82F6'); // Hex color for UI
            $table->string('icon')->nullable(); // Icon name for UI
            $table->boolean('is_active')->default(true);
            $table->boolean('include_in_total')->default(true); // Include in net worth calculation
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};