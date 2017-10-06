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
            //$grainPatternId = trim($getJson->get('grainpattern'));
            //$flakexfigured = trim($getJson->get('flakexfigured'));
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
            
            $bottomEdge = trim($getJson->get('bedgefinish'));
            $bedgeMaterialId = trim($getJson->get('bsizeedgematerial')); 
            $bedgeFinishSpeciesId = trim($getJson->get('bedgefinishspecies')); 

            $rightEdge = trim($getJson->get('redgefinish'));
            $redgeMaterialId = trim($getJson->get('rsizeedgematerial')); 
            $redgeFinishSpeciesId = trim($getJson->get('redgefinishspecies')); 

            $leftEdge = trim($getJson->get('ledgefinish'));
            $ledgeMaterialId = trim($getJson->get('lsizeedgematerial')); 
            $ledgeFinishSpeciesId = trim($getJson->get('ledgefinishspecies')); 
            
            $milling = trim($getJson->get('milling'));
            $millingDescription = trim($getJson->get('millingDescription'));
            $cost = trim($getJson->get('cost'));
            $unitMesureCostId = trim($getJson->get('unitmeasurecost'));
            $isLabels = trim($getJson->get('isLabels'));
            $numberLabels = trim($getJson->get('labels'));
            $lumberFee = trim($getJson->get('lumberfee'));
            $autoNumberArr = $getJson->get('autoNumber');
            $autoNumberstring = '';
            
            foreach($autoNumberArr as $val) {
                
               $autoNumberstring = $autoNumberstring.$val['autoNumber'].',';
               
            }
            $autoNumberstring = rtrim($autoNumberstring,',');
            
           
            $autoNumber = $autoNumberstring;
            $comments = trim($getJson->get('comment'));
            $createdAt = new \DateTime('now');
            $fileId = trim($getJson->get('fileId'));
            $quoteId = trim($getJson->get('quoteId'));

            if (empty($quantity) || empty($speciesId) || empty($patternId) || 
            empty($grainDirectionId) || empty($gradeId) ||  empty($thicknessId) || empty($plywoodWidth) 
            || empty($plywoodLength) || empty($finishThickId) || empty($backerId)  || empty($coreType)
            || empty($thickness) || empty($finish) || empty($uvCuredId) || empty($sheenId)
              || empty($millingDescription)  || empty($cost) || empty($unitMesureCostId) || 
              empty($numberLabels)  || empty($autoNumber) || empty($lumberFee) || 
              empty($comments )) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {

                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved plywood data.';
                $statusCode = 200;
                $this->savePlywoodData($quantity, $speciesId, 
                $patternId, $grainDirectionId, $gradeId, $thicknessId, $plywoodWidth, 
                $plywoodLength,$finishThickId,$backerId,$isSequenced,$coreType, $thickness, $finish,$uvCuredId, $sheenId,
                $shameOnId,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
                $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
                $milling,$millingDescription,$cost,$unitMesureCostId,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$createdAt,$fileId,$quoteId);
            
            }

        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        return new JsonResponse($arrApi, $statusCode);
    }

    private function savePlywoodData($quantity, $speciesId, 
    $patternId, $grainDirectionId, $gradeId, $thicknessId, $plywoodWidth, 
    $plywoodLength,$finishThickId,$backerId,$isSequenced,$coreType, $thickness, $finish,$uvCuredId, $sheenId,
    $shameOnId,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
    $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
    $milling,$millingDescription,$cost,$unitMesureCostId,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$createdAt,$fileId,$quoteId)
    {
        $em = $this->getDoctrine()->getManager();
        $plywood = new Plywood();
        $plywood->setQuantity($quantity);
        $plywood->setSpeciesId($speciesId);
        $plywood->setGrainPatternId('');
        $plywood->setFlakexFiguredId('');
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

        $plywood->setBottomEdge($bottomEdge);
        $plywood->setBedgeMaterialId($bedgeMaterialId);
        $plywood->setBedgeFinishSpeciesId($bedgeFinishSpeciesId);

        $plywood->setRightEdge($rightEdge);
        $plywood->setRedgeMaterialId($redgeMaterialId);
        $plywood->setRedgeFinishSpeciesId($redgeFinishSpeciesId);

        $plywood->setLeftEdge($leftEdge);
        $plywood->setLedgeMaterialId($ledgeMaterialId);
        $plywood->setLedgeFinishSpeciesId($ledgeFinishSpeciesId);
        
        $plywood->setMilling($milling);
        $plywood->setMillingDescription($millingDescription);
        $plywood->setCost($cost);
        $plywood->setUnitMesureCostId($unitMesureCostId);
        $plywood->setIsLabels($isLabels);
        $plywood->setNumberLabels($numberLabels);
        $plywood->setLumberFee($lumberFee); 
        $plywood->setAutoNumber($autoNumber);
        //$plywood->setQuoteId('1');
        $plywood->setFileId($fileId);
        
        $plywood->setComments($comments);
        $plywood->setQuoteId($quoteId);
        $plywood->setCreatedAt($createdAt);
        $plywood->setUpdatedAt($createdAt);

        $em->persist($plywood);
        $em->flush();
    }


    /**
     * @Route("api/plywood/getPlywoodData")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     */

     public function getPlywoodDataAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $_DATA = file_get_contents('php://input');
            $_DATA = json_decode($_DATA, true);
            $arrApi = array();
            $currLoggedInUserId = $_DATA['current_user_id'];
            $currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);
            if ( $currLoggedInUserRoleId != 1 ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no access.';
            } else {
                $plywood = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findOneById($_DATA['id']);
                if (empty($plywood)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'This plywood does not exists.';
                } else {
                    if (count($_DATA) == 2 && array_key_exists('id', $_DATA) && array_key_exists('current_user_id', $_DATA)) {
                        if (empty($_DATA['id']) || empty($currLoggedInUserRoleId)) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Parameter missing.';
                        } else {
                            $arrApi['status'] = 1;
                            $arrApi['message'] = 'Successfully retrerived the plywood details.';
                            $userId = $_DATA['id'];
                            //$profileObj = $this->getProfileDataOfUser($userId);

                            $arrApi['data']['id'] = $userId;
                            $arrApi['data']['quantity'] = $plywood->getQuantity();
                            $arrApi['data']['speciesId'] = $plywood->getSpeciesId();
                            $arrApi['data']['grainPatternId'] = $plywood->getGrainPatternId();
                            $arrApi['data']['flakexFiguredId'] = $plywood->getFlakexFiguredId();
                            $arrApi['data']['patternId'] = $plywood->getPatternId();
                            $arrApi['data']['grainDirectionId'] = $plywood->getGrainDirectionId();
                            $arrApi['data']['gradeId'] = $plywood->getGradeId();
                            $arrApi['data']['thicknessId'] = $plywood->getThicknessId();
                            $arrApi['data']['width'] = $plywood->getPlywoodWidth();
                            
                            $arrApi['data']['length'] = $plywood->getPlywoodLength();
                            $arrApi['data']['finishThickId'] = $plywood-> getFinishThickId();
                            $arrApi['data']['thickness'] = $plywood->getThickness();
                            $arrApi['data']['finish'] = $plywood->getFinish();
                            $arrApi['data']['uvCuredId'] = $plywood->getUvCuredId();
                            $arrApi['data']['sheenId'] = $plywood->getSheenId();
                            $arrApi['data']['shameOnId'] = $plywood-> getShameOnId();
                            $arrApi['data']['edgeDetail'] = $plywood->getEdgeDetail();
                            
                            $arrApi['data']['topEdge'] = $plywood->getTopEdge();
                            $arrApi['data']['edgeMaterialId'] = $plywood->getEdgeMaterialId();
                            $arrApi['data']['edgeFinishSpeciesId'] = $plywood->getEdgeFinishSpeciesId();

                            $arrApi['data']['bottomEdge'] = $plywood->getBottomEdge();
                            $arrApi['data']['bedgeMaterialId'] = $plywood->getBedgeMaterialId();
                            $arrApi['data']['bedgeFinishSpeciesId'] = $plywood->getBedgeFinishSpeciesId();

                            $arrApi['data']['rightEdge'] = $plywood->getRightEdge();
                            $arrApi['data']['redgeMaterialId'] = $plywood->getRedgeMaterialId();
                            $arrApi['data']['redgeFinishSpeciesId'] = $plywood->getRedgeFinishSpeciesId();

                            $arrApi['data']['leftEdge'] = $plywood->getLeftEdge();
                            $arrApi['data']['ledgeMaterialId'] = $plywood->getLedgeMaterialId();
                            $arrApi['data']['ledgeFinishSpeciesId'] = $plywood->getLedgeFinishSpeciesId();
                            
                            $arrApi['data']['milling'] = $plywood->getMilling();
                            $arrApi['data']['millingDescription'] = $plywood->getMillingDescription();
                            $arrApi['data']['cost'] = $plywood->getCost();
                            $arrApi['data']['unitMesureCostId'] = $plywood->getUnitMesureCostId();
                            $arrApi['data']['isLabels'] = $plywood->getIsLabels();
                            $arrApi['data']['numberLabels'] = $plywood->getNumberLabels();
                            //echo $arrApi['data']['autoNumber'];
                            if (!empty($plywood->getAutoNumber())) {
                                $tags = explode(',', $plywood->getAutoNumber());
                               
                                for ($i=0; $i< count($tags); $i++) {
                                    $arrApi['data']['autoNumber'][$i]['autoNumber'] = $tags[$i]; 
                                }
                            }
                            //die();
                           // $arrApi['data']['autoNumber'] = $plywood->getAutoNumber();
                            $arrApi['data']['coreType'] = $plywood->getCoreType();
                            $arrApi['data']['backerId'] = $plywood->getBackerId();
                       
                            $arrApi['data']['isSequenced'] = $plywood->getIsSequenced();
                            $arrApi['data']['lumberFee'] = $plywood->getLumberFee();
                            $arrApi['data']['comments'] = $plywood->getComments();
                            $arrApi['data']['quoteId'] = $plywood->getQuoteId();
                            $arrApi['data']['fileId'] = $plywood->getFileId();
                            
                            $arrApi['data']['fileLink'] = $this->getFileUrl( $plywood->getFileId(),$request );

                            
                        }
                    }
                }
            }
        }
        return new JsonResponse($arrApi);
    }

    private function getRoleIdByUserId($currLoggedInUserId) {
        $loggedInUserData = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($currLoggedInUserId);
        if (empty($loggedInUserData)) {
            return null;
        } else {
            $roleId = $loggedInUserData->getRoleId();
            return $roleId;
        }
    }

    /**
     * @Route("/api/plywood/edit" , name="edit_plywood")
     * @Method({"POST"})
     * Security("is_granted('ROLE_USER')")
     */
     public function editPlywoodAction(Request $request) {
        
        $arrApi = [];
        $statusCode = 200;
        try {

            $jsontoarraygenerator = new JsonToArrayGenerator();
            $getJson = $jsontoarraygenerator->getJson($request);
            $id = trim($getJson->get('id'));
            $quantity = trim($getJson->get('quantity'));
            $speciesId = trim($getJson->get('species'));
            //$grainPatternId = trim($getJson->get('grainpattern'));
            //$flakexfigured = trim($getJson->get('flakexfigured'));
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
            
            $bottomEdge = trim($getJson->get('bedgefinish'));
            $bedgeMaterialId = trim($getJson->get('bsizeedgematerial')); 
            $bedgeFinishSpeciesId = trim($getJson->get('bedgefinishspecies')); 

            $rightEdge = trim($getJson->get('redgefinish'));
            $redgeMaterialId = trim($getJson->get('rsizeedgematerial')); 
            $redgeFinishSpeciesId = trim($getJson->get('redgefinishspecies')); 

            $leftEdge = trim($getJson->get('ledgefinish'));
            $ledgeMaterialId = trim($getJson->get('lsizeedgematerial')); 
            $ledgeFinishSpeciesId = trim($getJson->get('ledgefinishspecies')); 
            
            $milling = trim($getJson->get('milling'));
            $millingDescription = trim($getJson->get('millingDescription'));
            $cost = trim($getJson->get('cost'));
            $unitMesureCostId = trim($getJson->get('unitmeasurecost'));
            $isLabels = trim($getJson->get('isLabels'));
            $numberLabels = trim($getJson->get('labels'));
            $lumberFee = trim($getJson->get('lumberfee'));
            $autoNumberArr = $getJson->get('autoNumber');
            $autoNumberstring = '';
            
            foreach($autoNumberArr as $val) {
                
               $autoNumberstring = $autoNumberstring.$val['autoNumber'].',';
               
            }
            $autoNumberstring = rtrim($autoNumberstring,',');
            
           
            $autoNumber = $autoNumberstring;

            //$autoNumber = trim($getJson->get('autoNumber'));
            $comments = trim($getJson->get('comment'));
            $createdAt = new \DateTime('now');
            $fileId = trim($getJson->get('fileId'));

            if (empty($id) || empty($quantity) || empty($speciesId) || empty($patternId) || 
            empty($grainDirectionId) || empty($gradeId) ||  empty($thicknessId) || empty($plywoodWidth) 
            || empty($plywoodLength) || empty($finishThickId) || empty($backerId)  || empty($coreType)
            || empty($thickness) || empty($finish) || empty($uvCuredId) || empty($sheenId) || empty($topEdge) || empty($edgeMaterialId) 
             || empty($millingDescription)  || empty($cost) || 
            empty($unitMesureCostId) || empty($numberLabels) 
            || empty($autoNumber) || empty($lumberFee) || empty($comments ) || empty($fileId )) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {

                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved plywood data.';
                $statusCode = 200;
                $this->editPlywoodData($id,$quantity, $speciesId, 
                $patternId, $grainDirectionId, $gradeId, $thicknessId, $plywoodWidth, 
                $plywoodLength,$finishThickId,$backerId,$isSequenced,$coreType, $thickness, $finish,$uvCuredId, $sheenId,
                $shameOnId,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
                $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
                $milling,$millingDescription,$cost,$unitMesureCostId,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$fileId,$createdAt);
            
            }

        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        return new JsonResponse($arrApi, $statusCode);
    }

    private function editPlywoodData($id,$quantity, $speciesId, 
    $patternId, $grainDirectionId, $gradeId, $thicknessId, $plywoodWidth, 
    $plywoodLength,$finishThickId,$backerId,$isSequenced,$coreType, $thickness, $finish,$uvCuredId, $sheenId,
    $shameOnId,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
    $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
    $milling,$millingDescription,$cost,$unitMesureCostId,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$fileId,$createdAt) 
    {
        $em = $this->getDoctrine()->getManager();
        $plywood =  $this->getDoctrine()->getRepository('AppBundle:Plywood')->find($id);
        
       // $em = $this->getDoctrine()->getManager();
        //$plywood = new Plywood();
        $plywood->setQuantity($quantity);
        $plywood->setSpeciesId($speciesId);
        $plywood->setGrainPatternId('');
        $plywood->setFlakexFiguredId('');
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

        $plywood->setBottomEdge($bottomEdge);
        $plywood->setBedgeMaterialId($bedgeMaterialId);
        $plywood->setBedgeFinishSpeciesId($bedgeFinishSpeciesId);

        $plywood->setRightEdge($rightEdge);
        $plywood->setRedgeMaterialId($redgeMaterialId);
        $plywood->setRedgeFinishSpeciesId($redgeFinishSpeciesId);

        $plywood->setLeftEdge($leftEdge);
        $plywood->setLedgeMaterialId($ledgeMaterialId);
        $plywood->setLedgeFinishSpeciesId($ledgeFinishSpeciesId);

        $plywood->setMilling($milling);
        $plywood->setMillingDescription($millingDescription);
        $plywood->setCost($cost);
        $plywood->setUnitMesureCostId($unitMesureCostId);
        $plywood->setIsLabels($isLabels);
        $plywood->setNumberLabels($numberLabels);
        $plywood->setLumberFee($lumberFee); 
        $plywood->setAutoNumber($autoNumber);
        //$plywood->setQuoteId('1');
        $plywood->setComments($comments);
        $plywood->setFileId($fileId);
       //$veneer->setQuoteId('1');
        //$plywood->setCreatedAt($createdAt);
        $plywood->setUpdatedAt($createdAt);

        $em->flush();
    }

    function getFileUrl($fileId,$request)
    {
        
        $files = $this->getDoctrine()->getRepository('AppBundle:Files')->findOneById($fileId);
        
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
       
        return $baseurl.'/uploads/'.$files->getFileName();

    }

}