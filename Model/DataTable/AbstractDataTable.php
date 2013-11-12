<?php
namespace Brown298\DataTablesBundle\Model\DataTable;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AbstractDataTable
 *
 * @package Brown298\DataTablesBundle\Model\DataTable
 * @author  John Brown <brown.john@gmail.com>
 */
abstract class AbstractDataTable implements DataTableInterface, ContainerAwareInterface
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
     * @var null
     */
    protected $container = null;

    /**
     * __construct
     *
     * @param array $columns
     */
    public function __construct(array $columns = null)
    {
        if ($columns !== null) {
            $this->columns = $columns;
        }
    }

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


    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * getJsonResponse
     *
     * @param Request $request
     *
     * @param callable|null $dataFormatter
     *
     * @return JsonResponse
     */
    public function getJsonResponse(Request $request, \Closure $dataFormatter = null)
    {
        return new JsonResponse($this->getData($request, $dataFormatter));
    }

    /**
     * {@inheritDoc}
     */
    public function processRequest(Request $request, \Closure $dataFormatter = null)
    {
        if (!$this->isAjaxRequest($request)) {
            return false;
        }

        if ($dataFormatter !== null) {
            $dataFormatter = $this->dataFormatter;
        } elseif ($this->getDataFormatter() !== null) {
            $dataFormatter = $this->getDataFormatter();
        }

        // ensure at least a minimal formatter is used
        if ($dataFormatter === null) {
            $dataFormatter = function($data) { return $data; };
        }

        return $this->getJsonResponse($request, $dataFormatter);
    }

}