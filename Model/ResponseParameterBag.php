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
        'echo' => array(
            'name'    => 'Echo',
            'const'   => 'sEcho',
            'default' => null,
        ), // string echo
        'total' => array(
            'name'    => 'Total Records',
            'const'   => 'iTotalRecords',
            'default' => 0,
        ), // total records
        'displayTotal' => array(
            'name'    => 'Display Total',
            'const'   => 'iTotalDisplayRecords',
            'default' => 0,
        ), // Number of records Displayed
        'data' => array(
            'name'    => 'Data',
            'const'   => 'aaData',
            'default' => array(),
        ), // array of response data
    );

    /**
     * all
     *
     * @param $dataFormatter
     *
     * @return array
     */
    public function all($dataFormatter = null)
    {
        $results = array();

        foreach ($this->parameterNames as $key => $data) {
            $results[$data['const']] = $this->getVarByName($key);
        }

        $dataFormatter = is_callable($dataFormatter) ? $dataFormatter : function($e) {return $e;};
        $results['aaData'] = $dataFormatter($results['aaData']);

        return $results;
    }


    /**
     * setRequest
     * @param RequestParameterBag $request
     */
    public function setRequest(RequestParameterBag $request)
    {
        $this->setVarByName('echo', $request->getEcho());
    }

    /**
     * setEcho
     *
     * @param $echo
     */
    public function setEcho($echo)
    {
        $this->setVarByName('echo', $echo);
    }

    /**
     * getEcho
     *
     * @return mixed
     */
    public function getEcho()
    {
        return $this->getVarByName('echo');
    }

    /**
     * setTotal
     *
     * @param integer $total
     */
    public function setTotal($total)
    {
        $this->setVarByName('total', $total);
    }

    /**
     * getTotal
     *
     * @return integer
     */
    public function getTotal()
    {
        return intval($this->getVarByName('total'));
    }


    /**
     * setDisplayTotal
     *
     * @param integer $total
     */
    public function setDisplayTotal($total)
    {
        $this->setVarByName('displayTotal', $total);
    }

    /**
     * getDisplayTotal
     *
     * @return integer
     */
    public function getDisplayTotal()
    {
        return $this->getVarByName('displayTotal');
    }


    /**
     * setData
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->setVarByName('data', $data);
    }

    /**
     * getData
     *
     * @return integer
     */
    public function getData()
    {
        return $this->getVarByName('data');
    }

}