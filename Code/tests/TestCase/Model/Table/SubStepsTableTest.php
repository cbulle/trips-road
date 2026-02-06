<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SubStepsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SubStepsTable Test Case
 */
class SubStepsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SubStepsTable
     */
    protected $SubSteps;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.SubSteps',
        'app.Trips',
        'app.SubStepPhotos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SubSteps') ? [] : ['className' => SubStepsTable::class];
        $this->SubSteps = $this->getTableLocator()->get('SubSteps', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SubSteps);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\SubStepsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\SubStepsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
