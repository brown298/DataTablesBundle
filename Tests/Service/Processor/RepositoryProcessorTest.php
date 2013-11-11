<?php
namespace Brown298\DataTablesBundle\Tests\Service\Processor;

use Brown298\DataTablesBundle\Service\Processor\RepositoryProcessor;
use \Phake;
use \Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class RepositoryProcessorTest
 *
 * @package Brown298\DataTablesBundle\Tests\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
class RepositoryProcessorTest extends AbstractBaseTest
{
    /**
     * @var Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $requestParameters;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var \Doctrine\ORM\AbstractQuery
     */
    protected $query;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $repository;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Brown298\DataTablesBundle\Service\Processor\RepositoryProcessor
     */
    protected $service;

    /**
     * @var Brown298\DataTablesBundle\Model\ResponseParameterBag
     */
    protected $responseParameters;

    /**
     * setUp
     */
    public function setUp()
    {
        parent::setUp();

        $this->responseParameters = Phake::mock('Brown298\DataTablesBundle\Model\ResponseParameterBag');
        $this->requestParameters  = Phake::mock('Brown298\DataTablesBundle\Model\RequestParameterBag');
        $this->queryBuilder       = Phake::mock('Doctrine\ORM\QueryBuilder');
        $this->query              = Phake::mock('Doctrine\ORM\AbstractQuery');
        $this->logger             = Phake::mock('Psr\Log\LoggerInterface');
        $this->repository         = Phake::mock('\Doctrine\ORM\EntityRepository');

        Phake::when($this->repository)->createQueryBuilder(Phake::anyParameters())->thenReturn($this->queryBuilder);
        Phake::when($this->queryBuilder)->getQuery(Phake::anyParameters())->thenReturn($this->query);
        Phake::when($this->queryBuilder)->andWhere(Phake::anyParameters())->thenReturn($this->queryBuilder);
        Phake::when($this->queryBuilder)->select(Phake::anyParameters())->thenReturn($this->queryBuilder);

        $this->service            = new RepositoryProcessor($this->repository, $this->requestParameters, $this->logger);

    }

    /**
     * testCreate
     */
    public function testCreate()
    {
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Service\Processor\RepositoryProcessor', $this->service);
    }

    /**
     * testGetSetOrderBy
     *
     */
    public function testGetSetOrderBy()
    {
        $this->service->setOrderBy(2);
        $this->assertEquals(2, $this->service->getOrderBy());
    }

    /**
     * testGetSetOffset
     *
     */
    public function testGetSetOffset()
    {
        $this->service->setOffset(2);
        $this->assertEquals(2, $this->service->getOffset());
    }

    /**
     * testGetSetLimit
     *
     */
    public function testGetSetLimit()
    {
        $this->service->setLimit(2);
        $this->assertEquals(2, $this->service->getLimit());
    }

    /**
     * testGetSetCriteria
     *
     */
    public function testGetSetCriteria()
    {
        $this->service->setCriteria(2);
        $this->assertEquals(2, $this->service->getCriteria());
    }

    /**
     * testAddCriteriaEmpty
     *
     */
    public function testAddCriteriaEmpty()
    {
        $this->callProtected($this->service, 'addCriteria', array());
        Phake::verify($this->queryBuilder, Phake::never())->andWhere(Phake::anyParameters());
        Phake::verify($this->queryBuilder, Phake::never())->setParameter(Phake::anyParameters());
    }

    /**
     * testAddCriteria
     *
     */
    public function testAddCriteria()
    {
        $this->callProtected($this->service, 'addCriteria', array(array('a.test' => '123')));
        Phake::verify($this->queryBuilder)->andWhere('a.test = :a_test');
        Phake::verify($this->queryBuilder)->setParameter('a_test', '123');
    }

    /**
     * testAddOrderingEmpty
     *
     */
    public function testAddOrderingEmpty()
    {
        $this->callProtected($this->service, 'addOrdering', array());
        Phake::verify($this->queryBuilder, Phake::never())->addOrderBy(Phake::anyParameters());
    }

    /**
     * testAddOrdering
     *
     */
    public function testAddOrdering()
    {
        $this->callProtected($this->service, 'addOrdering', array(array('a.test' => 'desc')));
        Phake::verify($this->queryBuilder)->addOrderBy('a.test', 'desc');
    }

    /**
     * testBuildFindBy
     *
     */
    public function testBuildFindBy()
    {
        $criteria = array('a.test'=> '123');
        $orderBy  = array('a.name' => 'asc');
        $limit    = 10;
        $offset   = 5;

        $this->service->buildFindBy($criteria, $orderBy, $limit, $offset);

        $this->assertEquals($offset, $this->service->getOffset());
        $this->assertEquals($limit, $this->service->getLimit());
        $this->assertEquals($criteria, $this->service->getCriteria());
        $this->assertEquals($orderBy, $this->service->getOrderBy());
    }

    /**
     * testBuildFindAll
     *
     */
    public function testBuildFindAll()
    {
        $criteria = array();
        $orderBy  = null;
        $limit    = null;
        $offset   = null;

        $this->service->buildFindALl();

        $this->assertEquals($offset, $this->service->getOffset());
        $this->assertEquals($limit, $this->service->getLimit());
        $this->assertEquals($criteria, $this->service->getCriteria());
        $this->assertEquals($orderBy, $this->service->getOrderBy());
    }

    /**
     * testCallThrowsErrorWithoutMethod
     *
     * @expectedException \BadMethodCallException
     */
    public function testCallThrowsErrorWithoutMethod()
    {
        $this->service->aa();
    }

    /**
     * testCallDoesNotReturnQueryBuilderThrowsError
     *
     * @expectedException \BadMethodCallException
     */
    public function testCallDoesNotReturnQueryBuilderThrowsError()
    {
        $this->service->getClassName();
    }

    /**
     * testGenericCreatesQueryBuilder
     *
     */
    public function testGenericCreatesQueryBuilder()
    {
        Phake::when($this->repository)->getClassName()->thenReturn($this->queryBuilder);

        $this->assertEquals($this->queryBuilder, $this->service->getClassName());
    }

    /**
     * testProcessEmpty
     *
     */
    public function testProcessEmpty()
    {
        Phake::when($this->query)->getArrayResult()->thenReturn(array(array(2)));
        $result = $this->service->process($this->responseParameters);
        Phake::verify($this->requestParameters, Phake::never())->setOffset(Phake::anyParameters());
        Phake::verify($this->requestParameters, Phake::never())->setDisplayLength(Phake::anyParameters());
    }

    /**
     * testProcessOffset
     *
     */
    public function testProcessOffset()
    {
        Phake::when($this->query)->getArrayResult()->thenReturn(array(array(2)));
        $this->service->setOffset(2);
        $result = $this->service->process($this->responseParameters);
        Phake::verify($this->requestParameters)->setOffset(Phake::anyParameters());
        Phake::verify($this->requestParameters, Phake::never())->setDisplayLength(Phake::anyParameters());
    }

    public function testProcessDisplayLength()
    {
        $this->service->setLimit(2);
        Phake::when($this->query)->getArrayResult()->thenReturn(array(array(2)));
        $result = $this->service->process($this->responseParameters);
        Phake::verify($this->requestParameters, Phake::never())->setOffset(Phake::anyParameters());
        Phake::verify($this->requestParameters)->setDisplayLength(Phake::anyParameters());
    }
}