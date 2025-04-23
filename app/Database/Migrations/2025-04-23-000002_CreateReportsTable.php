<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReportsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false
            ],
            'customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'cost' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'conversions' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'conversion_value' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'default' => 0,
            ],
            'running' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'paused' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
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
        $this->forge->addKey('user_id');
        $this->forge->addKey('customer_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('reports');
    }

    public function down()
    {
        $this->forge->dropTable('reports');
    }
} 