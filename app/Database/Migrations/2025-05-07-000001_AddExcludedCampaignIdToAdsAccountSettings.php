<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExcludedCampaignIdToAdsAccountSettings extends Migration
{
    public function up()
    {
        $this->forge->addColumn('ads_account_settings', [
            'exclude_campaign_ids' => [
                'type' => 'TEXT',
                'null' => true
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('ads_account_settings', 'exclude_campaign_ids');
    }
} 