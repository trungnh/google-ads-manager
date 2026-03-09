<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRevenueReportTables extends Migration
{
    public function up()
    {
        // 1. Table: products
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'product_code' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'unique' => true,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'shipping_fee' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'import_price' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'selling_price' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'return_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '5,4', // Lưu % dạng thập phân (ví dụ 1.5% = 0.015)
                'default' => 0,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
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
        $this->forge->createTable('products');

        // 2. Table: product_ads_accounts (Mapping N-N)
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'customer_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
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
        $this->forge->addUniqueKey(['product_id', 'customer_id']);
        $this->forge->createTable('product_ads_accounts');

        // 3. Table: revenue_reports
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
            ],
            'month' => [
                'type' => 'TINYINT',
                'constraint' => 2,
            ],
            'year' => [
                'type' => 'SMALLINT',
                'constraint' => 4,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            // Snapshot cấu hình tại thời điểm tạo báo cáo
            'return_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '5,4',
                'default' => 0,
            ],
            'import_price' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'shipping_fee' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'income_tax' => [
                'type' => 'DECIMAL',
                'constraint' => '5,4',
                'default' => 0,
            ],
            'ads_tax' => [
                'type' => 'DECIMAL',
                'constraint' => '5,4',
                'default' => 0,
            ],
            'payment_fee' => [
                'type' => 'DECIMAL',
                'constraint' => '5,4',
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
        $this->forge->addUniqueKey(['product_id', 'month', 'year']);
        $this->forge->createTable('revenue_reports');

        // 4. Table: revenue_report_daily
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'report_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            // Cho phép user nhập tay
            'orders' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'ads_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'revenue' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            // Calculated fields (optional to save, but good for reporting queries)
            'goods_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'ship_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'return_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'total_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
                'default' => 0,
            ],
            'profit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,4',
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
        $this->forge->addUniqueKey(['report_id', 'date']);
        $this->forge->createTable('revenue_report_daily');
    }

    public function down()
    {
        $this->forge->dropTable('revenue_report_daily', true);
        $this->forge->dropTable('revenue_reports', true);
        $this->forge->dropTable('product_ads_accounts', true);
        $this->forge->dropTable('products', true);
    }
}
