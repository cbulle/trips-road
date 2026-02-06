<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ConversationsFixture
 */
class ConversationsFixture extends TestFixture
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
                'user_one_id' => 1,
                'user_two_id' => 1,
                'modified' => 1769933217,
            ],
        ];
        parent::init();
    }
}
