<?php
namespace Brown298\DataTablesBundle\Model;

use Brown298\DataTablesBundle\Model\DataTable\QueryBuilderDataTableInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DataTableInterface
 *
 * @deprecated as of 0.3.0 will be removed by 1.0.0 please use Brown298\DataTablesBundle\Model\DataTable\DataTableInterface
 *
 * @package Brown298\DataTablesBundle\Model
 * @author  John Brown <brown.john@gmail.com>
 */
interface DataTableInterface extends QueryBuilderDataTableInterface
{
    /**
     * getEm
     *
     * @return mixed
     */
    public function getEm();

    /**
     * @param EntityManager $em
     * @return mixed
     */
    public function setEm(EntityManager $em);

}