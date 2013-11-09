<?php
namespace Brown298\DataTablesBundle\Tests\Service;

use \Brown298\DataTablesBundle\Service\ServerProcessService;
use \Phake;
use \Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class ServerProcessServiceTest
 *
 * @package Brown298\DataTablesBundle\Tests\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class ServerProcessServiceTest extends AbstractBaseTest
{

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var \Doctrine\ORM\AbstractQuery
     */
    protected $query;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $requestParameters;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Brown298\DataTablesBundle\Service\ServerProcessService
     */
    protected $service;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->service           = new ServerProcessService();
        $this->request           = Phake::mock('\Symfony\Component\HttpFoundation\Request');
        $this->queryBuilder      = Phake::mock('Doctrine\ORM\QueryBuilder');
        $this->query             = Phake::mock('Doctrine\ORM\AbstractQuery');
        $this->requestParameters = Phake::mock('Brown298\DataTablesBundle\Model\RequestParameterBag');
        $this->logger            = Phake::mock('Psr\Log\LoggerInterface');

        $this->service->setRequest($this->request);
    }

    /**
     * testCreate
     */
    public function testCreate()
    {
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Service\ServerProcessService', $this->service);
    }

    /**
     * testGetSetData
     */
    public function testGetSetData()
    {
        $data = array('test');
        $this->service->setData($data);
        $this->assertEquals($data, $this->service->getData());
    }

    /**
     * testGetSetRequest
     */
    public function testGetSetRequest()
    {
        $this->assertEquals($this->request, $this->service->getRequest());
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Model\RequestParameterBag', $this->service->getRequestParameters());
    }

    /**
     * testGetSetColumnsEmpty
     */
    public function testGetSetColumnsEmpty()
    {
        $this->service->setColumns(array());
        $this->assertEquals(array(), $this->service->getColumns());
    }

    /**
     * testGetSetColumnsValue
     */
    public function testGetSetColumnsValue()
    {
        $this->service->setColumns(array('test'));
        $this->assertEquals(array('test'), $this->service->getColumns());
    }

    /**
     * testAddColumn
     */
    public function testAddColumn()
    {
        $this->service->addColumn('test','123');
        $this->assertEquals(array('test'=>'123'), $this->service->getColumns());
    }


    /**
     * testGetResponseParameters
     */
    public function testGetResponseParameters()
    {
        $this->assertNull($this->service->getResponseParameters());

        $responseParameters = Phake::mock('Brown298\DataTablesBundle\Model\ResponseParameterBag');
        $this->setProtectedValue($this->service, 'responseParameters', $responseParameters);

        $this->assertEquals($responseParameters, $this->service->getResponseParameters());
    }


    /**
     * testProcessWithDataEmpty
     */
    public function testProcessWithDataEmpty()
    {
        $data          = array();
        $dataFormatter = null;

        $this->service->setData($data);
        $result = $this->service->process($dataFormatter);

        $this->assertEquals(array(
            'sEcho'                => null,
            'iTotalRecords'        => 0,
            'iTotalDisplayRecords' => 0,
            'aaData'               => array(),
        ), $result);
    }

    /**
     * testProcessWithData
     */
    public function testProcessWithData()
    {
        $data          = array('test');
        $dataFormatter = null;
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->requestParameters)->getDisplayLength()->thenReturn(10);

        $this->service->setData($data);
        $result = $this->service->process($dataFormatter);

        $this->assertEquals(array(
            'sEcho'                => null,
            'iTotalRecords'        => 1,
            'iTotalDisplayRecords' => 1,
            'aaData'               => array('test'),
        ), $result);
    }

    /**
     * testProcessCallsFormatter
     *
     * ensure the formatting function gets called
     */
    public function testProcessCallsFormatter()
    {
        $data          = array('test');
        $dataFormatter = function($data) {
            return array('123');
        };
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->requestParameters)->getDisplayLength()->thenReturn(10);

        $this->service->setData($data);
        $result = $this->service->process($dataFormatter);

        $this->assertEquals(array(
            'sEcho'                => null,
            'iTotalRecords'        => 1,
            'iTotalDisplayRecords' => 1,
            'aaData'               => array('123'),
        ), $result);
    }

    /**
     * testDebug
     */
    public function testDebug()
    {
        $this->service->setLogger($this->logger);

        $this->service->debug('test');

        Phake::verify($this->logger)->debug('test');
    }

    /**
     * testProcessQueryBuilder
     *
     * test that the process works with a query builder
     */
    public function testProcessQueryBuilder()
    {
        $dataFormatter = null;
        $this->service->setQueryBuilder($this->queryBuilder);
        Phake::when($this->queryBuilder)->select(Phake::anyParameters())->thenReturn($this->queryBuilder);
        Phake::when($this->queryBuilder)->getQuery()->thenReturn($this->query);
        Phake::when($this->query)->getArrayResult()->thenReturn(array())->thenReturn(array(array()));

        $result = $this->service->process($dataFormatter);

        $this->assertEquals(array(
            'sEcho'                => null,
            'iTotalRecords'        => null,
            'iTotalDisplayRecords' => null,
            'aaData'               => array(),
        ), $result);
    }

    /**
     * testProcessQueryBuilderEntity
     *
     * test that the process works with a query builder requesting an entity
     */
    public function testProcessQueryBuilderEntity()
    {
        $dataFormatter = null;
        $this->service->setQueryBuilder($this->queryBuilder);
        Phake::when($this->queryBuilder)->select(Phake::anyParameters())->thenReturn($this->queryBuilder);
        Phake::when($this->queryBuilder)->getQuery()->thenReturn($this->query);
        Phake::when($this->query)->getArrayResult()->thenReturn(array(array()));
        Phake::when($this->query)->getResult()->thenReturn(array());

        $result = $this->service->process($dataFormatter, true);

        $this->assertEquals(array(
            'sEcho'                => null,
            'iTotalRecords'        => null,
            'iTotalDisplayRecords' => null,
            'aaData'               => array(),
        ), $result);

        Phake::verify($this->query)->getResult();
    }

    /**
     * testSetQueryBuilder
     */
    public function testSetQueryBuilder()
    {
        Phake::when($this->queryBuilder)->getDQLPart('select')->thenReturn(array());
        $this->service->setQueryBuilder($this->queryBuilder);

        $processor = $this->getProtectedValue($this->service, 'processor');
        $this->assertEquals($this->queryBuilder, $this->getProtectedValue($processor, 'queryBuilder'));

        // ensure we parse the columsn
        Phake::verify($this->queryBuilder)->getDQLPart('select');
    }

}