<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TripsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TripsTable Test Case
 */
class TripsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TripsTable
     */
    protected $Trips;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Trips',
        'app.Roadtrips',
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
        $config = $this->getTableLocator()->exists('Trips') ? [] : ['className' => TripsTable::class];
        $this->Trips = $this->getTableLocator()->get('Trips', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Trips);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\TripsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\TripsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
