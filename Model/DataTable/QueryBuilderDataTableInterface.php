<?php
namespace Brown298\DataTablesBundle\Model\DataTable;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface QueryBuilderDataTableInterface
 *
 * @package Brown298\DataTablesBundle\Model\DataTable
 * @author  John Brown <brown.john@gmail.com>
 */
interface QueryBuilderDataTableInterface extends DataTableInterface
{
    /**
     * execute
     *
     * @param $service
     * @param $formatter
     *
     * @return mixed
     */
    public function execute($service, $formatter);

    /**
     * setQueryBuilder
     *
     * @param QueryBuilder $qb
     *
     * @return mixed
     */
    public function setQueryBuilder(QueryBuilder $qb);

    /**
     * getQueryBuilder
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getQueryBuilder(Request $request = null);
} 