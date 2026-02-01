<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FavoritePlacesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FavoritePlacesTable Test Case
 */
class FavoritePlacesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FavoritePlacesTable
     */
    protected $FavoritePlaces;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.FavoritePlaces',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('FavoritePlaces') ? [] : ['className' => FavoritePlacesTable::class];
        $this->FavoritePlaces = $this->getTableLocator()->get('FavoritePlaces', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->FavoritePlaces);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\FavoritePlacesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\FavoritePlacesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
