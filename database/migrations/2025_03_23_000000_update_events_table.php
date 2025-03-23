<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            // Add a unique constraint to the post_id column
            $table->foreignId('post_id')->unique()->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique(['post_id']);

            // Restore the foreign key without the unique constraint
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
        });
    }
}