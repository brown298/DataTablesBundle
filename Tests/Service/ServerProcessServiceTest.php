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
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

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
        $this->service = new ServerProcessService();
        $this->request = Phake::mock('\Symfony\Component\HttpFoundation\Request');
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

}