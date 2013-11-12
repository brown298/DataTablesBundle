<?php
namespace Brown298\DataTablesBundle\Model;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use \Brown298\DataTablesBundle\Model\DataTable\AbstractQueryBuilderDataTable as BaseAbstractDataTable;
use \Brown298\DataTablesBundle\Model\DataTable\QueryBuilderDataTableInterface as BaseDataTableInterface;

/**
 * Class AbstractDataTable
 *
 * @deprecated as of 0.3.0 will be removed by 1.0.0 please use Brown298\DataTablesBundle\Model\DataTable\AbstractDataTable
 *
 * @package Brown298\DataTablesBundle\Model
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractDataTable extends BaseAbstractDataTable implements BaseDataTableInterface,  DataTableInterface
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em = null;

    /**
     * __construct
     *
     * @param EntityManager $em
     * @param array $columns
     */
    public function __construct(EntityManager $em = null, array $columns = null)
    {
        $this->em = $em;
        parent::__construct($columns);
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     * @return mixed|void
     */
    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }
}