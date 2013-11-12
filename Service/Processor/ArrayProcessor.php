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
class ArrayProcessor extends AbstractProcessor implements ProcessorInterface
{
    /**
     * @var array
     */
    protected $data = array();

    /**
     * process
     *
     * @param ResponseParameterBag $responseParameters
     * @param bool $getEntity
     *
     * @return ResponseParameterBag
     */
    public function process(ResponseParameterBag $responseParameters = null, $getEntity=false)
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
        $this->setColumns(array_keys($data));
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}