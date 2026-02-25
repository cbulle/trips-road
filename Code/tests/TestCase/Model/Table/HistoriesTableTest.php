<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\HistoriesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\HistoriesTable Test Case
 */
class HistoriesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\HistoriesTable
     */
    protected $Histories;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Histories',
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
        $config = $this->getTableLocator()->exists('Histories') ? [] : ['className' => HistoriesTable::class];
        $this->Histories = $this->getTableLocator()->get('Histories', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Histories);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\HistoriesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\HistoriesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
