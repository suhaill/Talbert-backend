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
use AppBundle\Entity\Addresses;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AddressController extends Controller
{
    /**
     * @Route("/api/address/getShipAddByCustomerId")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: customer_id
     */
    public function getShipAddListAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $arrApi = array();
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $customerId = $data->get('customer_id');
            $addresses = $this->getAddressByCustomer($customerId);
            if (empty($addresses)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This customer has no shipping address';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully reterived shipping address list';
                for ($i=0; $i < count($addresses); $i++ ) {
                    $arrApi['data']['addresses'][$i]['id'] = $addresses[$i]->getId();
                    $arrApi['data']['addresses'][$i]['address'] = $addresses[$i]->getNickname();
                }
            }
            return new JsonResponse($arrApi);
        }
    }

    private function getAddressByCustomer($customerId) {
        return $this->getDoctrine()->getRepository('AppBundle:Addresses')->findBy(array('userId' => $customerId,'addressType'=>'shipping','status'=> 1));
    }

}
