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
use AppBundle\Entity\ShippingMethods;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ShippingMethodController extends Controller
{
    /**
     * @Route("/api/shippingmethod/getShippingMethods")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getShippingMethodListAction(Request $request) {
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $users = $this->getDoctrine()->getRepository('AppBundle:ShippingMethods')->findBy(array('status'=> 1));
            if ( empty($users) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no salesman.';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the salesman list.';
                for ($i=0; $i<count($users); $i++) {
                    $userId = $users[$i]->getId();
                    if (!empty($userId)) {
                        $arrApi['data']['shippingmethods'][$i]['id'] = $users[$i]->getId();
                        $arrApi['data']['shippingmethods'][$i]['name'] = $users[$i]->getName();
                    }
                }
            }
            return new JsonResponse($arrApi);
        }
    }

}
