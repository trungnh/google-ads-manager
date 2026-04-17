<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpireDateToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'expire_date' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'expire_date');
    }
}
