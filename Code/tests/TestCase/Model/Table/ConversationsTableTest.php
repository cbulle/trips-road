<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ConversationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ConversationsTable Test Case
 */
class ConversationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ConversationsTable
     */
    protected $Conversations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Conversations',
        'app.UserOnes',
        'app.UserTwos',
        'app.Messages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Conversations') ? [] : ['className' => ConversationsTable::class];
        $this->Conversations = $this->getTableLocator()->get('Conversations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Conversations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ConversationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ConversationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
