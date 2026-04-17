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
        Schema::create('post_media', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('file_path');
            $table->enum('type', ['image', 'video']);
            $table->unsignedInteger('display_order')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
