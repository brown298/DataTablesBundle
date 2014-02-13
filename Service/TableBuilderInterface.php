<?php
namespace Brown298\DataTablesBundle\Service;

/**
 * Interface TableBuilderInterface
 *
 * @package Brown298\DataTablesBundle\Service
 * @author  John Brown <brown.john@gmail.com>
 */
interface TableBuilderInterface
{
    /**
     * build
     *
     * @param array $args
     * @return mixed
     */
    public function build(array $args = array());
}