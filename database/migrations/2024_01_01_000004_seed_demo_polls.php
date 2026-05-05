<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $exists = DB::table('polls')->where('question', 'Your favorite backend framework?')->exists();

        if ($exists) {
            return;
        }

        $pollId = DB::table('polls')->insertGetId([
            'question' => 'Your favorite backend framework?',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('poll_options')->insert([
            [
                'poll_id' => $pollId,
                'option_text' => 'Laravel',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'poll_id' => $pollId,
                'option_text' => 'Django',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'poll_id' => $pollId,
                'option_text' => 'Express',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'poll_id' => $pollId,
                'option_text' => 'Spring Boot',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down()
    {
        $poll = DB::table('polls')->where('question', 'Your favorite backend framework?')->first();
        if (! $poll) {
            return;
        }

        DB::table('poll_options')->where('poll_id', $poll->id)->delete();
        DB::table('votes')->where('poll_id', $poll->id)->delete();
        DB::table('polls')->where('id', $poll->id)->delete();
    }
};
