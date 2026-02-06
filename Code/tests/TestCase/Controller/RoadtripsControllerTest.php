<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RoadtripsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\RoadtripsController Test Case
 *
 * @link \App\Controller\RoadtripsController
 */
class RoadtripsControllerTest extends TestCase
{
    use IntegrationTestTrait;

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
        'app.PointsOfInterestsRoadtrips',
    ];

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\RoadtripsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\RoadtripsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\RoadtripsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\RoadtripsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\RoadtripsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
