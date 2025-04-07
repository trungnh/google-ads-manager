<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleAndStatusToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'default' => 'user',
                'after' => 'password_hash'
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'default' => 'active',
                'after' => 'role'
            ],
            'last_login' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status'
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'last_login'
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at'
            ]
        ];

        // Kiểm tra xem trường đã tồn tại chưa trước khi thêm
        if (!$this->db->fieldExists('role', 'users')) {
            $this->forge->addColumn('users', $fields);
        }
        
        // Cập nhật dữ liệu - đặt người dùng đầu tiên làm superadmin
        $query = $this->db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1");
        $user = $query->getRow();
        
        if ($user) {
            $this->db->table('users')
                     ->where('id', $user->id)
                     ->update(['role' => 'superadmin']);
            
            // Cập nhật các người dùng còn lại thành admin nếu có
            $this->db->table('users')
                     ->where('id !=', $user->id)
                     ->update(['role' => 'admin']);
        }
    }

    public function down()
    {
        // Xóa các trường nếu cần roll back
        $fields = ['role', 'status', 'last_login', 'created_by', 'deleted_at'];
        
        foreach ($fields as $field) {
            if ($this->db->fieldExists($field, 'users')) {
                $this->forge->dropColumn('users', $field);
            }
        }
    }
} 