<?php
namespace Brown298\DataTablesBundle\Controller;

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
 * @author  John Brown <john.brown@partnerweekly.com>
 */
abstract class AbstractController extends Controller
{
    /**
     * @var array
     */
    protected $columns = array(
        'id' => 'Id',
    );

    /**
     * getData
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    protected function getData(Request $request)
    {
        return array();
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
        return null;
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
        $qb = $this->getQueryBuilder($request);
        if ($qb !== null) {
            $data = $this->getDataByQueryBuilder($request, $qb, $dataFormatter);
        } else {
            $data = $this->getData($request);
        }
        return new JsonResponse($data);
    }

    /**
     * getData
     *
     * @param Request      $request
     * @param QueryBuilder $qb
     *
     * @param null         $dataFormatter
     *
     * @return JsonResponse
     */
    protected function getDataByQueryBuilder(Request $request, QueryBuilder $qb, $dataFormatter = null)
    {
        $service = $this->get('data_tables.service');
        if ($service->getRequest() == null) {
            $service->setRequest($request);
        }
        $service->setQueryBuilder($qb);
        $service->setColumns($this->columns);

        return $service->process($dataFormatter);
    }
}