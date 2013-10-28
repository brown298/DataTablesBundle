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
        $this->request = Phake::mock('\Symfony\Component\HttpFoundation\Request');
        $this->service->setRequest($this->request);

        $this->assertEquals($this->request, $this->service->getRequest());
        $this->assertInstanceOf('\Brown298\DataTablesBundle\Model\RequestParameterBag', $this->service->getRequestParameters());
    }
}