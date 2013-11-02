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
     * testAddOrderNoSortingDoesNothing
     */
    public function testAddOrderNoSortingDoesNothing()
    {
        $this->assertEquals($this->queryBuilder, $this->service->addOrder($this->queryBuilder));
    }

    /**
     * testAddOrderWithSortingAddsToQueryBuilder
     */
    public function testAddOrderWithSortingAddsToQueryBuilder()
    {
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->requestParameters)->getSortingColumns()->thenReturn(array('test'=> 'asc'));

        $this->service->addOrder($this->queryBuilder);

        Phake::verify($this->queryBuilder)->addOrderBy('test','asc');
    }

    /**
     * testAddOffsetEmptyDoesNothing
     */
    public function testAddOffsetEmptyDoesNothing()
    {
        $this->assertEquals($this->queryBuilder, $this->service->addOffset($this->queryBuilder));
    }

    /**
     * testAddOffsetAddsToQueryBuilder
     */
    public function testAddOffsetAddsToQueryBuilder()
    {
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->requestParameters)->getOffset()->thenReturn(2);

        $this->service->addOffset($this->queryBuilder);

        Phake::verify($this->queryBuilder)->setFirstResult(2);
    }

    /**
     * testAddLimitsEmptyDoesNothing
     */
    public function testAddLimitsEmptyDoesNothing()
    {
        $this->assertEquals($this->queryBuilder, $this->service->addLimits($this->queryBuilder));
    }

    /**
     * testAddLimitAddsToQueryBuilder
     */
    public function testAddLimitAddsToQueryBuilder()
    {
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->requestParameters)->getDisplayLength()->thenReturn(2);

        $this->service->addLimits($this->queryBuilder);

        Phake::verify($this->queryBuilder)->setMaxResults(2);
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
     * testAddSearchWithoutParameters
     *
     * test that the search without criteria returns the original querybuilder
     */
    public function testAddSearchWithoutParameters()
    {
        $results = $this->service->addSearch($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $results);
        Phake::verify($this->queryBuilder, Phake::never())->andWhere(Phake::anyParameters());
    }

    /**
     * testAddSearchWithSingleParameter
     *
     * ensures a parameter gets added
     */
    public function testAddSearchWithSingleParameter()
    {
        $columns = array(
            'a.id' => 'test',
        );
        Phake::when($this->requestParameters)->getSearchColumns()->thenReturn($columns);
        $this->setProtectedValue($this->service,'requestParameters', $this->requestParameters);

        $results = $this->service->addSearch($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $results);
        Phake::verify($this->queryBuilder)->andWhere('a.id LIKE :a_id');
        Phake::verify($this->queryBuilder)->setParameter('a_id', '%test%');
    }

    /**
     * testAddSearchWithMultipleParametersUsesOr
     *
     * ensures when multiple columns are being searched, they are or joined
     */
    public function testAddSearchWithMultipleParametersUsesAnd()
    {
        $columns = array(
            'a.id'   => 'test',
            'b.name' => '123',
        );
        Phake::when($this->requestParameters)->getSearchColumns()->thenReturn($columns);
        $this->setProtectedValue($this->service,'requestParameters', $this->requestParameters);

        $results = $this->service->addSearch($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $results);
        Phake::verify($this->queryBuilder)->andWhere('a.id LIKE :a_id and b.name LIKE :b_name');
        Phake::verify($this->queryBuilder)->setParameter('a_id', '%test%');
        Phake::verify($this->queryBuilder)->setParameter('b_name', '%123%');
    }

    /**
     * testGetTotalRecords
     */
    public function testGetTotalRecords()
    {
        $expectedResults = 2;
        Phake::when($this->queryBuilder)->getQuery()->thenReturn($this->query);
        Phake::when($this->queryBuilder)->select(Phake::anyParameters())->thenReturn($this->queryBuilder);
        Phake::when($this->query)->getArrayResult()->thenReturn(array(array($expectedResults)));

        $result = $this->service->getTotalRecords($this->queryBuilder, 's');
        Phake::verify($this->queryBuilder)->select(array('count(s.id)'));
        Phake::verify($this->queryBuilder)->setMaxResults(1);

        $this->assertEquals($expectedResults, $result);
    }

    /**
     * testParseColumnsEmptySelect
     */
    public function testParseColumnsEmptySelect()
    {
        $selects = array();
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->queryBuilder)->getDQLPart('select')->thenReturn($selects);

        $this->service->parseColumns($this->queryBuilder);

        Phake::verify($this->requestParameters, Phake::never())->addColumn(Phake::anyParameters());
    }

    /**
     * testParseColumnsWithSelectNoAs
     *
     * ensures that the query runs when the select does not contain an alias
     */
    public function testParseColumnsWithSelectNoAs()
    {
        $selects = array(
            array('a.id'),
        );
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->queryBuilder)->getDQLPart('select')->thenReturn($selects);

        $this->service->parseColumns($this->queryBuilder);

        Phake::verify($this->requestParameters)->addColumn('a.id','a.id');
    }

    /**
     * testParseColumnsWithSelectAs
     */
    public function testParseColumnsWithSelectAs()
    {
        $selects = array(
            array('a.id as ai'),
        );
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        Phake::when($this->queryBuilder)->getDQLPart('select')->thenReturn($selects);

        $this->service->parseColumns($this->queryBuilder);

        Phake::verify($this->requestParameters)->addColumn('a.id','ai');
    }

    /**
     * testSetQueryBuilder
     */
    public function testSetQueryBuilder()
    {
        Phake::when($this->queryBuilder)->getDQLPart('select')->thenReturn(array());
        $this->service->setQueryBuilder($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $this->getProtectedValue($this->service, 'queryBuilder'));

        // ensure we parse the columsn
        Phake::verify($this->queryBuilder)->getDQLPart('select');
    }

    /**
     * testBuildQuery
     */
    public function testBuildQuery()
    {
        Phake::when($this->requestParameters)->getSortingColumns()->thenReturn(array());
        Phake::when($this->requestParameters)->getColumns()->thenReturn(array());
        Phake::when($this->requestParameters)->getSearchColumns()->thenReturn(array());
        Phake::when($this->queryBuilder)->getQuery()->thenReturn($this->query);
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        $this->setProtectedValue($this->service, 'queryBuilder', $this->queryBuilder);
        $result = $this->service->buildQuery();

        $this->assertEquals($this->queryBuilder, $result);

        // verify addSearch
        Phake::verify($this->requestParameters)->getSearchColumns();

        // verify addOrder
        Phake::verify($this->requestParameters)->getSortingColumns();

        // verify addOffset
        Phake::verify($this->requestParameters)->getOffset();

        // verify addLimits
        Phake::verify($this->requestParameters)->getDisplayLength();
    }

    /**
     * testProcessQueryBuilder
     *
     * test that the process works with a query builder
     */
    public function testProcessQueryBuilder()
    {
        $dataFormatter = null;
        $this->setProtectedValue($this->service, 'queryBuilder', $this->queryBuilder);
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
        $this->setProtectedValue($this->service, 'queryBuilder', $this->queryBuilder);
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
     * testGenericSearchNoColumns
     */
    public function testGenericSearchNoColumns()
    {
        Phake::when($this->requestParameters)->getSearchString()->thenReturn('test');
        Phake::when($this->requestParameters)->getColumns()->thenReturn(array());
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        $this->service->setLogger($this->logger);

        $result = $this->service->addGenericSearch($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $result);
        Phake::verify($this->logger)->debug('sSearch: test');
    }

    /**
     * testGenericSearchColumn
     *
     * tests a single search colmn
     */
    public function testGenericSearchColumn()
    {
        $columns = array(
            'a.id' => 'ID',
        );
        Phake::when($this->requestParameters)->getSearchString()->thenReturn('test');
        Phake::when($this->requestParameters)->getColumns()->thenReturn($columns);
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        $this->service->setLogger($this->logger);

        $result = $this->service->addGenericSearch($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $result);
        Phake::verify($this->logger)->debug('sSearch: test');
        Phake::verify($this->queryBuilder)->setParameter('a_id_search', '%test%');
        Phake::verify($this->queryBuilder)->andWhere('a.id LIKE :a_id_search');
    }

    /**
     * testGenericSearchColumns
     */
    public function testGenericSearchColumns()
    {
        $columns = array(
            'a.id'   => 'ID',
            'a.name' => 'Name',
        );
        Phake::when($this->requestParameters)->getSearchString()->thenReturn('test');
        Phake::when($this->requestParameters)->getColumns()->thenReturn($columns);
        $this->setProtectedValue($this->service, 'requestParameters', $this->requestParameters);
        $this->service->setLogger($this->logger);

        $result = $this->service->addGenericSearch($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $result);
        Phake::verify($this->logger)->debug('sSearch: test');
        Phake::verify($this->queryBuilder)->setParameter('a_id_search', '%test%');
        Phake::verify($this->queryBuilder)->setParameter('a_name_search', '%test%');
        Phake::verify($this->queryBuilder)->andWhere('a.id LIKE :a_id_search or a.name LIKE :a_name_search');
    }
}