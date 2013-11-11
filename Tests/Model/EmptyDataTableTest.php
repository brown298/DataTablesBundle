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
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();
        $this->container         = Phake::mock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->request           = Phake::mock('Symfony\Component\HttpFoundation\Request');
        $this->dataTablesService = Phake::mock('Brown298\DataTablesBundle\Service\ServerProcessService');
        $this->logger            = Phake::mock('\Psr\Log\LoggerInterface');

        Phake::when($this->container)->get('logger')->thenReturn($this->logger);

        $this->dataTable = new EmptyDataTable();
    }

    /**
     * testConstructSetsColumns
     */
    public function testConstructSetsColumns()
    {
        $columns = array('test');

        $this->dataTable = new EmptyDataTable($columns);

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
     * testSetContainer
     */
    public function testSetContainer()
    {
        $this->dataTable->setContainer($this->container);
        $this->assertEquals($this->container, $this->getProtectedValue($this->dataTable, 'container'));
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
     * testGetSetDataFormatter
     */
    public function testGetSetDataFormatter()
    {
        $formatter = function($test) { return $test; };

        $this->dataTable->setDataFormatter($formatter);
        $this->assertEquals($formatter, $this->dataTable->getDataFormatter());
    }

    /**
     * testGetJsonResponseNullFormatter
     */
    public function testGetJsonResponseNullFormatter()
    {
        $result = $this->dataTable->getJsonResponse($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $result);
    }

    /**
     * testProcessRequestNonAjax
     */
    public function testProcessRequestNonAjax()
    {
        $this->assertFalse($this->dataTable->processRequest($this->request));
    }

    /**
     * testProcessRequestNullDataFormatter
     */
    public function testProcessRequestNullDataFormatter()
    {
        Phake::when($this->request)->isXmlHttpRequest()->thenReturn(true);

        $result = $this->dataTable->processRequest($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $result);
    }

    /**
     * testProcessRequestDataFormatter
     */
    public function testProcessRequestDataFormatter()
    {
        Phake::when($this->request)->isXmlHttpRequest()->thenReturn(true);
        $result = $this->dataTable->processRequest($this->request, function ($data){});

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $result);
    }

    /**
     * testProcessRequestDataFormatter
     */
    public function testProcessRequestGetDataFormatter()
    {
        Phake::when($this->request)->isXmlHttpRequest()->thenReturn(true);
        $this->setProtectedValue($this->dataTable,'dataFormatter',function ($data){});

        $result = $this->dataTable->processRequest($this->request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $result);
    }
}