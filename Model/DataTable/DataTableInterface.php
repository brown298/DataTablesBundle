<?php
namespace Brown298\DataTablesBundle\Model\DataTable;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class DataTableInterface
 *
 * @package Brown298\DataTablesBundle\Model\DataTable
 * @author  John Brown <brown.john@gmail.com>
 */
interface DataTableInterface
{
    /**
     * getColumns
     *
     * @return array
     */
    public function getColumns();

    /**
     * setColumns
     *
     * @param array|null $columns
     * @return null
     */
    public function setColumns(array $columns = null);

    /**
     * isAjaxRequest
     *
     * @param Request $request
     * @return mixed
     */
    public function isAjaxRequest(Request $request);

    /**
     * @param callable $dataFormatter
     * @return mixed
     */
    public function setDataFormatter(\Closure $dataFormatter = null);

    /**
     * @return mixed
     */
    public function getDataFormatter();

    /**
     * @param array $metaData
     * @return mixed
     */
    public function setMetaData(array $metaData = null);

    /**
     * @return mixed
     */
    public function getMetaData();

    /**
     * getData
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function getData(Request $request);

    /**
     * getJsonResponse
     *
     * @param Request $request
     * @param callable|null $dataFormatter
     *
     * @return mixed
     */
    public function getJsonResponse(Request $request, \Closure $dataFormatter = null);

    /**
     * processRequest
     *
     * processes a request and returns the appropriate response
     *
     * @param Request $request
     * @param callable $dataFormatter
     * @return mixed false if not an ajax request
     */
    public function processRequest(Request $request, \Closure $dataFormatter = null);

}