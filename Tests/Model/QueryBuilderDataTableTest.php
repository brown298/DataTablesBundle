<?php
namespace Brown298\DataTablesBundle\Tests\Model;

use Phake;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;
use Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable;

/**
 * Class QueryBuilderDataTableTest
 *
 * @package Brown298\DataTablesBundle\Tests\Model
 * @author  John Brown <brown.john@gmail.com>
 */
class QueryBuilderDataTableTest extends AbstractBaseTest
{
    /**
     * @Mock
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @Mock
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @Mock
     * @var \Brown298\DataTablesBundle\Service\ServerProcessService
     */
    protected $dataTablesService;

    /**
     * @var \Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable
     */
    protected $dataTable;

    /**
     * @Mock
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @Mock
     * @var Brown298\DataTablesBundle\Service\ServerProcessService
     */
    protected $service;

    /**
     * @Mock
     * @var Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * setUp
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->dataTable = new QueryBuilderDataTable();
        $this->dataTable->setContainer($this->container);
        Phake::when($this->container)->get('data_tables.service')->thenReturn($this->service);
    }

    /**
     * testCreate
     *
     */
    public function testCreate()
    {
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable', $this->dataTable);
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Model\DataTable\QueryBuilderDataTableInterface', $this->dataTable);
    }

    /**
     * testGetSetQueryBuilder
     *
     */
    public function testGetSetQueryBuilder()
    {
        $this->dataTable->setQueryBuilder($this->queryBuilder);
        $qb = $this->dataTable->getQueryBuilder($this->request);
        $this->assertEquals($this->queryBuilder, $qb);
        $this->assertInstanceOf('\Doctrine\ORM\QueryBuilder', $qb);
    }

    /**
     * testExecute
     *
     */
    public function testExecute()
    {
        $this->dataTable->execute($this->service, 'test');

        Phake::verify($this->service)->process('test', false);
    }

    /**
     * testGetDataWithoutQueryBuilderReturnNull
     *
     */
    public function testGetDataWithoutQueryBuilderReturnNull()
    {
        $this->assertNull($this->dataTable->getData($this->request));
    }

    /**
     * testGetDataByQueryBuilderExecutes
     *
     */
    public function testGetDataByQueryBuilderExecutes()
    {
        $this->callProtected($this->dataTable,'getDataByQueryBuilder', array($this->request, $this->queryBuilder));

        Phake::verify($this->service)->process(null, false);
    }

    /**
     * testGetDataWithQueryBuilderExecutes
     *
     */
    public function testGetDataWithQueryBuilderExecutes()
    {
        $this->dataTable->setQueryBuilder($this->queryBuilder);
        Phake::when($this->service)->process(Phake::anyParameters())->thenReturn('test');

        $result = $this->dataTable->getData($this->request);

        Phake::verify($this->service)->process(null, false);
        $this->assertEquals('test', $result);
    }

    /**
     * testGetDataByQueryBuilderSetsRequest
     *
     */
    public function testGetDataByQueryBuilderSetsRequest()
    {
        $this->callProtected($this->dataTable,'getDataByQueryBuilder', array($this->request, $this->queryBuilder));
        Phake::verify($this->service)->setRequest($this->request);
    }

    /**
     * testGetDataByQueryBuilderSetsQueryBuilder
     *
     */
    public function testGetDataByQueryBuilderSetsQueryBuilder()
    {
        $this->callProtected($this->dataTable,'getDataByQueryBuilder', array($this->request, $this->queryBuilder));
        Phake::verify($this->service)->setQueryBuilder($this->queryBuilder);
    }

    /**
     * testGetDataByQueryBuilderSetsColumns
     *
     */
    public function testGetDataByQueryBuilderSetsColumns()
    {
        $this->callProtected($this->dataTable,'getDataByQueryBuilder', array($this->request, $this->queryBuilder));
        Phake::verify($this->service)->setColumns(Phake::anyParameters());
    }

    /**
     * testGetDataByQueryBuilderSetsLogger
     *
     */
    public function testGetDataByQueryBuilderSetsLogger()
    {
        Phake::when($this->container)->has('logger')->thenReturn(true);
        Phake::when($this->container)->get('logger')->thenReturn($this->logger);

        $this->callProtected($this->dataTable,'getDataByQueryBuilder', array($this->request, $this->queryBuilder));

        Phake::verify($this->service)->setLogger($this->logger);
    }
}