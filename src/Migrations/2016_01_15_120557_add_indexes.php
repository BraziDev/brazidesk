<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Add indicies for better performance.
 *
 * Class AddIndexes
 */
class AddIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('brazidesk', function (Blueprint $table) {
            $table->index('subject');
            $table->index('status_id');
            $table->index('priority_id');
            $table->index('user_id');
            $table->index('agent_id');
            $table->index('category_id');
            $table->index('completed_at');
        });

        Schema::table('brazidesk_comments', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('ticket_id');
        });

        Schema::table('brazidesk_settings', function (Blueprint $table) {
            $table->index('lang');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('brazidesk', function (Blueprint $table) {
            $table->dropIndex('brazidesk_subject_index');
            $table->dropIndex('brazidesk_status_id_index');
            $table->dropIndex('brazidesk_priority_id_index');
            $table->dropIndex('brazidesk_user_id_index');
            $table->dropIndex('brazidesk_agent_id_index');
            $table->dropIndex('brazidesk_category_id_index');
            $table->dropIndex('brazidesk_completed_at_index');
        });

        Schema::table('brazidesk_comments', function (Blueprint $table) {
            $table->dropIndex('brazidesk_comments_user_id_index');
            $table->dropIndex('brazidesk_comments_ticket_id_index');
        });

        Schema::table('brazidesk_settings', function (Blueprint $table) {
            $table->dropIndex('brazidesk_settings_lang_index');
            $table->dropIndex('brazidesk_settings_slug_index');
        });
    }
}
