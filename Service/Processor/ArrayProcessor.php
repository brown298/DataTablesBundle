<?php
namespace Brown298\DataTablesBundle\Service\Processor;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Psr\Log\LoggerInterface;

/**
 * Class ArrayProcessor
 *
 * @package Brown298\DataTablesBundle\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
class ArrayProcessor
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var null|PSR\Log|Logger
     */
    protected $logger = null;

    /**
     * __construct
     *
     * @param RequestParameterBag $requestParameters
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(RequestParameterBag $requestParameters, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->requestParameters = $requestParameters;
    }

    public function process(ResponseParameterBag $responseParameters = null, $dataFormatter = null)
    {
        if ($responseParameters === null) {
            $responseParameters = new ResponseParameterBag();
            $responseParameters->setRequest($this->requestParameters);
        }

        $offset = $this->requestParameters->getOffset();
        $length = $this->requestParameters->getDisplayLength();

        if ($length > 0) {
            $data = array_slice($this->data, $offset, $length);
        } else {
            $data = array_slice($this->data, $offset);
        }

        $responseParameters->setData($data);
        $responseParameters->setTotal(count($this->data));
        $responseParameters->setDisplayTotal(count($data));

        return $responseParameters;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * setLogger
     *
     * @param $logger
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * debug
     *
     * @param $message
     */
    public function debug($message)
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->debug($message);
        }
    }
}