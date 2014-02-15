<?php
namespace Brown298\DataTablesBundle\Tests\Service;

use Brown298\DataTablesBundle\Service\AbstractTableBuilder;
use Brown298\DataTablesBundle\Service\TableBuilderInterface;
use \Phake;
use \Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class AbstractTableBuilderTest
 * @package Brown298\DataTablesBundle\Tests\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class AbstractTableBuilderTest extends AbstractBaseTest
{

    /**
     * @var \Brown298\DataTablesBundle\MetaData\Table
     */
    protected $table;

    /**
     * @var \Doctrine\ORM\EntityManger
     */
    protected $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var tableBuilder
     */
    protected $tableBuilder;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->table     = Phake::Mock('Brown298\DataTablesBundle\MetaData\Table');
        $this->container = Phake::Mock('\Symfony\Component\DependencyInjection\ContainerInterface');
        $this->em        = Phake::Mock('\Doctrine\ORM\EntityManager');

        $this->tableBuilder = new tableBuilder();
        $this->tableBuilder->setContainer($this->container);
        $this->tableBuilder->setEm($this->em);
    }


    /**
     * testCreate
     */
    public function testCreate()
    {
        $this->assertInstanceOf('Brown298\DataTablesBundle\Service\AbstractTableBuilder', $this->tableBuilder);
        $this->assertInstanceOf('Brown298\DataTablesBundle\Service\TableBuilderInterface', $this->tableBuilder);
    }

    /**
     * testBuildClassNameReturnsDefault
     */
    public function testBuildClassNameReturnsDefault()
    {
        $expectedResult = 'Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable';
        $result = $this->callProtected($this->tableBuilder, 'getClassName', array($this->table));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testBuildClassNameReturnsClass
     */
    public function testBuildClassNameReturnsClass()
    {
        $expectedResult = 'test';
        $this->table->class = $expectedResult;
        $result = $this->callProtected($this->tableBuilder, 'getClassName', array($this->table));
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testBuildTableEmptyArgs
     */
    public function testBuildTableEmptyArgs()
    {
        $this->setProtectedValue($this->tableBuilder, 'args', array('test'));
        $this->callProtected($this->tableBuilder,'buildTable');

        $table = $this->getProtectedValue($this->tableBuilder, 'table');
        $this->assertInstanceOf('Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable', $table);
        $this->assertTrue($table->hydrateObjects);
        $this->assertEquals($this->em, $this->getProtectedValue($table,'em'));
        $this->assertEquals($this->container,  $this->getProtectedValue($table,'container'));
    }

    /**
     * testBuildGetsDefaultTable
     */
    public function testBuildGetsDefaultTable()
    {
        $args  = array('test');
        $table = $this->tableBuilder->build($args);
        $this->assertInstanceOf('Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable', $table);
    }

    /**
     * buildTableArgs
     */
    public function testBuildTableArgs()
    {
        $this->setProtectedValue($this->tableBuilder, 'args', array('test',array('anArg')));
        $this->callProtected($this->tableBuilder,'buildTable');

        $table = $this->getProtectedValue($this->tableBuilder, 'table');
        $this->assertInstanceOf('Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable', $table);
        $this->assertTrue($table->hydrateObjects);
        $this->assertEquals($this->em, $this->getProtectedValue($table,'em'));
        $this->assertEquals($this->container,  $this->getProtectedValue($table,'container'));
    }
}

/**
 * Class tableBuilder
 * @package Brown298\DataTablesBundle\Tests\Service
 */
class tableBuilder extends AbstractTableBuilder implements TableBuilderInterface
{

}