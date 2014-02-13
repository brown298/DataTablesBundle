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
     * @var \Brown298\DataTablesBundle\Service\ServerProcessService
     */
    protected $service;

    /**
     * @Mock
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @Mock
     * @var \Brown298\DataTablesBundle\MetaData\Column
     */
    protected $column;

    /**
     * @Mock
     * @var \Brown298\DataTablesBundle\MetaData\Format
     */
    protected $format;

    /**
     * @Mock
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $renderer;

    /**
     * @Mock
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @Mock
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repo;

    /**
     * @Mock
     * @var \Brown298\DataTablesBundle\MetaData\Table
     */
    protected $table;

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


    /**
     * testColumnRenderingNoFormatting
     */
    public function testColumnRenderingNoFormatting()
    {
        $metaData             = array('columns'=> array($this->column));
        $row                  = array('test' => 'result');
        $this->column->source = 'data.test';

        $this->dataTable->setMetaData($metaData);
        $result = $this->dataTable->getColumnRendering($row);

        $this->assertEquals(array('result'), $result);
    }

    /**
     * testColumnRenderingFormatNoTemplate
     */
    public function testColumnRenderingFormatNoTemplate()
    {
        $metaData                 = array('columns'=> array($this->column));
        $row                      = array('test' => 'result', 'value2' => 'result2');
        $this->column->format     = $this->format;
        $this->format->dataFields = array('result1' => 'data.test', 'result2' =>'data.value2');

        $this->dataTable->setMetaData($metaData);
        $result = $this->dataTable->getColumnRendering($row);

        $this->assertEquals(array(
            array('result1' => 'result', 'result2' => 'result2'),
        ), $result);
    }

    public function testColumnRenderingFormatTemplate()
    {
        $metaData                 = array('columns'=> array($this->column));
        $row                      = array('test' => 'result', 'value2' => 'result2');
        $this->column->format     = $this->format;
        $this->format->dataFields = array('result1' => 'data.test', 'result2' =>'data.value2');
        $this->format->template   = 'template';

        Phake::when($this->container)->get('templating')->thenReturn($this->renderer);
        Phake::when($this->renderer)->render('template', $this->anything())->thenReturn('renderResult');

        $this->dataTable->setMetaData($metaData);
        $result = $this->dataTable->getColumnRendering($row);

        $this->assertEquals(array('renderResult'), $result);
        Phake::verify($this->renderer)->render($this->anything(), array('result1' => 'result', 'result2' => 'result2'));
    }

    /**
     * testGetSetMetaData
     */
    public function testGetSetMetaData()
    {
        $expectedData = array('test');
        $this->assertNull($this->dataTable->getMetaData());
        $this->dataTable->setMetaData($expectedData);
        $this->assertEquals($expectedData, $this->dataTable->getMetaData());
        $this->dataTable->setMetaData(null);
        $this->assertNull($this->dataTable->getMetaData());
    }

    /**
     * testGetDataValueArray
     */
    public function testGetDataValueArray()
    {
        $row = array('test' => 'result');
        $result = $this->callProtected($this->dataTable,'getDataValue', array($row, 'data.test'));
        $this->assertEquals('result', $result);
    }

    /**
     * testGetDataOther
     */
    public function testGetDataOther()
    {
        $row = 'test';
        $result = $this->callProtected($this->dataTable,'getDataValue', array($row, 'data.test'));
        $this->assertNull($result);
    }

    /**
     * testGetValueObjectUnknown
     */
    public function testGetValueObjectUnknown()
    {
        $expectedResult = 'Unknown';
        $row            = array();
        $source         = '';

        $result = $this->callProtected($this->dataTable, 'getObjectValue', array($row, $source));

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * testGetSetEm
     */
    public function testGetSetEm()
    {
        $this->assertNull($this->dataTable->getEm());
        $this->dataTable->setEm($this->em);
        $this->assertEquals($this->em, $this->dataTable->getEm());
        $this->dataTable->setEm(null);
        $this->dataTable->setEm($this->em);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetQueryBuilderThrowsError()
    {
        $this->dataTable->getQueryBuilder();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetQueryBulderNullEmThrowsError()
    {
        $this->dataTable->setMetaData(array('table' => $this->table));
        $this->table->entity = 'test';

        $this->dataTable->getQueryBuilder();
    }

    /**
     * testGetQueryBuilder
     * @expectedException \RuntimeException
     */
    public function testGetQueryBuilderMissingFunction()
    {
        $this->dataTable->setMetaData(array('table' => $this->table));
        $this->table->entity       = 'test';
        $this->table->queryBuilder = 'testing';
        $this->dataTable->setEm($this->em);

        $this->dataTable->getQueryBuilder();
    }

    /**
     * testGetQueryBuilderFunction
     */
    public function testGetQueryBuilderFunction()
    {
        $expectedResults = 'asdf';
        $this->dataTable->setMetaData(array('table' => $this->table));
        $this->table->entity       = 'test';
        $this->table->queryBuilder = 'clear';
        $this->dataTable->setEm($this->em);
        $this->repo->testing = function() {};
        Phake::when($this->em)->getRepository(Phake::anyParameters())->thenReturn($this->repo);
        Phake::when($this->repo)->clear(Phake::anyParameters())->thenReturn($expectedResults);

        $result = $this->dataTable->getQueryBuilder();

        $this->assertEquals($expectedResults, $result);
    }

    /**
     * testGetQueryBuilderGenerate
     */
    public function testGetQueryBuilderGenerate()
    {
        $expectedResults = 'asdf';
        $this->dataTable->setMetaData(array('table' => $this->table));
        $this->table->entity       = 'test';
        $this->dataTable->setEm($this->em);
        $this->repo->testing = function() {};
        Phake::when($this->em)->getRepository(Phake::anyParameters())->thenReturn($this->repo);
        Phake::when($this->repo)->createQueryBuilder(Phake::anyParameters())->thenReturn($expectedResults);

        $result = $this->dataTable->getQueryBuilder();

        $this->assertEquals($expectedResults, $result);
    }
}
