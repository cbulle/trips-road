<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SharedRoadtripsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SharedRoadtripsTable Test Case
 */
class SharedRoadtripsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SharedRoadtripsTable
     */
    protected $SharedRoadtrips;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.SharedRoadtrips',
        'app.Roadtrips',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('SharedRoadtrips') ? [] : ['className' => SharedRoadtripsTable::class];
        $this->SharedRoadtrips = $this->getTableLocator()->get('SharedRoadtrips', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SharedRoadtrips);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\SharedRoadtripsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\SharedRoadtripsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
