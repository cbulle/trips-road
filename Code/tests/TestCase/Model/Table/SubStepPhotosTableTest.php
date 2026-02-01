<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SubStepPhotosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SubStepPhotosTable Test Case
 */
class SubStepPhotosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SubStepPhotosTable
     */
    protected $SubStepPhotos;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.SubStepPhotos',
        'app.SubSteps',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SubStepPhotos') ? [] : ['className' => SubStepPhotosTable::class];
        $this->SubStepPhotos = $this->getTableLocator()->get('SubStepPhotos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SubStepPhotos);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\SubStepPhotosTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\SubStepPhotosTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
