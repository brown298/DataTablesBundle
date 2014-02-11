<?php
namespace Brown298\DataTablesBundle\Tests\Model;

use Brown298\DataTablesBundle\Test\DataTable\RepositoryDataTable;
use Phake;
use Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class RepositoryDataTableTest
 *
 * @package Brown298\DataTablesBundle\Tests\Model
 * @author  John Brown <brown.john@gmail.com>
 */
class RepositoryDataTableTest extends AbstractBaseTest
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
     * @var \Brown298\DataTablesBundle\Test\DataTable\RepositoryDataTable
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
     * @Mock
     * @var Doctrine\ORM\EntityRepository
     */
    protected $repository;


    /**
     * setUp
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->dataTable = new RepositoryDataTable();
        $this->dataTable->setContainer($this->container);
        Phake::when($this->container)->get('data_tables.service')->thenReturn($this->service);
    }

    /**
     * testCreate
     *
     */
    public function testCreate()
    {
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Test\DataTable\RepositoryDataTable', $this->dataTable);
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Model\DataTable\RepositoryDataTableInterface', $this->dataTable);
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Model\DataTable\QueryBuilderDataTableInterface', $this->dataTable);
    }

    /**
     * testGetSetRepository
     *
     */
    public function testGetSetRepository()
    {
        $this->dataTable->setRepository($this->repository);
        $this->assertEquals($this->repository, $this->dataTable->getRepository());
    }

    /**
     * testSetContainerGetsServerProcess
     *
     */
    public function testSetContainerGetsServerProcess()
    {
        $this->dataTable->setContainer($this->container);

        Phake::verify($this->container, Phake::atLeast(2))->get('data_tables.service');
        $this->assertEquals($this->service, $this->getProtectedValue($this->dataTable, 'serverProcessorService'));
    }

    /**
     * testFindByThrowsExceptionWithoutContainer
     *
     * @expectedException \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function testFindByThrowsExceptionWithoutContainer()
    {
        $this->dataTable->setContainer(null);
        $this->dataTable->findBy(array());
    }

    /**
     * testFindByThrowsExceptionWithoutRepository
     *
     * @expectedException \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function testFindByThrowsExceptionWithoutRepository()
    {
        $this->dataTable->setContainer($this->container);
        $this->dataTable->findBy(array());
    }

    /**
     * testFindByRunsOnRepository
     *
     */
    public function testFindByRunsOnRepository()
    {
        $this->dataTable->setContainer($this->container);
        $this->dataTable->setRepository($this->repository);
        $this->dataTable->findBy(array());

        Phake::verify($this->service)->setRepository($this->repository);
        Phake::verify($this->service)->findBy(array(), null, null, null);
    }

    /**
     * testFindAllThrowsExceptionWithoutContainer
     *
     * @expectedException \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function testFindAllThrowsExceptionWithoutContainer()
    {
        $this->dataTable->setContainer(null);
        $this->dataTable->findAll(array());
    }

    /**
     * testFindAllThrowsExceptionWithoutRepository
     *
     * @expectedException \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function testFindAllThrowsExceptionWithoutRepository()
    {
        $this->dataTable->setContainer($this->container);
        $this->dataTable->findAll(array());
    }

    /**
     * testFindAllRunsService
     *
     */
    public function testFindAllRunsService()
    {
        $this->dataTable->setContainer($this->container);
        $this->dataTable->setRepository($this->repository);
        $this->dataTable->findAll();

        Phake::verify($this->service)->setRepository($this->repository);
        Phake::verify($this->service)->findAll();
    }

    /**
     * testGenericThrowsExceptionWithoutContainer
     *
     * @expectedException \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function testGenericThrowsExceptionWithoutContainer()
    {
        $this->dataTable->setContainer(null);
        $this->dataTable->aaaah();
    }

    /**
     * testGenericThrowsExceptionWithoutRepository
     *
     * @expectedException \Brown298\DataTablesBundle\Exceptions\ProcessorException
     */
    public function testGenericThrowsExceptionWithoutRepository()
    {
        $this->dataTable->setContainer($this->container);
        $this->dataTable->aaaah();
    }

    /**
     * testGenericByRunsOnRepository
     *
     */
    public function testGenericByRunsOnRepository()
    {
        $this->dataTable->setContainer($this->container);
        $this->dataTable->setRepository($this->repository);
        $this->dataTable->aaaah();

        Phake::verify($this->service)->setRepository($this->repository);
        Phake::verify($this->service)->aaaah(Phake::anyParameters());
    }

    /**
     * testGetDataNull
     */
    public function testGetDataNull()
    {
        Phake::when($this->container)->get(Phake::anyParameters())->thenReturn($this->service);

        $this->assertNull($this->dataTable->getData($this->request));
    }

    /**
     * testGetData
     */
    public function testGetData()
    {
        $expectedResult = 'test';
        Phake::when($this->service)->process(Phake::anyParameters())->thenReturn($expectedResult);
        Phake::when($this->container)->get(Phake::anyParameters())->thenReturn($this->service);
        $this->dataTable->setRepository($this->repository);
        $this->dataTable->setRepository($this->repository);

        $result = $this->dataTable->getData($this->request);

        $this->assertEquals($expectedResult, $result);
        Phake::verify($this->service)->setRequest($this->request);
        Phake::verify($this->service)->setRepository($this->repository);

    }

} 