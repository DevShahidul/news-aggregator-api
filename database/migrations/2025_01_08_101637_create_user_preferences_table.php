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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('source_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('preference_type', ['favorite', 'blocked'])->default('favorite');
            $table->integer('priority')->default(0);
            $table->timestamps();

            // Add unique constraint with a shorter name
            $table->unique(
                ['user_id', 'category_id', 'source_id', 'preference_type'],
                'user_pref_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
