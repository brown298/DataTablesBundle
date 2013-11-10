<?php
namespace Brown298\DataTablesBundle\Model\DataTable;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractDataTable
 *
 * @package Brown298\DataTablesBundle\Model\DataTable
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractDataTable implements DataTableInterface
{
    /**
     * @var array definition of the column as DQLName => display
     */
    protected $columns = array();

    /**
     * @var null
     */
    protected $dataFormatter = null;


    /**
     * getColumns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * setColumns
     *
     * @param array $columns
     * @return null|void
     */
    public function setColumns(array $columns = null)
    {
        $this->columns = $columns;
    }

    /**
     * isAjaxRequest
     *
     * @param Request $request
     * @return bool
     */
    public function isAjaxRequest(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        return false;
    }

    /**
     * @param callable|null $dataFormatter
     * @return mixed|void
     */
    public function setDataFormatter(\Closure $dataFormatter = null)
    {
        $this->dataFormatter = $dataFormatter;
    }

    /**
     * @return null
     */
    public function getDataFormatter()
    {
        return $this->dataFormatter;
    }

}