<?php
namespace Brown298\DataTablesBundle\Tests\Service;

use Brown298\DataTablesBundle\Service\TableManager;
use Phake;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class TableManagerTest
 * @package Brown298\DataTablesBundle\Tests\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class TableManagerTest extends AbstractBaseTest
{
    /**
     * @var \Doctrine\ORM\EntityManger
     */
    protected $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Common\Annotations\AnnotationReader
     */
    protected $reader;

    /**
     * @var \Brown298\DataTablesBundle\Service\TableManager
     */
    protected $tableManager;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->container = Phake::Mock('\Symfony\Component\DependencyInjection\ContainerInterface');
        $this->em        = Phake::Mock('\Doctrine\ORM\EntityManager');
        $this->reader        = Phake::Mock('\Doctrine\Common\Annotations\AnnotationReader');

        $this->tableManager = new TableManager($this->container, $this->reader, array(), array(), $this->em);
    }

    /**
     * testCreate
     */
    public function testCreate()
    {
        $this->assertInstanceOf('Brown298\DataTablesBundle\Service\TableManager', $this->tableManager);
    }

    /**
     * testGetBundles
     */
    public function testGetBundles()
    {
        $expectedResults = 'test';
        Phake::when($this->container)->getParameter('kernel.bundles')->thenReturn($expectedResults);

        $result  = $this->callProtected($this->tableManager,'getBundles');

        $this->assertEquals($expectedResults, $result);
    }

    /**
     * testHasTable
     */
    public function testHasTable()
    {
        $tables = array('test'=>'table');
        $this->setProtectedValue($this->tableManager,'tables', $tables);
        $this->assertTrue($this->tableManager->hasTable('test'));
        $this->assertFalse($this->tableManager->hasTable('testing'));
    }



}