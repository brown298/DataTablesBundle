<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Brown298\DataTablesBundle\Service\Processor\QueryBuilderProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ServerProcessService
 *
 * handles the server side processing
 *
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <brown.john@gmail.com>
 */
class ServerProcessService
{
    /**
     * @var \Brown298\DataTablesBundle\Service\Processor\QueryBuilderPorcessor
     */
    protected $queryBuilderProcessor;

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
     * @var array
     */
    protected $data = null;

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
     * setQueryBuilder
     *
     * @param QueryBuilder $queryBuilder]
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilderProcessor = new QueryBuilderProcessor($queryBuilder, $this->requestParameters, $this->logger);
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
     * process
     *
     * gets the results
     *
     * @param null $dataFormatter
     * @param bool $getEntity
     *
     * @return array
     */
    public function process($dataFormatter = null, $getEntity = false)
    {
        $this->responseParameters = new ResponseParameterBag();
        $this->responseParameters->setRequest($this->requestParameters);

        // check if we are using a query builder or an array of data
        if (!is_array($this->data)) {
            $this->responseParameters = $this->queryBuilderProcessor->process($this->responseParameters, $dataFormatter, $getEntity);
        } else {
            $offset = $this->requestParameters->getOffset();
            $length = $this->requestParameters->getDisplayLength();

            if ($length > 0) {
                $data = array_slice($this->data, $offset, $length);
            } else {
                $data = array_slice($this->data, $offset);
            }

            $this->responseParameters->setData($data);
            $this->responseParameters->setTotal(count($this->data));
            $this->responseParameters->setDisplayTotal(count($data));
        }

        return $this->responseParameters->all($dataFormatter);
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

}