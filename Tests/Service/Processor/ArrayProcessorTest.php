<?php
namespace Brown298\DataTablesBundle\Tests\Service\Processor;

use Brown298\DataTablesBundle\Service\Processor\ArrayProcessor;
use \Phake;
use \Brown298\DataTablesBundle\Test\AbstractBaseTest;

/**
 * Class ArrayProcessorTest
 * @package Brown298\DataTablesBundle\Tests\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
class ArrayProcessorTest extends AbstractBaseTest
{

    /**
     * @var Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $requestParameters;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Brown298\DataTablesBundle\Service\Processor\ArrayProcessor
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
        $this->logger             = Phake::mock('Psr\Log\LoggerInterface');
        $this->service            = new ArrayProcessor($this->requestParameters, $this->logger);
    }

    /**
     * testGetSetData
     */
    public function testGetSetData()
    {
        $data = array('test'=>'123');
        $this->service->setData($data);
        $this->assertEquals($data, $this->service->getData());
        Phake::verify($this->requestParameters)->setColumns(array('test'));
    }

    /**
     * testProcessCreatesResponseParameterBag
     */
    public function testProcessCreatesResponseParameterBag()
    {
        $result = $this->service->process();
        $this->assertInstanceOf('Brown298\DataTablesBundle\Model\ResponseParameterBag', $result);
    }

    /**
     * testProcessSetsData
     */
    public function testProcessSetsData()
    {
        $this->service->process($this->responseParameters);

        Phake::verify($this->responseParameters)->setData(Phake::anyParameters());
    }
}