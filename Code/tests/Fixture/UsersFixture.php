<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'last_name' => 'Lorem ipsum dolor sit amet',
                'first_name' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'address' => 'Lorem ipsum dolor sit amet',
                'zipcode' => 1,
                'city' => 'Lorem ipsum dolor sit amet',
                'phone' => 'Lorem ipsum dolor ',
                'birth_date' => '2026-02-01',
                'profile_picture' => 'Lorem ipsum dolor sit amet',
                'public_key' => 'Lorem ipsum dolor sit amet',
                'session_id' => 'Lorem ipsum dolor sit amet',
                'username' => 'Lorem ipsum dolor sit amet',
                'reset_token_hash' => 'Lorem ipsum dolor sit amet',
                'reset_expires_at' => '2026-02-01 08:03:46',
                'google_id' => 'Lorem ipsum dolor sit amet',
                'created' => '2026-02-01 08:03:46',
            ],
        ];
        parent::init();
    }
}
