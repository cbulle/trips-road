<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\NotificationPreferencesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\NotificationPreferencesTable Test Case
 */
class NotificationPreferencesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\NotificationPreferencesTable
     */
    protected $NotificationPreferences;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.NotificationPreferences',
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
        $config = $this->getTableLocator()->exists('NotificationPreferences') ? [] : ['className' => NotificationPreferencesTable::class];
        $this->NotificationPreferences = $this->getTableLocator()->get('NotificationPreferences', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->NotificationPreferences);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\NotificationPreferencesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\NotificationPreferencesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
