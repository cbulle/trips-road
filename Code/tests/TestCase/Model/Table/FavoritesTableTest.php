<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FavoritesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FavoritesTable Test Case
 */
class FavoritesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FavoritesTable
     */
    protected $Favorites;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Favorites',
        'app.Users',
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
        $config = $this->getTableLocator()->exists('Favorites') ? [] : ['className' => FavoritesTable::class];
        $this->Favorites = $this->getTableLocator()->get('Favorites', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Favorites);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\FavoritesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\FavoritesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
