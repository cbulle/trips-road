<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * NotificationPreferencesFixture
 */
class NotificationPreferencesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 1,
                'receive_message_notifications' => 1,
                'receive_email_notifications' => 1,
                'receive_roadtrip_notifications' => 1,
                'receive_file_notifications' => 1,
                'quiet_hours_start' => '08:08:11',
                'quiet_hours_end' => '08:08:11',
                'modified' => 1769933291,
            ],
        ];
        parent::init();
    }
}
