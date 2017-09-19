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

class ProductController extends Controller
{


    /**
     * @Route("api/product/getProducts")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * parameters: None
     *
     */

    public function getProductsAction(Request $request) {
        $arrApi = [];
        $statusCode = 200;
        $i=0;
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAll();
        foreach ($products as $product){
            $arrApi['data'][$i]['id']= $product->getId();
            $arrApi['data'][$i]['userId']= $product->getUserId();
            $arrApi['data'][$i]['cost']= $product->getCost();
            $arrApi['data'][$i]['perUnit']= $product->getPerUnit();
            $arrApi['data'][$i]['description'] = $product->getDescription();
            $arrApi['data'][$i]['comments'] = $product->getComments();
            $i++;

        }

        $arrApi['message'] = 'Successfully retreived products';
        $arrApi['status']=1;
        $statusCode = 200;
        return new JsonResponse($arrApi,$statusCode);

    }




    /**
     * @Route("api/product/update/{id}")
     * @Method("PUT")
     * @Security("is_granted('ROLE_USER')")
     * parameters: None
     *
     */

    public function updateVendorAction($id,Request $request) {
        $arrApi = [];
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $getJson = $jsontoarraygenerator->getJson($request);
        $cost = $getJson->get('cost');
        $comment = $getJson->get('comments');
        try{
            $em = $this->getDoctrine()->getManager();
            $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
            $cost ? $product->setCost($cost) :$product->setCost($product->getCost());
            $comment ? $product->setComments($comment) :$product->setComments($product->getComments());
            $em->flush();

        }catch (Exception $e){
            throw  $e;
        }

        $arrApi['status'] = 1;
        $arrApi['message'] = 'Product data updated';
        $statusCode = 200;
        return new JsonResponse($arrApi,$statusCode);

    }


}