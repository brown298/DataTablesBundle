<?php
namespace Brown298\DataTablesBundle\Model;

/**
 * Class ResponseParameterBag
 *
 * @package Brown298\DataTablesBundle\Model
 * @author  John Brown <brown.john@gmail.com>
 */
class ResponseParameterBag extends AbstractParamterBag
{
    /**
     * @var array
     */
    public $parameterNames = array(
        'sEcho',                // string echo
        'iTotalRecords',        // total records
        'iTotalDisplayRecords', // Number of records Displayed
        'aaData',               // array of response data
    );
}