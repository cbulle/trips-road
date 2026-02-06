<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FriendsFixture
 */
class FriendsFixture extends TestFixture
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
                'user_id' => 1,
                'friend_id' => 1,
                'status' => 'Lorem ipsum dolor sit amet',
                'created' => 1769933266,
            ],
        ];
        parent::init();
    }
}
