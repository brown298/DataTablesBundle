<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Brown298\DataTablesBundle\Service\Processor\ProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class AbstractServerProcessor
 *
 *
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractServerProcessor
{

    /**
     * @var mixed
     *
     * \Brown298\DataTablesBundle\Service\Processor\QueryBuilderProcessor
     * \Brown298\DataTablesBundle\Service\Processor\ArrayProcessor
     */
    protected $processor;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $requestParameters;

    /**
     * @var \Brown298\DataTablesBundle\Model\ResponseParameterBag
     */
    protected $responseParameters;

    /**
     * @var null|PSR\Log|Logger
     */
    protected $logger = null;

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->requestParameters = new RequestParameterBag();
        $this->requestParameters->fromRequest($request);
    }

    /**
     * getRequest
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * addColumn
     *
     * @param $name
     * @param $title
     */
    public function addColumn($name, $title)
    {
        $this->requestParameters->addColumn($name, $title);
    }

    /**
     * setColumns
     *
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->requestParameters->setColumns($columns);
    }

    /**
     * getColumns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->requestParameters->getColumns();
    }

    /**
     * @return \Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    public function getRequestParameters()
    {
        return $this->requestParameters;
    }

    /**
     * @return \Brown298\DataTablesBundle\Model\ResponseParameterBag
     */
    public function getResponseParameters()
    {
        return $this->responseParameters;
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

    /**
     * setProcessor
     *
     * @param ProcessorInterface $processor
     */
    public function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * getProcessor
     *
     * @return mixed
     */
    public function getProcessor()
    {
        return $this->processor;
    }


}