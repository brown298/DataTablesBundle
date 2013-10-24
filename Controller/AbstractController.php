<?php
namespace Brown298\DataTablesBundle\Controller;

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
     * getQueryBuilder
     *
     * @return null
     */
    abstract protected function getQueryBuilder();

    /**
     * dataAction
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function dataAction(Request $request)
    {
        $service = $this->get('data_tables.service');
        $service->setRequest($request);
        $service->setQueryBuilder($this->getQueryBuilder());

        return new JsonResponse($service->process());
    }
}