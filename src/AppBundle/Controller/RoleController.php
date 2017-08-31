<?php

namespace AppBundle\Controller;

namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    /**
     * @Route("/api/role/getRoles")
     * @Method("GET")
     * parameters: None
     * url: http://localhost/Talbert/backend/web/app_dev.php/api/role/getRoles
     */
    public function getRolesAction(Request $request)
    {
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $roles = $this->getDoctrine()->getRepository('AppBundle:Role')->findAll();
            if (empty($roles) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no role.';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the role list.';
                for($i=0;$i<count($roles);$i++) {
                    $arrApi['data']['roles'][$i]['id'] = $roles[$i]->getId();
                    $arrApi['data']['roles'][$i]['role_name'] = $roles[$i]->getName();
                }
            }
            return new JsonResponse($arrApi);
        }
    }

}
