<?php
namespace Brown298\DataTablesBundle\Tests\Service\Processor;

use \Brown298\DataTablesBundle\Service\Processor\QueryBuilderProcessor;
use \Phake;
use \Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class QueryBuilderProcessorTest
 *
 * @package Brown298\DataTablesBundle\Tests\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
class QueryBuilderProcessorTest extends AbstractBaseTest
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
     * @var Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $requestParameters;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Brown298\DataTablesBundle\Service\Processor\QueryBuilderProcessor
     */
    protected $service;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->queryBuilder      = Phake::mock('Doctrine\ORM\QueryBuilder');
        $this->query             = Phake::mock('Doctrine\ORM\AbstractQuery');
        $this->requestParameters = Phake::mock('Brown298\DataTablesBundle\Model\RequestParameterBag');
        $this->logger            = Phake::mock('Psr\Log\LoggerInterface');

        $this->service           = new QueryBuilderProcessor($this->queryBuilder, $this->requestParameters);
    }

    /**
     * testCreate
     */
    public function testCreate()
    {
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Service\Processor\QueryBuilderProcessor', $this->service);
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
        Phake::verify($this->queryBuilder)->select(array('count(s)'));
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

    /**
     * testProcessCreatesResponseParameterBag
     */
    public function testProcessCreatesResponseParameterBag()
    {
        Phake::when($this->queryBuilder)->getQuery()->thenReturn($this->query);
        Phake::when($this->query)->getResult()->thenReturn(array());
        Phake::when($this->queryBuilder)->select(Phake::anyParameters())->thenReturn($this->queryBuilder);
        Phake::when($this->query)->getArrayResult()->thenReturn(array(array()));
        $result = $this->service->process();
        $this->assertInstanceOf('Brown298\DataTablesBundle\Model\ResponseParameterBag', $result);
    }
}