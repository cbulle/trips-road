<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PointsOfInterestsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PointsOfInterestsTable Test Case
 */
class PointsOfInterestsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PointsOfInterestsTable
     */
    protected $PointsOfInterests;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.PointsOfInterests',
        'app.Users',
        'app.Comments',
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
        $config = $this->getTableLocator()->exists('PointsOfInterests') ? [] : ['className' => PointsOfInterestsTable::class];
        $this->PointsOfInterests = $this->getTableLocator()->get('PointsOfInterests', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->PointsOfInterests);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\PointsOfInterestsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\PointsOfInterestsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
