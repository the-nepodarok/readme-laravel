<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $posts = [
        [
            'category_id' => 3,
            'user_id' => 1,
            'header' => 'Hello, world',
            'text' => 'PHP?',
            'view_count' => 55
        ],
        [
            'category_id' => 3,
            'user_id' => 1,
            'header' => 'Байкал',
            'text' => 'Озеро Байкал – огромное древнее озеро
            в горах Сибири к северу от монгольской границы.
            Байкал считается самым глубоким озером в мире.
            Он окружен сетью пешеходных маршрутов, называемых
            Большой байкальской тропой.
            Деревня Листвянка, расположенная на западном берегу озера, –
            популярная отправная точка для летних экскурсий.
            Зимой здесь можно кататься на коньках и собачьих упряжках.',
            'created_at' => '2023-09-27 19:00:47',
            'view_count' => 65
        ],
        [
            'category_id' => 1,
            'user_id' => 1,
            'header' => 'Фотки',
            'photo' => 'test_img.png',
        ],
        [
            'category_id' => 4,
            'user_id' => 2,
            'header' => 'Цитата',
            'quote' => 'Only a sith deals in absolutes.',
            'quote_author' => 'Obi-Wan Kenobi'
        ],
        [
            'category_id' => 5,
            'user_id' => 2,
            'header' => 'Link, link',
            'link' => 'https://wikipedia.org',
        ],
        [
            'category_id' => 2,
            'user_id' => 2,
            'header' => 'hello, world',
            'video' => 'JbyXJO_yin4'
        ]
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('local')) {
            DB::table('users')->insert([
                [
                    'name' => 'Test',
                    'email' => 'test@user.id',
                    'password' => Hash::make('testPassword'),
                    'avatar' => '374e5302-6897-40e7-ba76-dcbfd2f072ce.jpg',
                ], [
                    'name' => 'Test2',
                    'email' => 'test2@user.id',
                    'password' => Hash::make('testPassword2'),
                    'avatar' => null
                ]
            ]);

            foreach ($this->posts as $post) {
                DB::table('posts')->insert($post);
            }

            DB::table('post_user')->insert([
                [
                    'post_id' => 1,
                    'user_id' => 1
                ],
                [
                    'post_id' => 1,
                    'user_id' => 2
                ],
                [
                    'post_id' => 2,
                    'user_id' => 1
                ],
                [
                    'post_id' => 3,
                    'user_id' => 1
                ],
            ]);

            DB::table('followers')->insert([
                'follower_id' => 1,
                'subscription_id' => 2
            ]);

            DB::table('comments')->insert([
                'text' => 'Hello, Laravel',
                'user_id' => 1,
                'post_id' => 1
            ]);

            DB::table('messages')->insert([
                [
                    'sender_id' => 2,
                    'receiver_id' => 1,
                    'text' => 'Hello there!'
                ]
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
            DB::table('comments')->truncate();
            DB::table('posts')->truncate();
            DB::table('followers')->truncate();
            DB::table('post_user')->truncate();
            DB::table('users')->truncate();
            DB::table('messages')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
