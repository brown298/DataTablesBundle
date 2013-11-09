<?php
namespace Brown298\DataTablesBundle\Service;

use Brown298\DataTablesBundle\Model\RequestParameterBag;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Brown298\DataTablesBundle\Service\AbstractServerProcessor;
use Brown298\DataTablesBundle\Service\Processor\ArrayProcessor;
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
class ServerProcessService extends AbstractServerProcessor
{

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
        switch(get_class($this->processor)) {
            case "Brown298\\DataTablesBundle\\Service\\Processor\\QueryBuilderProcessor":
                $this->responseParameters = $this->processor->process($this->responseParameters, $dataFormatter, $getEntity);
                break;
            case "Brown298\\DataTablesBundle\\Service\\Processor\\ArrayProcessor":
                $this->responseParameters = $this->processor->process($this->responseParameters, $dataFormatter);
                break;
        }
        
        return $this->responseParameters->all($dataFormatter);
    }

    /**
     * setQueryBuilder
     *
     * @param QueryBuilder $queryBuilder]
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->processor = new QueryBuilderProcessor($queryBuilder, $this->requestParameters, $this->logger);
    }

    /**
     * getQueryBuilder
     *
     * @return null
     */
    public function getQueryBuilder()
    {
        if ($this->processor == null || !($this->processor instanceof ArrayProcessor)) {
            return null;
        }

        return $this->processor->getQueryBuilder();
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->processor = new ArrayProcessor($this->requestParameters, $this->logger);
        $this->processor->setData($data);
    }

    /**
     * @return array
     */
    public function getData()
    {
        if ($this->processor == null || !($this->processor instanceof ArrayProcessor)) {
            return null;
        }

        return $this->processor->getData();
    }

}