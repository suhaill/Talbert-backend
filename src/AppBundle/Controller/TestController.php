<?php
/**
 * Created by PhpStorm.
 * User: d-14
 * Date: 9/8/17
 * Time: 5:35 PM
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
/**
 * @Security("is_granted('ROLE_USER')")
 */
class TestController extends Controller
{
    /**
     * @Route("/api/test/new")
     */
    public function newAction(){
        $response = new Response(json_encode(array('name' => "Rajesh patel")));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}