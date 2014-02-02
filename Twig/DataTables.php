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
            'addDataTable'     => new \Twig_SimpleFunction('addDataTable', array($this, 'addDataTable'), array(
                    'is_safe' => array('html')
                )),
        );
    }

    /**
     * getFilters
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'convertFunctions' => new \Twig_SimpleFilter('convertFunctions', array($this, 'convertFunctions')),
        );
    }

    /**
     * convertFunctions
     *
     * @param $rawJson
     *
     * @return string
     */
    public function convertFunctions($rawJson)
    {
        $data        = json_decode($rawJson);
        $valueArray  = array();
        $replaceKeys = array();

        foreach($data as $key => &$value) {
            if (preg_match('/^function.*/', $value)
                || $this->isJsonObject($value)) {
                $valueArray[]  = $value;
                $value         = "%{$key}%";
                $replaceKeys[] = "\"{$value}\"";
            }
        }

        $resultJson = json_encode($data);
        $resultJson = str_replace($replaceKeys, $valueArray, $resultJson);

        return $resultJson;
    }

    /**
     * isJsonObject
     *
     * @param $string
     *
     * @return bool
     */
    protected function isJsonObject($string)
    {
        $evalObj = json_decode($string);
        $result  =  is_object($evalObj) || is_array($evalObj);
        return $result;
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
                'bHidePaginate'   => 1,
                'path'            => '#',
                'iDisplayLength'  => -1,
                'table_class'     => 'display dataTable table table-striped',
                'aaData'          => null,
                'twigVars'        => array(),
                'customParams'    => array(),
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
        $args = array_merge($this->params['twigVars'], array(
                'columns'   => $this->columns,
                'rawParams' => $this->params,
                'params'    => $this->buildJsParams(),
            ));

        return $this->environment->render($this->params['script_template'], $args);
    }

    /**
     * buildJsParams
     *
     */
    protected function buildJsParams()
    {
        $this->params['customParams'] = (!isset($this->params['customParams'])) ? array() : $this->params['customParams'];
        $keys    = array_merge(array(
            'aaData',
            'aaSorting',
            'aaSortingFixed',
            'aoColumnDefs',
            'aoColumns',
            'bLengthChange',
            'bFilter',
            'bPaginate',
            'bProcessing',
            'bServerSide',
            'bSort',
            'bInfo',
            'fnDrawCallback',
            'fnRowCallback',
            'fnServerData',
            'iDisplayLength',
            'sPaginationType',
        ), array_keys($this->params['customParams']));
        $results = array();

        // custom conversions
        if (isset($this->params['path'])) {
            $results['sAjaxSource'] = $this->params['path'];
        }
        if (isset($this->params['customSearchForm'])) {
            $results['fnServerData'] = 'function ( sSource, aoData, fnCallback ) { var searchValues = jQuery("'
                . $this->params['customSearchForm'] . '").serializeArray(); for ( var i=0; i < searchValues.length; i++)'
                . ' { aoData.push(searchValues[i]); } jQuery.getJSON( sSource, aoData, function (json) {fnCallback(json)} ); }';
        }
        if (isset($this->params['aaData']) && isset($this->params['aoColumnDefs'])) {
            $this->params['aoColumns'] = $this->params['aoColumnDefs'];
            unset($this->params['aoColumnDefs']);
        }

        if (isset($this->params['bHidePaginate']) && $this->params['bHidePaginate']) {
            $this->params['fnDrawCallback'] = 'function() { if (jQuery(".dataTables_paginate a").length <=5) {jQuery(".dataTables_paginate").hide();} else {jQuery(".dataTables_paginate").show();} }';
        }

        // build the final params
        foreach ($keys as $key) {
            if (isset($this->params[$key]) || isset($this->params['customParams'][$key])) {
                if (preg_match('/^aa.+/', $key) || preg_match('/^ao.+/', $key)) {
                    $results[$key] = json_encode($this->params[$key]);
                } elseif(isset($this->params['customParams'][$key])) {
                    $results[$key] = $this->params['customParams'][$key];
                } else {
                    $results[$key] = $this->params[$key];
                }
            }
        }

        return $results;
    }

    /**
     * renderTable
     *
     * @return string
     */
    public function renderTable()
    {
        $args = array_merge($this->params['twigVars'], array(
                'columns' => $this->columns,
                'params'  => $this->params,
        ));
        return $this->environment->render($this->params['table_template'], $args);
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