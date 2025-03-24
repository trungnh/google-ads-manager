<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'account_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'auto_optimize' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'cpa_threshold' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'increase_budget' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'gsheet1' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'gsheet_date_col' => [
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => '',
            ],
            'gsheet_phone_col' => [
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => '',
            ],
            'gsheet_value_col' => [
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => '',
            ],
            'gsheet_campaign_col' => [
                'type' => 'CHAR',
                'constraint' => 1,
                'default' => '',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('account_id', 'ads_accounts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ads_account_settings');
    }

    public function down()
    {
        $this->forge->dropTable('ads_account_settings');
    }
} 