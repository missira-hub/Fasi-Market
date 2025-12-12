<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Add reply_to_message_id if it doesn't exist
            if (!Schema::hasColumn('messages', 'reply_to_message_id')) {
                $table->unsignedBigInteger('reply_to_message_id')->nullable()->after('id');
                $table->foreign('reply_to_message_id')
                      ->references('id')
                      ->on('messages')
                      ->onDelete('set null');
            }

            // Add reply_to_sender_name if it doesn't exist
            if (!Schema::hasColumn('messages', 'reply_to_sender_name')) {
                $table->string('reply_to_sender_name')->nullable()->after('reply_to_message_id');
            }

            // Add reply_to_message_text if it doesn't exist
            if (!Schema::hasColumn('messages', 'reply_to_message_text')) {
                $table->text('reply_to_message_text')->nullable()->after('reply_to_sender_name');
            }
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Drop foreign key first
            if (Schema::hasColumn('messages', 'reply_to_message_id')) {
                $table->dropForeign(['reply_to_message_id']);
                $table->dropColumn('reply_to_message_id');
            }

            if (Schema::hasColumn('messages', 'reply_to_sender_name')) {
                $table->dropColumn('reply_to_sender_name');
            }

            if (Schema::hasColumn('messages', 'reply_to_message_text')) {
                $table->dropColumn('reply_to_message_text');
            }
        });
    }
};

