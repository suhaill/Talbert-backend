<?php

namespace AppBundle\Controller;

use AppBundle\Service\JsonToArrayGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use PDO;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Orders;

class OrderController extends Controller
{
    /**
     * @Route("/api/order/getOrders")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getOrdersListAction(Request $request) {
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $orders = $this->getDoctrine()->getRepository('AppBundle:Orders')->findBy(array(), array('id' => 'DESC'));
            if (empty($orders)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no order right now';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the orders list';
                $i=0;
                foreach ($orders as $orDat) {
                    $arrApi['data']['orders'][$i]['id'] = $orDat->getId();
                    $arrApi['data']['orders'][$i]['estNumber'] = $orDat->getEstNumber();
                    $arrApi['data']['orders'][$i]['orderDate'] = $orDat->getOrderDate()->format('m/d/y');
                    $arrApi['data']['orders'][$i]['productname'] = $orDat->getProductName();
                    $arrApi['data']['orders'][$i]['poNumber'] = $orDat->getPoNumber();
                    $arrApi['data']['orders'][$i]['shipDate'] = $orDat->getShipDate()->format('m/d/y');
                    $i++;
                }
            }
            return new JsonResponse($arrApi);
        }
    }

}
