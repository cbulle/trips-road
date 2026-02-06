<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * GeocodedPlacesFixture
 */
class GeocodedPlacesFixture extends TestFixture
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
                'name' => 'Lorem ipsum dolor sit amet',
                'latitude' => 1.5,
                'longitude' => 1.5,
                'type' => 'Lorem ipsum dolor sit amet',
                'modified' => '2026-02-01 08:06:41',
            ],
        ];
        parent::init();
    }
}
