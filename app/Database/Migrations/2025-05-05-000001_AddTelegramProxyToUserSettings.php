<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTelegramProxyToUserSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user_settings', [
            'telegram_proxy' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'use_telegram_proxy' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user_settings', 'telegram_proxy');
        $this->forge->dropColumn('user_settings', 'use_telegram_proxy');
    }
}