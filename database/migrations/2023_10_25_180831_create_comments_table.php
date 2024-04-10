<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')
                ->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->text('text');
            $table->foreignId('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreignId('post_id')
                ->references('id')
                ->on('posts')
                ->cascadeOnDelete();
            $table->index('user_id');
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
