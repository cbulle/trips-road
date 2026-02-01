<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TripsFixture
 */
class TripsFixture extends TestFixture
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
                'order_number' => 1,
                'title' => 'Lorem ipsum dolor sit amet',
                'departure' => 'Lorem ipsum dolor sit amet',
                'arrival' => 'Lorem ipsum dolor sit amet',
                'created' => 1769933093,
                'transport_mode' => 'Lorem ipsum dolor sit amet',
                'roadtrip_id' => 1,
                'avoid_highways' => 1,
                'avoid_tolls' => 1,
                'departure_time' => '08:04:53',
            ],
        ];
        parent::init();
    }
}
