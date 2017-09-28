<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Plywood;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\JsonToArrayGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PlywoodController extends Controller
{

	 /**
     * @Route("/api/plywood/add" , name="add_plywood")
     * @Method({"POST"})
     * Security("is_granted('ROLE_USER')")
     */
    public function addPlywoodAction(Request $request) {

        $arrApi = [];
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $getJson = $jsontoarraygenerator->getJson($request);

        $quantity = trim($getJson->get('quantity'));
        $speciesId = trim($getJson->get('speciesId'));
        $grainPatternId = trim($getJson->get('grainPatternId'));
        $patternId = trim($getJson->get('patternId'));
        $grainDirectionId = trim($getJson->get('grainDirectionId'));
        $gradeId = trim($getJson->get('gradeId'));
        $thicknessId = trim($getJson->get('thicknessId'));
        $plywoodWidth = trim($getJson->get('plywoodWidth'));
        $plywoodLength = trim($getJson->get('plywoodLength'));
        $finishThickId = trim($getJson->get('finishThickId'));
        $backerId = trim($getJson->get('backerId'));
        $isSequenced = trim($getJson->get('isSequenced'));
        $coreType = trim($getJson->get('coreType'));
        $thickness = trim($getJson->get('thickness'));
        $finish = trim($getJson->get('finish'));
        $uvCuredId = trim($getJson->get('uvCuredId'));
        $sheenId = trim($getJson->get('sheenId'));
        $shameOnId = trim($getJson->get('shameOnId'));
        $edgeDetail = trim($getJson->get('edgeDetail'));
        $topEdge = trim($getJson->get('topEdge'));
        $edgeMaterialId = trim($getJson->get('edgeMaterialId'));   
        $milling = trim($getJson->get('milling'));
        $millingDescription = trim($getJson->get('millingDescription'));
        $cost = trim($getJson->get('cost'));
        $unitMesureCostId = trim($getJson->get('unitMesureCostId'));
        $isLabels = trim($getJson->get('isLabels'));
        $numberLabels = trim($getJson->get('numberLabels'));
        $lumberFee = trim($getJson->get('lumberFee'));
        $autoNumber = trim($getJson->get('autoNumber'));
        $comments = trim($getJson->get('comments'));


        //$response = new JsonResponse($request);
        //return $response;
    }

}