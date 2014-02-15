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
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private $kernel;

    /**
     * @var Symfony\Component\HttpKernel\Bundle\Bundle
     */
    protected $bundle;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->container = Phake::Mock('\Symfony\Component\DependencyInjection\ContainerInterface');
        $this->em        = Phake::Mock('\Doctrine\ORM\EntityManager');
        $this->reader    = Phake::Mock('\Doctrine\Common\Annotations\AnnotationReader');
        $this->kernel    = Phake::Mock('\Symfony\Component\HttpKernel\Kernel');
        $this->bundle    = Phake::Mock('Symfony\Component\HttpKernel\Bundle\Bundle');

        Phake::when($this->container)->get('kernel')->thenReturn($this->kernel);

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

    /**
     * testHasBuiltTableFalse
     */
    public function testHasBuiltTableFalse()
    {
        $tables = array();
        $this->setProtectedValue($this->tableManager,'tables', $tables);
        $this->assertFalse($this->tableManager->hasBuiltTable('test'));
    }

    /**
     * testHasBuiltTableTrue
     */
    public function testHasBuiltTableTrue()
    {
        $tables = array('test' =>'value');
        $this->setProtectedValue($this->tableManager,'tables', $tables);
        $this->setProtectedValue($this->tableManager,'builtTables', $tables);
        $this->assertTrue($this->tableManager->hasBuiltTable('test'));
    }

    /**
     * testGetBundleDirectories
     */
    public function testGetBundleDirectories()
    {
        $expectedResult = 'test';
        $bundles        = array('bundle' => 'namespace');
        Phake::when($this->container)->getParameter('kernel.bundles')->thenReturn($bundles);
        Phake::when($this->kernel)->getBundle('bundle')->thenReturn($this->bundle);
        Phake::when($this->bundle)->getPath()->thenReturn($expectedResult);

        $result = $this->callProtected($this->tableManager,'getBundleDirectories');

        $this->assertEquals(array('bundle'=> $expectedResult), $result);
    }
}