<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FavoritePlacesFixture
 */
class FavoritePlacesFixture extends TestFixture
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
                'name' => 'Lorem ipsum dolor sit amet',
                'address' => 'Lorem ipsum dolor sit amet',
                'latitude' => 1.5,
                'longitude' => 1.5,
                'category' => 'Lorem ipsum dolor sit amet',
                'created' => 1769933243,
            ],
        ];
        parent::init();
    }
}
