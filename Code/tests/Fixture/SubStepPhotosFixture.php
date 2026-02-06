<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SubStepPhotosFixture
 */
class SubStepPhotosFixture extends TestFixture
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
                'sub_step_id' => 1,
                'photo' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
