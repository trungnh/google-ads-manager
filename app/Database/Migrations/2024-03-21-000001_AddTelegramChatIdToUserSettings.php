<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTelegramChatIdToUserSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user_settings', [
            'telegram_chat_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user_settings', 'telegram_chat_id');
    }
} 