<?php
namespace Brown298\DataTablesBundle\Controller;

use Brown298\DataTablesBundle\Test\DataTable\QueryBuilderDataTable;
use Brown298\DataTablesBundle\Model\ResponseParameterBag;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractController
 * @package Brown298\DataTablesBundle\Controller
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractController extends Controller
{
    /**
     * @var array
     */
    protected $columns = array();

    /**
     * @var QueryBuilderDataTable
     */
    protected $dataTable;

    /**
     * getData
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    protected function getData(Request $request)
    {
        if ($this->dataTable == null) {
            $this->dataTable = new QueryBuilderDataTable();
            $this->dataTable->setContainer($this->container);
        }

        return $this->dataTable->getData($request);
    }

    /**
     * getQueryBuilder
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null
     */
    protected function getQueryBuilder(Request $request)
    {
        if ($this->dataTable == null) {
            $this->dataTable = new QueryBuilderDataTable();
            $this->dataTable->setContainer($this->container);
        }

        return $this->dataTable->getQueryBuilder($request);
    }

    /**
     * dataAction
     *
     * @param Request $request
     *
     * @param null    $dataFormatter
     *
     * @return JsonResponse
     */
    public function dataAction(Request $request, $dataFormatter = null)
    {
        $this->dataTable = new QueryBuilderDataTable();
        $this->dataTable->setContainer($this->container);
        $this->dataTable->setColumns($this->columns);
        $this->dataTable->setQueryBuilder($this->getQueryBuilder($request));
        return $this->dataTable->getJsonResponse($request, $dataFormatter);
    }
}