<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RefactorAdsAccountSettingsJson extends Migration
{
    public function up()
    {
        // 1. Thêm cột integration_settings
        $this->forge->addColumn('ads_account_settings', [
            'integration_settings' => [
                'type' => 'JSON',
                'null' => true,
                'after' => 'exclude_campaign_ids'
            ]
        ]);
        
        // CHÚ Ý: Việc di chuyển dữ liệu từ các cột cũ sang cột JSON sẽ được thực hiện 
        // thông qua Server Command an toàn (php spark migrate:json_data)
        // Việc DROP các cột cũ sẽ được thực hiện ở một Migration riêng sau khi đã Migrate dữ liệu thành công.
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'integration_settings');
    }
}
