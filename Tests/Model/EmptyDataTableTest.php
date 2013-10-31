<?php
namespace Brown298\DataTablesBundle\Tests\Model;

use Brown298\DataTablesBundle\Model\EmptyDataTable;
use Phake;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class EmptyDataTableTest
 * @package Brown298\DataTablesBundle\Tests\Model
 * @author  John Brown <brown.john@gmail.com>
 */
class EmptyDataTableTest extends AbstractBaseTest
{

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var Brown298\DataTablesBundle\Service\ServerProcessService
     */
    protected $dataTablesService;

    /**
     * @var Brown298\DataTablesBundle\Model\EmptyDataTable
     */
    protected $dataTable;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->em                = Phake::mock('Doctrine\ORM\EntityManager');
        $this->queryBuilder      = Phake::mock('Doctrine\ORM\QueryBuilder');
        $this->container         = Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->request           = Phake::mock('Symfony\Component\HttpFoundation\Request');
        $this->dataTablesService = Phake::mock('Brown298\DataTablesBundle\Service\ServerProcessService');

        $this->dataTable = new EmptyDataTable($this->em);
    }

    /**
     * testConstructSetsColumns
     */
    public function testConstructSetsColumns()
    {
        $columns = array('test');

        $this->dataTable = new EmptyDataTable($this->em, $columns);

        $this->assertEquals($columns, $this->dataTable->getColumns());
    }

    /**
     * testSetColumns
     */
    public function testSetColumns()
    {
        $columns = array('test');

        $this->dataTable->setColumns($columns);

        $this->assertEquals($columns, $this->dataTable->getColumns());
    }

    /**
     * testGetSetEm
     */
    public function testGetSetEm()
    {
        $this->dataTable->setEm($this->em);

        $this->assertEquals($this->em, $this->dataTable->getEm());
    }

    /**
     * testSetContainer
     */
    public function testSetContainer()
    {
        $this->dataTable->setContainer($this->container);
        $this->assertEquals($this->container, $this->getProtectedValue($this->dataTable, 'container'));
    }

    /**
     * testSetQueryBuilder
     */
    public function testSetQueryBuilder()
    {
        $this->dataTable->setQueryBuilder($this->queryBuilder);
        $this->assertEquals($this->queryBuilder, $this->getProtectedValue($this->dataTable, 'queryBuilder'));
    }

    /**
     * testIsAjaxRequest
     */
    public function testIsAjaxRequest()
    {
        $this->assertFalse($this->dataTable->isAjaxRequest($this->request));
        Phake::when($this->request)->isXmlHttpRequest()->thenReturn(true);
        $this->assertTrue($this->dataTable->isAjaxRequest($this->request));
    }

    /**
     * testGetData
     */
    public function testGetData()
    {
        $this->assertEquals(array(), $this->dataTable->getData($this->request));
    }

    /**
     * testGetQueryBuilder
     */
    public function testGetQueryBuilder()
    {
        $this->setProtectedValue($this->dataTable, 'queryBuilder', $this->queryBuilder);
        $this->assertEquals($this->queryBuilder, $this->dataTable->getQueryBuilder($this->request));
    }

    /**
     * testGetSetDataFormatter
     */
    public function testGetSetDataFormatter()
    {
        $formatter = function($test) { return $test; };

        $this->dataTable->setDataFormatter($formatter);
        $this->assertEquals($formatter, $this->dataTable->getDataFormatter());
    }

    /**
     * testGetDatByQueryBuilderEmptyColumns
     */
    public function testGetDatByQueryBuilderEmptyColumns()
    {
        Phake::when($this->container)->get(Phake::anyParameters())->thenReturn($this->dataTablesService);
        $this->dataTable->setContainer($this->container);

        $this->callProtected($this->dataTable, 'getDataByQueryBuilder', array($this->request, $this->queryBuilder, null));

        Phake::verify($this->dataTablesService)->process(null);
        Phake::verify($this->dataTablesService)->setRequest($this->request);
        Phake::verify($this->dataTablesService)->setColumns(array());
    }

}