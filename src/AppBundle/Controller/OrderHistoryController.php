<?php
namespace AppBundle\Controller;
use AppBundle\Entity\OrderHistory;
use AppBundle\Service\JsonToArrayGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class OrderHistoryController extends Controller
{



    /**
     * @Route("api/order/getvendororderhistory")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * parameters: None
     *
     */

    public function getVendorOrderHistoryAction(Request $request) {
        $arrApi = [];
        $statusCode = 200;
        $i=0;
        $orderhistories = $this->getDoctrine()->getRepository('AppBundle:OrderHistory')->findAll();
        foreach ($orderhistories as $orderhistory){
            $arrApi['data'][$i]['id']= $orderhistory->getId();
            $arrApi['data'][$i]['customer_name']= $orderhistory->getCustomerName();
            $arrApi['data'][$i]['po_number']= $orderhistory->getPoNumber();
            $arrApi['data'][$i]['cost'] = $orderhistory->getCost();
            $arrApi['data'][$i]['comments'] = $orderhistory->getComments();
            $i++;

        }

        $arrApi['message'] = 'Successfully retreived the order history.';
        $arrApi['status']=1;
        $statusCode = 200;
        return new JsonResponse($arrApi,$statusCode);

    }

}