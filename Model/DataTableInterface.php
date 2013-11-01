<?php
namespace Brown298\DataTablesBundle\Model;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DataTableInterface
 *
 * @package Brown298\DataTablesBundle\Model
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
     * getQueryBuilder
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null
     */
     public function getQueryBuilder(Request $request);

    /**
     * @param Request $request
     * @return mixed
     */
    public function getData(Request $request);

    /**
     * isAjaxRequest
     *
     * @param Request $request
     * @return mixed
     */
    public function isAjaxRequest(Request $request);

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

    /**
     * execute
     *
     * runs the server process and sends the resulting data to the formatter
     *
     * @param $service
     * @param $formatter
     *
     * @return mixed
     */
    public function execute($service, $formatter);
}