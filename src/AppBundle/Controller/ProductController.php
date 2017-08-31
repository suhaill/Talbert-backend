<?php
/**
 * Created by PhpStorm.
 * User: d-14
 * Date: 21/8/17
 * Time: 12:47 PM
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
/**
 * @Security("is_granted('ROLE_USER')")
 */
class ProductController extends Controller
{

    /**
     * @Route("/api/getusername")
     *
     */
    public function getUserName(){
        $user = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();

     return new JsonResponse($user);
    }
}