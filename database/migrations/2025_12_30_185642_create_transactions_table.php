<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', [
                'income',      // Money coming in
                'expense',     // Money going out
                'transfer',    // Between own accounts
                'lent',        // Money lent to someone
                'borrowed',    // Money borrowed from someone
                'repayment_in',  // Receiving repayment
                'repayment_out'  // Paying back borrowed money
            ]);
            $table->decimal('amount', 15, 2);
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('set null'); // Source account
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->onDelete('set null'); // For transfers
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('contact_id')->nullable()->constrained()->onDelete('set null'); // For debt tracking
            $table->date('transaction_date');
            $table->string('title')->nullable(); // Quick description
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable(); // Transaction ID, Receipt no
            $table->json('metadata')->nullable(); // For extensibility (tags, location, etc)
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'account_id']);
            $table->index(['user_id', 'category_id']);
            $table->index(['user_id', 'contact_id']);
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};