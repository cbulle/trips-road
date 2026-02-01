<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\GeocodedPlacesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\GeocodedPlacesTable Test Case
 */
class GeocodedPlacesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\GeocodedPlacesTable
     */
    protected $GeocodedPlaces;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.GeocodedPlaces',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('GeocodedPlaces') ? [] : ['className' => GeocodedPlacesTable::class];
        $this->GeocodedPlaces = $this->getTableLocator()->get('GeocodedPlaces', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->GeocodedPlaces);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\GeocodedPlacesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\GeocodedPlacesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
