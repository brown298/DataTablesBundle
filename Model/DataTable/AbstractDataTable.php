<?php
namespace Brown298\DataTablesBundle\Model\DataTable;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Brown298\DataTablesBundle\Model\DataTable\DataTableInterface;

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
     * @var null
     */
    protected $metaData = null;

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
     * @param $row
     * @return array
     */
    public function getColumnRendering($row)
    {
        $result = array();
        foreach($this->metaData['columns'] as $column) {
            if (isset($column->format)) {
                $args = array();
                foreach($column->format->dataFields as $name => $source) {
                    $args[$name] = $this->getDataValue($row, $source);
                }
                if ($column->format->template != null) {
                    $renderer = $this->container->get('templating');
                    $result[] = $renderer->render($column->format->template, $args);
                } else { // no render so send back the raw data
                    $result[] = $args;
                }
            } else {
                $result[] = $this->getDataValue($row, $column->source);
            }
        }
        return $result;
    }

    /**
     * @param $row
     * @param $source
     * @return null
     */
    protected function getDataValue($row, $source)
    {
        $result = null;
        if (is_object($row)) {
            $result = $this->getObjectValue($row, $source);
        } else if(is_array($row)) {
            $tokens = explode('.', $source);
            $result = $row[array_pop($tokens)];
        }

        return $result;
    }

    /**
     * getObject Value
     *
     * allows for relations based on things like faq.createdBy.id
     *
     * @param $row
     * @param $source
     * @return string
     */
    protected function getObjectValue($row, $source)
    {
        $result = 'Unknown';

        $tokens = explode('.', $source);
        $name = 'get' . Inflector::classify(array_pop($tokens));
        if (count($tokens) <= 1 && method_exists($row, $name)) {
            $result = $row->$name();
        } else {
            if (count($tokens) > 1) {
                $sub = $this->getObjectValue($row, implode('.', $tokens));
                if (is_object($sub) && method_exists($sub,$name)) {
                    $result = $sub->$name();
                } elseif (is_array($sub)) {
                    $result = array();
                    foreach($sub as $d) {
                        $result[] = $this->getObjectValue($d, implode('.', $tokens));
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return null
     */
    public function getDataFormatter()
    {
        if ($this->dataFormatter == null && !empty($this->metaData)) {
            $table = $this;
            $this->dataFormatter = function($data) use ($table) {
                $count   = 0;
                $results = array();

                foreach ($data as $row) {
                    $results[$count] = $table->getColumnRendering($row);
                    $count +=1;
                }

                return $results;
            };
        }

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

    /**
     * @param array $metaData
     * @return mixed|void
     */
    public function setMetaData(array $metaData = null)
    {
        $this->metaData = $metaData;
    }

    /**
     * @return null
     */
    public function getMetaData()
    {
        return $this->metaData;
    }


}