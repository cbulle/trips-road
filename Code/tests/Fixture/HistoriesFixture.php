<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * HistoriesFixture
 */
class HistoriesFixture extends TestFixture
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
                'roadtrip_id' => 1,
                'created' => 1771920934,
            ],
        ];
        parent::init();
    }
}
