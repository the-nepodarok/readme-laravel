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
        if (app()->environment('local')) {
            DB::table('categories')->insert([
                ['name' => 'Фото', 'value' => 'photo', 'icon_width' => 22, 'icon_height' => 18],
                ['name' => 'Видео', 'value' => 'video', 'icon_width' => 24, 'icon_height' => 16],
                ['name' => 'Текст', 'value' => 'text', 'icon_width' => 20, 'icon_height' => 21],
                ['name' => 'Цитата', 'value' => 'quote', 'icon_width' => 21, 'icon_height' => 20],
                ['name' => 'Ссылка', 'value' => 'link', 'icon_width' => 21, 'icon_height' => 18],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (app()->environment('local')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('categories')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
