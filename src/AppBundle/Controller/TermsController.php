<?php
/**
 * Created by PhpStorm.
 * User: d-14
 * Date: 8/9/17
 * Time: 3:28 PM
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class TermsController extends Controller
{


    /**
     * @Route("/api/terms")
     * @Method({"GET"})
     */

    public function termsAction(){
        $arrApi = [];
        $statusCode = 200;

        try{
            $em = $this->getDoctrine()->getManager();

            $terms = $em->getRepository('AppBundle:Terms')->findAll();
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Terms list successfully received';

            for ($i=0; $i<count($terms); $i++) {

                $arrApi['data']['terms'][$i]['id'] = $terms[$i]->getId();
                $arrApi['data']['terms'][$i]['name'] = $terms[$i]->getName();

            }

        }
        catch(\Exception $e){
            echo "Exception Found - " . $e->getMessage() . "<br/>";

        }



        return new JsonResponse($arrApi);


    }
}