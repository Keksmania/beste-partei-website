<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing event data to posts table
        DB::transaction(function () {
            $events = DB::table('events')->get();

            foreach ($events as $event) {
                $postData = [
                    'name' => $event->name,
                    'description' => $event->description,
                    'image' => $event->image,
                    'thumbnail' => $event->thumbnail,
                    'created_at' => $event->created_at,
                    'updated_at' => $event->updated_at,
                ];

                try {
                    $postId = DB::table('posts')->insertGetId($postData);

                    DB::table('events')
                        ->where('id', $event->id)
                        ->update(['post_id' => $postId]);
                } catch (\Exception $e) {
                    Log::error('Migration failed for event ID: ' . $event->id . ' with error: ' . $e->getMessage());
                    Log::error('Data being inserted: ' . json_encode($postData));
                    // Optionally, you could continue to the next event or rethrow the exception
                    // throw $e;
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the migration
        DB::transaction(function () {
            $events = DB::table('events')->get();

            foreach ($events as $event) {
                $post = DB::table('posts')->where('id', $event->post_id)->first();

                DB::table('events')
                    ->where('id', $event->id)
                    ->update([
                        'name' => $post->name,
                        'description' => $post->description,
                        'image' => $post->image,
                        'thumbnail' => $post->thumbnail,
                    ]);

                DB::table('posts')->where('id', $post->id)->delete();
            }
        });
    }
};