<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Testing;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\JsonToArrayGenerator;

class TestingController extends Controller
{
    /**
     * @Route("/api/testing/{id}", name="testing")
     */

    public function indexAction($id,Request $request)
    {

       $jsontoarraygenerator = new JsonToArrayGenerator();
        $data['status'] = '100';
        

        if ($request->getMethod() == 'GET') {

            $data['msg'] = 'get';
             $data['data'] = $id;

            $testing= new testing();
            $testing->setData( $data['data']);
            $testing->setStatus($data['status']);
            $testing->setMsg($data['msg']);


            $em = $this->getDoctrine()->getManager();

            $em->persist($testing);
            $em->flush();
            $lastInsertId = $testing->getId();
            
            $data['lastInsertId'] = $lastInsertId;

            $response = new  JsonResponse($data);
            return $response;
        
        }

        if ($request->getMethod() == 'POST') {

            $getJson = $jsontoarraygenerator->getJson($request);
            $data['data'] = trim($getJson->get('data'));

           // $data = $request->request->get('data');
            //die($data);
            $data['msg'] = 'post';
            $data['data'] = $id;
            $testing= new testing();
            $testing->setData( $data['data']);
            $testing->setStatus($data['status']);
            $testing->setMsg($data['msg']);


            $em = $this->getDoctrine()->getManager();

            $em->persist($testing);
            $em->flush();

            $lastInsertId = $testing->getId();
            
            $data['lastInsertId'] = $lastInsertId;

            $response = new  JsonResponse($data);
            return $response;
        }
    
    }
}

