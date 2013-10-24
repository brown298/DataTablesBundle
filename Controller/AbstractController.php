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
     * @return null
     */
     protected function getQueryBuilder()
     {
        return null;
     }

    /**
     * dataAction
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function dataAction(Request $request)
    {
        $qb = $this->getQueryBuilder();
        if ($qb !== null) {
            $data = $this->getDataByQueryBuilder($request, $this->getQueryBuilder());
        } else {
            $data = $this->getData($request);
        }
        return new JsonResponse($data);
    }

    /**
     * getData
     *
     * @param Request $request
     * @param QueryBuilder $qb
     *
     * @return JsonResponse
     */
    protected function getDataByQueryBuilder(Request $request, QueryBuilder $qb)
    {
        $service = $this->get('data_tables.service');
        $service->setRequest($request);
        $service->setQueryBuilder($qb);

        return $service->process();
    }
}