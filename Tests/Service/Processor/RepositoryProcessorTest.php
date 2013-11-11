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
        Phake::when($this->queryBuilder)->andWhere(Phake::anyParameters())->thenReturn($this->queryBuilder);

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
}