<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $adminExists = DB::table('users')->where('email', 'admin@livepoll.com')->exists();

        if (! $adminExists) {
            DB::table('users')->insert([
                'name' => 'Admin',
                'email' => 'admin@livepoll.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $pollExists = DB::table('polls')->where('question', 'Your favorite backend framework?')->exists();

        if ($pollExists) {
            return;
        }

        $pollId = DB::table('polls')->insertGetId([
            'question' => 'Your favorite backend framework?',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('poll_options')->insert([
            ['poll_id' => $pollId, 'option_text' => 'Laravel', 'created_at' => now(), 'updated_at' => now()],
            ['poll_id' => $pollId, 'option_text' => 'Django', 'created_at' => now(), 'updated_at' => now()],
            ['poll_id' => $pollId, 'option_text' => 'Express', 'created_at' => now(), 'updated_at' => now()],
            ['poll_id' => $pollId, 'option_text' => 'Spring Boot', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        DB::table('votes')->whereIn('poll_id', function ($query) {
            $query->select('id')->from('polls')->where('question', 'Your favorite backend framework?');
        })->delete();

        DB::table('poll_options')->whereIn('poll_id', function ($query) {
            $query->select('id')->from('polls')->where('question', 'Your favorite backend framework?');
        })->delete();

        DB::table('polls')->where('question', 'Your favorite backend framework?')->delete();
        DB::table('users')->where('email', 'admin@livepoll.com')->delete();
    }
};
