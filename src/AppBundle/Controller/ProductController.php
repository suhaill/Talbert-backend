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
        /* $arrApi['status'] = 1;
        $arrApi['message'] = 'Successfully retreived the backer grades list.'; */
        $products = $this->getDoctrine()->getRepository('AppBundle:Product')->findAll();

        if (empty($products) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no backer grades.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the backer grades list.';
            for($i=0;$i<count($products);$i++) {
                $arrApi['data']['products'][$i]['id'] = $products[$i]->getId();
                $arrApi['data']['products'][$i]['productname'] = $products[$i]->getProductName();
                $arrApi['data']['products'][$i]['userId']= $products[$i]->getUserId();
                $arrApi['data']['products'][$i]['cost']= $products[$i]->getCost();
                $arrApi['data']['products'][$i]['perUnit']= $products[$i]->getPerUnit();
                $arrApi['data']['products'][$i]['description'] = $products[$i]->getDescription();
                $arrApi['data']['products'][$i]['comments'] = $products[$i]->getComments();
                
            }
        }
       
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
        $cost = trim($getJson->get('cost'));
        $comment = trim($getJson->get('comments'));
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