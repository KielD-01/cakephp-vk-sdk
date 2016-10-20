<?php
namespace App\Test\TestCase\Controller\Component;

use App\Controller\Component\VkAuthComponent;
use Cake\Controller\ComponentRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Component\VkAuthComponent Test Case
 */
class VkAuthComponentTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Controller\Component\VkAuthComponent
     */
    public $VkAuth;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $registry = new ComponentRegistry();
        $this->VkAuth = new VkAuthComponent($registry);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->VkAuth);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
