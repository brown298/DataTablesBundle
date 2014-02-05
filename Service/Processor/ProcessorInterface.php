<?php
namespace Brown298\DataTablesBundle\Service\Processor;

use Brown298\DataTablesBundle\Model\ResponseParameterBag;

/**
 * Class ProcessorInterface
 * @package Brown298\DataTablesBundle\Service\Processor
 * @author  John Brown <brown.john@gmail.com>
 */
interface ProcessorInterface
{
    /**
     * tells the processor to run
     *
     * @param ResponseParameterBag $responseParameters
     * @param bool $getEntity
     *
     * @return mixed
     */
    public function process(ResponseParameterBag $responseParameters = null, $getEntity = false);
}