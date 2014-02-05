<?php
namespace Brown298\DataTablesBundle\Service\Processor;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractProcessor
 *
 * @package Brown298\DataTablesBundle\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractProcessor
{
    /**
     * @var \Brown298\DataTablesBundle\Model\ResponseParameterBag
     */
    protected $responseParameters;

    /**
     * @var Brown298\DataTablesBundle\Model\RequestParameterBag
     */
    protected $requestParameters;

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
}