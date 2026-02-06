<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\RoadtripsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RoadtripsTable Test Case
 */
class RoadtripsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RoadtripsTable
     */
    protected $Roadtrips;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Roadtrips',
        'app.Users',
        'app.Comments',
        'app.Favorites',
        'app.Histories',
        'app.SharedRoadtrips',
        'app.Trips',
        'app.PointsOfInterests',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Roadtrips') ? [] : ['className' => RoadtripsTable::class];
        $this->Roadtrips = $this->getTableLocator()->get('Roadtrips', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Roadtrips);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\RoadtripsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\RoadtripsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
