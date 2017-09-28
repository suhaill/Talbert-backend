<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Venner;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\JsonToArrayGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class VeneerController extends Controller
{

	 /**
     * @Route("/api/veneer/add" , name="add_veneer")
     * @Method({"POST"})
     * Security("is_granted('ROLE_USER')")
     */
    public function addVeneerAction(Request $request) {

        $arrApi = [];
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $getJson = $jsontoarraygenerator->getJson($request);

        $quantity = trim($getJson->get('quantity'));
        $speciesId = trim($getJson->get('speciesId'));
        $grainPatternId = trim($getJson->get('grainPatternId'));
        $grainDirectionId = trim($getJson->get('grainDirectionId'));
        $gradeId = trim($getJson->get('gradeId'));
        $thicknessId = trim($getJson->get('thicknessId'));
        $width = trim($getJson->get('width'));
        $isNetSize = trim($getJson->get('isNetSize'));
        $length = trim($getJson->get('length'));
        $coreTypeId = trim($getJson->get('coreTypeId'));
        $backer = trim($getJson->get('backer'));
        $isFlexSanded = trim($getJson->get('isFlexSanded'));
        $sequenced = trim($getJson->get('sequenced'));
        $lumberFee = trim($getJson->get('lumberFee'));
        $comments = trim($getJson->get('comments'));
        $quoteId = trim($getJson->get('quoteId'));

        //$response = new JsonResponse($request);
        //return $response;
    }

}