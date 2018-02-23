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
use AppBundle\Entity\User;
use AppBundle\Entity\Profiles;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class SalesmanController extends Controller
{
    /**
     * @Route("/api/salesman/getSalesmans")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getSalesmanListAction(Request $request) {
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $users = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('roleId' => 4,'isActive'=> 1),array('id' => 'DESC'));
            if ( empty($users) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no salesman.';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the salesman list.';
                for ($i=0; $i<count($users); $i++) {
                    $userId = $users[$i]->getId();
                    if (!empty($userId)) {
                        $arrApi['data']['salesmans'][$i]['id'] = $users[$i]->getId();
                        $arrApi['data']['salesmans'][$i]['name'] = explode(' ', $this->getFnameById($userId))[0];
                    }
                }
            }
            return new JsonResponse($arrApi);
        }
    }


    // reusable methods

    public function getFnameById($userid) {
        if (!empty($userid)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $userid));
            return $profileObj->getFname();
        }
    }

    private function getLnameById($userid) {
        if (!empty($userid)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $userid));
            return $profileObj->getLname();
        }
    }

}
