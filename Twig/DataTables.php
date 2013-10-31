<?php
namespace Brown298\DataTablesBundle\Twig;

use Symfony\Bridge\Twig\TwigEngine;

/**
 * Class DataTables
 *
 * @package Brown298\DataTablesBundle\Twig
 * @author John Brown <brown.john@gmail.com>
 */

class DataTables extends \Twig_Extension
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var Twig_Environment
     */
    private $environment;

    /**
     * initRuntime
     *
     * @param \Twig_Environment $environment
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * getFunctions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'addDataTable' => new \Twig_Function_Method($this, 'addDataTable', array(
                    'is_safe' => array('html')
                )),
        );
    }

    /**
     * addDataTable
     *
     * @param array $columns
     * @param array $params
     *
     * @return string
     */
    public function addDataTable(array $columns, $params = array())
    {
        $this->columns = $columns;
        if (!is_array($params)) {
            $params = array();
        }

        $this->params = array_merge(
            array(
                'table_template'  => 'Brown298DataTablesBundle::table.html.twig',
                'script_template' => 'Brown298DataTablesBundle::script.html.twig',
                'id'              => 'dataTable',
                'bProcessing'     => 1,
                'bServerSide'     => 1,
                'bLengthChange'   => 0,
                'bFilter'         => 0,
                'bSort'           => 1,
                'sPaginationType' => 'full_numbers',
                'bInfo'           => 0,
                'bPaginate'       => 1,
                'path'            => '',
                'iDisplayLength'  => -1,
                'table_class'     => 'display dataTable table table-striped',
                'aaData'          => null,
            ),
            $params
        );

        return $this->renderJs() . $this->renderTable();
    }

    /**
     * renderJs
     *
     * @return string
     */
    public function renderJs()
    {
        return $this->environment->render($this->params['script_template'], array(
                'columns' => $this->columns,
                'params'  => $this->params,
            ));
    }

    /**
     * renderTable
     *
     * @return string
     */
    public function renderTable()
    {
        return $this->environment->render($this->params['table_template'], array(
                'columns' => $this->columns,
                'params'  => $this->params,
            ));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'data_tables';
    }
}