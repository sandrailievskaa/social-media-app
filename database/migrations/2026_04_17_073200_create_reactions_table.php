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
        Schema::create('reactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('reactable_type');
            $table->ulid('reactable_id');
            $table->enum('type', ['like', 'love', 'laugh', 'wow', 'sad', 'angry']);
            $table->unique(['user_id', 'reactable_type', 'reactable_id']);
            $table->index(['reactable_type', 'reactable_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
