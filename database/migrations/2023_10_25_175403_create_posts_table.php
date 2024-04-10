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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('header', 30);
            $table->text('text')->nullable();
            $table->string('quote', 128)->nullable();
            $table->string('quote_author', 30)->nullable();
            $table->string('photo')->nullable();
            $table->string('video')->nullable();
            $table->string('link')->nullable();
            $table->string('link_preview')->nullable();
            $table->boolean('is_repost')->default(false);
            $table->integer('original_post_id')->nullable();
            $table->integer('view_count')->default(0);
            $table->foreignId('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnDelete();
            $table->fullText('text');
            $table->index('user_id');
            $table->index('category_id');
            $table->index('view_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
