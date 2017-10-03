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
use AppBundle\Entity\Plywood;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        try {

            $jsontoarraygenerator = new JsonToArrayGenerator();
            $getJson = $jsontoarraygenerator->getJson($request);

            $quantity = trim($getJson->get('quantity'));
            $speciesId = trim($getJson->get('species'));
            $grainPatternId = trim($getJson->get('grainpattern'));
            $flakexfigured = trim($getJson->get('flakexfigured'));
            $patternId = trim($getJson->get('pattern'));
            $grainDirectionId = trim($getJson->get('graindirection'));
            $gradeId = trim($getJson->get('facegrade'));
            $thicknessId = trim($getJson->get('thickness'));
            $plywoodWidth = trim($getJson->get('width'));
            $plywoodLength = trim($getJson->get('length'));
            $finishThickId = trim($getJson->get('finishthick'));
           
            $isSequenced = trim($getJson->get('sequenced'));
            $coreType = trim($getJson->get('coretype'));
            $thickness = trim($getJson->get('corethickness'));
            $finish = trim($getJson->get('finish'));
            $uvCuredId = trim($getJson->get('uvcured'));
            $sheenId = trim($getJson->get('sheen'));
            $shameOnId = trim($getJson->get('coresameon'));
            $backerId = trim($getJson->get('backergrade'));
            $edgeDetail = trim($getJson->get('egdedetail'));
            $topEdge = trim($getJson->get('edgefinish'));
            $edgeMaterialId = trim($getJson->get('sizeedgematerial')); 
            $edgeFinishSpeciesId = trim($getJson->get('edgefinishspecies'));    
            $milling = trim($getJson->get('milling'));
            $millingDescription = trim($getJson->get('millingDescription'));
            $cost = trim($getJson->get('cost'));
            $unitMesureCostId = trim($getJson->get('unitmeasurecost'));
            $isLabels = trim($getJson->get('isLabels'));
            $numberLabels = trim($getJson->get('labels'));
            $lumberFee = trim($getJson->get('lumberfee'));
            $autoNumber = trim($getJson->get('autoNumber'));
            $comments = trim($getJson->get('comment'));
            $createdAt = new \DateTime('now');
            $fileId = trim($getJson->get('fileId'));
            if (empty($quantity) || empty($speciesId) || empty($grainPatternId) || empty($patternId) || 
            empty($grainDirectionId) || empty($gradeId) ||  empty($thicknessId) || empty($plywoodWidth) 
            || empty($plywoodLength) || empty($finishThickId) || empty($backerId)  || empty($coreType)
            || empty($thickness) || empty($finish) || empty($uvCuredId) || empty($sheenId) || 
            empty($edgeDetail) || empty($topEdge) || empty($edgeMaterialId) || empty($edgeFinishSpeciesId) 
            || empty($milling) || empty($millingDescription)  || empty($cost) || 
            empty($unitMesureCostId) || empty($isLabels) || empty($numberLabels) 
            || empty($autoNumber) || empty($lumberFee) || empty($comments )) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {

                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved plywood data.';
                $statusCode = 200;
                $this->savePlywoodData($quantity, $speciesId, $grainPatternId, $flakexfigured, 
                $patternId, $grainDirectionId, $gradeId, $thicknessId, $plywoodWidth, 
                $plywoodLength,$finishThickId,$backerId,$isSequenced,$coreType, $thickness, $finish,$uvCuredId, $sheenId,
                $shameOnId,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,
                $milling,$millingDescription,$cost,$unitMesureCostId,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$createdAt,$fileId);
            
            }

        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        return new JsonResponse($arrApi, $statusCode);
    }

    private function savePlywoodData($quantity, $speciesId, $grainPatternId, $flakexfigured, 
    $patternId, $grainDirectionId, $gradeId, $thicknessId, $plywoodWidth, 
    $plywoodLength,$finishThickId,$backerId,$isSequenced,$coreType, $thickness, $finish,$uvCuredId, $sheenId,
    $shameOnId,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,
    $milling,$millingDescription,$cost,$unitMesureCostId,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$createdAt,$fileId)
    {
        $em = $this->getDoctrine()->getManager();
        $plywood = new Plywood();
        $plywood->setQuantity($quantity);
        $plywood->setSpeciesId($speciesId);
        $plywood->setGrainPatternId($grainPatternId);
        $plywood->setGrainPatternId($flakexfigured);
        $plywood->setPatternId($patternId);
        $plywood->setGrainDirectionId($grainDirectionId);
        $plywood->setGradeId($gradeId);
        $plywood->setThicknessId($thicknessId);
        $plywood->setPlywoodWidth($plywoodWidth);
        $plywood->setPlywoodLength($plywoodLength);
        $plywood->setFinishThickId($finishThickId);
        $plywood->setBackerId($backerId);
        $plywood->setIsSequenced($isSequenced);
        $plywood->setCoreType($coreType);
        $plywood->setThickness($thickness);
        $plywood->setFinish($finish);
        $plywood->setUvCuredId($uvCuredId);
        $plywood->setSheenId($sheenId);
        $plywood->setShameOnId($shameOnId);
        $plywood->setEdgeDetail($edgeDetail);
        $plywood->setTopEdge($topEdge);
        $plywood->setEdgeMaterialId($edgeMaterialId);
        $plywood->setEdgeFinishSpeciesId($edgeFinishSpeciesId);
        $plywood->setMilling($milling);
        $plywood->setMillingDescription($millingDescription);
        $plywood->setCost($cost);
        $plywood->setUnitMesureCostId($unitMesureCostId);
        $plywood->setIsLabels($isLabels);
        $plywood->setNumberLabels($numberLabels);
        $plywood->setLumberFee($lumberFee); 
        $plywood->setAutoNumber($autoNumber);
        $plywood->setFileId($fileId);
        
        $plywood->setComments($comments);
       //$veneer->setQuoteId('1');
        $plywood->setCreatedAt($createdAt);
        $plywood->setUpdatedAt($createdAt);

        $em->persist($plywood);
        $em->flush();
    }

}