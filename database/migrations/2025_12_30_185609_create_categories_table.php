<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['income', 'expense'])->default('expense');
            $table->string('color', 7)->default('#6B7280');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'type', 'is_active']);
            $table->index(['user_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};