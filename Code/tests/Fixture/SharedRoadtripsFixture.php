<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SharedRoadtripsFixture
 */
class SharedRoadtripsFixture extends TestFixture
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
                'roadtrip_id' => 1,
                'token' => 'Lorem ipsum dolor sit amet',
                'view_count' => 1,
                'created' => 1769933393,
            ],
        ];
        parent::init();
    }
}
