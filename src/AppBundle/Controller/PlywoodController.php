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
use AppBundle\Entity\Files;
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
            $patternMatch = trim($getJson->get('patternMatch'));
            $gradeId = trim($getJson->get('facegrade'));
            $thicknessId = trim($getJson->get('thickness'));
            $plywoodWidth = trim($getJson->get('width'));
            $widthFraction = trim($getJson->get('widthFraction'));
            $netsize = trim($getJson->get('netsize'));
            $plywoodLength = trim($getJson->get('length'));
            $lengthFraction = trim($getJson->get('lengthFraction'));
            $finishThickId = trim($getJson->get('finishthick'));
            $finishThicktype = trim($getJson->get('finishThicktype'));
            $isSequenced = trim($getJson->get('sequenced'));
            $coreType = trim($getJson->get('coretype'));
            $thickness = trim($getJson->get('corethickness'));
            $finish = trim($getJson->get('finish'));
            $facPaint = trim($getJson->get('facPaint'));
            $uvCuredId = trim($getJson->get('uvcured'));
            $uvColorId = trim($getJson->get('uvcolor'));
            $sheenId = trim($getJson->get('sheen'));
            $shameOnId = trim($getJson->get('coresameon'));
            $coreSameOnbe = trim($getJson->get('coresameonbe'));
            $coreSameOnte = trim($getJson->get('coresameonte'));
            $coreSameOnre = trim($getJson->get('coresameonre'));
            $coreSameOnle = trim($getJson->get('coresameonle'));
            $backerId = trim($getJson->get('backergrade'));
            $edgeDetail = trim($getJson->get('egdedetail'));
            $edgeSameOnB = trim($getJson->get('edgeSameOnB'));
            $edgeSameOnR = trim($getJson->get('edgeSameOnR'));
            $edgeSameOnL = trim($getJson->get('edgeSameOnL'));
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
            $unitMesureCostId = trim($getJson->get('unitmeasurecost'));
            $running = trim($getJson->get('running'));
            $runningDescription = trim($getJson->get('runningDescription'));
            $unitMesureCostIdR = trim($getJson->get('unitmeasurecostR'));
            $isLabels = trim($getJson->get('isLabels'));
            $numberLabels = trim($getJson->get('labels'));
            $lumberFee = trim($getJson->get('lumberfee'));
            $autoNumberArr = $getJson->get('autoNumber');
            $autoNumberstring = '';

            if($autoNumberArr)
            {
                foreach($autoNumberArr as $val) {
                    
                   $autoNumberstring = $autoNumberstring.$val['autoNumber'].',';
                   
                }
                $autoNumberstring = rtrim($autoNumberstring,',');
            }
            
            $autoNumber = $autoNumberstring;
            $comments = trim($getJson->get('comment'));
            $createdAt = new \DateTime('now');
            $fileId = trim($getJson->get('fileId'));
            $quoteId = trim($getJson->get('quoteId'));
            $formtype = trim($getJson->get('formtype'));

            if (empty($quantity) || empty($speciesId) || empty($patternId) || 
            empty($grainDirectionId) || empty($patternMatch) || empty($gradeId) ||  empty($thicknessId) || empty($plywoodWidth)
            || empty($plywoodLength) || empty($finishThickId) || empty($backerId)  || empty($coreType)
            || empty($thickness) || empty($finish) || empty($facPaint) || empty($uvCuredId) || empty($sheenId) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {

                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved plywood data.';
                $statusCode = 200;
                $lastInserted = $this->savePlywoodData($quantity, $speciesId, 
                $patternId, $grainDirectionId, $patternMatch, $gradeId, $thicknessId, $plywoodWidth,$widthFraction,$netsize,
                $plywoodLength,$lengthFraction,$finishThickId,$finishThicktype,$backerId,$isSequenced,$coreType, $thickness, $finish, $facPaint, $uvCuredId,$uvColorId, $sheenId,
                $shameOnId,$coreSameOnbe,$coreSameOnte,$coreSameOnre,$coreSameOnle,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
                $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
                $milling,$millingDescription,$unitMesureCostId,$running,$runningDescription,$unitMesureCostIdR,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$createdAt,$fileId,$quoteId,$formtype,$edgeSameOnB,$edgeSameOnR,$edgeSameOnL);
                $arrApi['lastInserted'] = $lastInserted;
            }

        }

        catch(Exception $e) {

            throw $e->getMessage();

        }

        return new JsonResponse($arrApi, $statusCode);
    }

    private function savePlywoodData($quantity, $speciesId, 
    $patternId, $grainDirectionId, $patternMatch, $gradeId, $thicknessId, $plywoodWidth,$widthFraction,$netsize,
    $plywoodLength,$lengthFraction,$finishThickId,$finishThicktype,$backerId,$isSequenced,$coreType, $thickness, $finish,$facPaint, $uvCuredId,$uvColorId, $sheenId,
    $shameOnId,$coreSameOnbe,$coreSameOnte,$coreSameOnre,$coreSameOnle,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
    $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
    $milling,$millingDescription,$unitMesureCostId,$running,$runningDescription,$unitMesureCostIdR,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$createdAt,$fileId,$quoteId,$formtype,$edgeSameOnB,$edgeSameOnR,$edgeSameOnL)
    {
        $em = $this->getDoctrine()->getManager();
        $plywood = new Plywood();
        $plywood->setQuantity($quantity);
        $plywood->setSpeciesId($speciesId);
        $plywood->setGrainPatternId('');
        $plywood->setFlakexFiguredId('');
        $plywood->setPatternId($patternId);
        $plywood->setGrainDirectionId($grainDirectionId);
        $plywood->setPatternMatch($patternMatch);
        $plywood->setGradeId($gradeId);
        $plywood->setThicknessId($thicknessId);
        $plywood->setPlywoodWidth($plywoodWidth);
        $plywood->setWidthFraction($widthFraction);
        $plywood->setIsNetSize($netsize);
        $plywood->setPlywoodLength($plywoodLength);
        $plywood->setLengthFraction($lengthFraction);
        $plywood->setFinishThickId($finishThickId);
        $plywood->setFinishThickType($finishThicktype);
        $plywood->setBackerId($backerId);
        $plywood->setIsSequenced($isSequenced);
        $plywood->setCoreType($coreType);
        $plywood->setThickness($thickness);
        $plywood->setFinish($finish);
        $plywood->setFacPaint($facPaint);
        $plywood->setUvCuredId($uvCuredId);

        $plywood->setUvColorId($uvColorId); 
        $plywood->setSheenId($sheenId);
        $plywood->setShameOnId($shameOnId);

        $plywood->setCoreSameOnbe($coreSameOnbe);
        $plywood->setCoreSameonte($coreSameOnte);
        $plywood->setCoreSameOnre($coreSameOnre);
        $plywood->setCoreSameOnle($coreSameOnle);

        $plywood->setEdgeDetail($edgeDetail);
        $plywood->setEdgeSameOnB($edgeSameOnB);
        $plywood->setEdgeSameOnR($edgeSameOnR);
        $plywood->setEdgeSameOnL($edgeSameOnL);

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
        $plywood->setUnitMesureCostId($unitMesureCostId);

        $plywood->setRunning($running);
        $plywood->setRunningDescription($runningDescription);
        $plywood->setUnitMesureCostIdR($unitMesureCostIdR);

        $plywood->setIsLabels($isLabels);
        $plywood->setNumberLabels($numberLabels);
        $plywood->setLumberFee($lumberFee); 
        $plywood->setAutoNumber($autoNumber);
        //$plywood->setQuoteId('1');
        $plywood->setFileId(0);
        
        $plywood->setComments($comments);
        $plywood->setQuoteId($quoteId);
        $plywood->setCreatedAt($createdAt);
        $plywood->setUpdatedAt($createdAt);
        $plywood->setIsActive(1);
        // Calculator data
        $plywood->setCustMarkupPer(25);
        $plywood->setCalcTW(1);
        $plywood->setVenCost(0);
        $plywood->setVenWaste(1);
        $plywood->setSubTotalVen(0);
        $plywood->setCoreCost(0);
        $plywood->setCoreWaste(1);
        $plywood->setSubTotalCore(0);
        $plywood->setBackrCost(0);
        $plywood->setBackrWaste(1);
        $plywood->setSubTotalBackr(0);
        $plywood->setPanelCost(0);
        $plywood->setPanelWaste(1);
        $plywood->setSubTotalPanel(0);
        $plywood->setFinishCost(0);
        $plywood->setFinishWaste(1);
        $plywood->setSubTotalFinish(0);
        $plywood->setEdgeintCost(0);
        $plywood->setEdgeintWaste(1);
        $plywood->setSubTotalEdgeint(0);
        $plywood->setEdgevCost(0);
        $plywood->setEdgevWaste(1);
        $plywood->setSubTotalEdgev(0);
        $plywood->setFinishEdgeCost(0);
        $plywood->setFinishEdgeWaste(1);
        $plywood->setSubTotalFinishEdge(0);
        $plywood->setMillingCost(0);
        $plywood->setMillingWaste(1);
        $plywood->setSubTotalMilling(0);
        $plywood->setRunningCost(0);
        $plywood->setRunningWaste(1);
        $plywood->setSubTotalrunning(0);
        $plywood->setTotalcostPerPiece(0);
        $plywood->setMarkup(0);
        $plywood->setSellingPrice(0);
        $plywood->setLineitemTotal(0);
        $plywood->setMachineSetup(0);
        $plywood->setMachineTooling(0);
        $plywood->setPreFinishSetup(0);
        $plywood->setColorMatch(0);
        $plywood->setTotalCost(0);
        $em->persist($plywood);
        $em->flush();

        $lastInserted = $plywood->getId();
        
        $fileId_ar = explode(',', $fileId);
        //var_dump($fileId_ar);
        if(count($fileId_ar)>0 && !empty($fileId))
        {
            if($formtype == 'clone')
            {

                for($i=0;$i<count($fileId_ar);$i++)
                {
                    $em2 = $this->getDoctrine()->getManager();
                    $file =  $this->getDoctrine()->getRepository('AppBundle:Files')->find($fileId_ar[$i]);
                    //var_dump($file);
                    $fileEntity = new Files();
                    $fileEntity->setFileName($file->getFileName());
                    $fileEntity->setAttachableId($lastInserted);
                    $fileEntity->setAttachableType($file->getAttachableType());
                    $fileEntity->setOriginalName($file->getOriginalName());
                    $em->persist($fileEntity);
                    $em->flush();

                    /* $file->setAttachableId($lastInserted);
                    $em2->persist($file);
                    $em2->flush(); */
        
                }

            }
            else
            {
                for($i=0;$i<count($fileId_ar);$i++)
                {
                    $em2 = $this->getDoctrine()->getManager();
                    $file =  $this->getDoctrine()->getRepository('AppBundle:Files')->find($fileId_ar[$i]);
            
                    $file->setAttachableId($lastInserted);
                    $em2->persist($file);
                    $em2->flush();
        
                }
            }
        }

        return $lastInserted;
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
                            $arrApi['data']['type'] = 'plywood';
                            $arrApi['data']['speciesId'] = $plywood->getSpeciesId();
                            $arrApi['data']['grainPatternId'] = $plywood->getGrainPatternId();
                            $arrApi['data']['flakexFiguredId'] = $plywood->getFlakexFiguredId();
                            $arrApi['data']['patternId'] = $plywood->getPatternId();
                            $arrApi['data']['grainDirectionId'] = $plywood->getGrainDirectionId();
                            $arrApi['data']['patternMatch'] = $plywood->getPatternMatch();
                            $arrApi['data']['gradeId'] = $plywood->getGradeId();
                            $arrApi['data']['thicknessId'] = $plywood->getThicknessId();
                            $arrApi['data']['width'] = $plywood->getPlywoodWidth();
                            $arrApi['data']['widthFraction'] = $plywood->getWidthFraction();
                            $arrApi['data']['netsize'] = $plywood->isNetSize();
                            $arrApi['data']['length'] = $plywood->getPlywoodLength();
                            $arrApi['data']['lengthFraction'] = $plywood->getLengthFraction();

                            $arrApi['data']['finishThickId'] = $plywood->getFinishThickId();
                            $arrApi['data']['finishThicktype'] = $plywood->getFinishThickType();
                            $arrApi['data']['thickness'] = $plywood->getThickness();
                            $arrApi['data']['finish'] = $plywood->getFinish();
                            $arrApi['data']['facPaint'] = $plywood->getFacPaint();
                            $arrApi['data']['uvCuredId'] = $plywood->getUvCuredId();
                            $arrApi['data']['uvColorId'] = $plywood->getUvColorId();
                            $arrApi['data']['sheenId'] = $plywood->getSheenId();

                            $arrApi['data']['shameOnId'] = $plywood-> getShameOnId();

                            $arrApi['data']['coreSameOnbe'] = $plywood-> getCoreSameOnbe();
                            $arrApi['data']['coreSameOnte'] = $plywood-> getCoreSameonte();
                            $arrApi['data']['coreSameOnre'] = $plywood-> getCoreSameOnre();
                            $arrApi['data']['coreSameOnle'] = $plywood-> getCoreSameOnle();

                            $arrApi['data']['edgeDetail'] = $plywood->getEdgeDetail();
                            $arrApi['data']['edgeSameOnB'] = $plywood->getEdgeSameOnB();
                            $arrApi['data']['edgeSameOnR'] = $plywood->getEdgeSameOnR();
                            $arrApi['data']['edgeSameOnL'] = $plywood->getEdgeSameOnL();

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
                            $arrApi['data']['unitMesureCostId'] = $plywood->getUnitMesureCostId();

                            $arrApi['data']['running'] = $plywood->isRunning();
                            $arrApi['data']['runningDescription'] = $plywood->getRunningDescription();
                            $arrApi['data']['unitMesureCostIdR'] = $plywood->getUnitMesureCostIdR();

                            $arrApi['data']['isLabels'] = $plywood->getIsLabels();
                            $arrApi['data']['numberLabels'] = $plywood->getNumberLabels();
                            //echo $arrApi['data']['autoNumber'];
                            if (!empty($plywood->getAutoNumber())) {
                                $tags = explode(',', $plywood->getAutoNumber());
                               
                                for ($i=0; $i< count($tags); $i++) {
                                    $arrApi['data']['autoNumber'][$i]['autoNumber'] = $tags[$i]; 
                                }
                            } else {
                                $arrApi['data']['autoNumber'] = null;
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
                            $arrApi['data']['isactive'] = $plywood->getIsActive();

                            
                            /* if(!empty($plywood->getFileId()))
                            {
                                $arrApi['data']['fileLink'] = $this->getFileUrl( $plywood->getFileId(),$request );
                            } */

                            $allfiles = $this->getDoctrine()->getRepository("AppBundle:Files")->findBy(array('attachableid'=>$_DATA['id'],'attachabletype'=>'Plywood'));
                            //var_dump($allfiles);
                            $filestring = '';
                            for($i=0;$i<count($allfiles);$i++)
                            {
                                $ext = pathinfo($allfiles[$i]->getOriginalName(), PATHINFO_EXTENSION);
                                $filestring = $filestring.$allfiles[$i]->getId().',';
                                $arrApi['data']['files'][$i]['id'] = $allfiles[$i]->getId();
                                $arrApi['data']['files'][$i]['originalname'] = $allfiles[$i]->getOriginalName();
                                $arrApi['data']['files'][$i]['type'] = $ext;
                                $arrApi['data']['files'][$i]['fileLink'] = $this->getFileUrl( $allfiles[$i]->getId(),$request );
                                
                            }
                            $arrApi['data']['filestring'] = rtrim($filestring,',');
                            // Calculator data
                            $arrApi['data']['custMarkup'] = $plywood->getCustMarkupPer();
                            $arrApi['data']['venCost'] = $plywood->getVenCost();
                            $arrApi['data']['venWaste'] = $plywood->getVenWaste();
                            $arrApi['data']['subTotalVen'] = $plywood->getSubTotalVen();
                            $arrApi['data']['coreCost'] = $plywood->getCoreCost();
                            $arrApi['data']['coreWaste'] = $plywood->getCoreWaste();
                            $arrApi['data']['subTotalCore'] = $plywood->getSubTotalCore();
                            $arrApi['data']['backrCost'] = $plywood->getBackrCost();
                            $arrApi['data']['backrWaste'] = $plywood->getBackrWaste();
                            $arrApi['data']['subTotalBackr'] = $plywood->getSubTotalBackr();
                            $arrApi['data']['panelCost'] = $plywood->getPanelCost();
                            $arrApi['data']['panelWaste'] = $plywood->getPanelWaste();
                            $arrApi['data']['subTotPanel'] = $plywood->getSubTotalPanel();
                            $arrApi['data']['finishCost'] = $plywood->getFinishCost();
                            $arrApi['data']['finishWaste'] = $plywood->getFinishWaste();
                            $arrApi['data']['subTotalWaste'] = $plywood->getSubTotalFinish();
                            $arrApi['data']['edgeIntCost'] = $plywood->getEdgeintCost();
                            $arrApi['data']['edgeIntWaste'] = $plywood->getEdgeintWaste();
                            $arrApi['data']['subTotalEdgeint'] = $plywood->getSubTotalEdgeint();
                            $arrApi['data']['edgeVCost'] = $plywood->getEdgevCost();
                            $arrApi['data']['edgeVWaste'] = $plywood->getEdgevWaste();
                            $arrApi['data']['subTotalEdgev'] = $plywood->getSubTotalEdgev();
                            $arrApi['data']['finishEdgeCost'] = $plywood->getFinishEdgeCost();
                            $arrApi['data']['finishEdgeWaste'] = $plywood->getFinishEdgeWaste();
                            $arrApi['data']['subTotalFinishEdge'] = $plywood->getSubTotalFinishEdge();
                            $arrApi['data']['millingCost'] = $plywood->getMillingCost();
                            $arrApi['data']['millingWaste'] = $plywood->getMillingWaste();
                            $arrApi['data']['subTotalmilling'] = $plywood->getSubTotalMilling();
                            $arrApi['data']['rfCostP'] = $plywood->getRunningCost();
                            $arrApi['data']['rfWasteP'] = $plywood->getRunningWaste();
                            $arrApi['data']['subTotRfP'] = $plywood->getSubTotalrunning();
                            $arrApi['data']['totalCostPerPiece'] = $plywood->getTotalcostPerPiece();
                            $arrApi['data']['markup'] = $plywood->getMarkup();
                            $arrApi['data']['sellingPrice'] = $plywood->getSellingPrice();
                            $arrApi['data']['lineitemTotal'] = $plywood->getLineitemTotal();
                            $arrApi['data']['machineSetup'] = $plywood->getMachineSetup();
                            $arrApi['data']['machineTooling'] = $plywood->getMachineTooling();
                            $arrApi['data']['preFinishSetup'] = $plywood->getPreFinishSetup();
                            $arrApi['data']['colorMatch'] = $plywood->getColorMatch();
                            $arrApi['data']['totalCost'] = $plywood->getTotalCost();

                            
                            //$arrApi['data']['isactive'] = $veneer->getIsActive();
        
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
            $patternMatch = trim($getJson->get('patternMatch'));
            $gradeId = trim($getJson->get('facegrade'));
            $thicknessId = trim($getJson->get('thickness'));
            $plywoodWidth = trim($getJson->get('width'));
            $widthFraction = trim($getJson->get('widthFraction'));
            $netsize = trim($getJson->get('netsize'));
            $plywoodLength = trim($getJson->get('length'));
            $lengthFraction = trim($getJson->get('lengthFraction'));
            $finishThickId = trim($getJson->get('finishthick'));
            $finishThicktype = trim($getJson->get('finishThicktype'));
            $isSequenced = trim($getJson->get('sequenced'));
            $coreType = trim($getJson->get('coretype'));
            $thickness = trim($getJson->get('corethickness'));
            $finish = trim($getJson->get('finish'));
            $facPaint = trim($getJson->get('facPaint'));
            $uvCuredId = trim($getJson->get('uvcured'));
            $uvColorId = trim($getJson->get('uvcolor'));
            $sheenId = trim($getJson->get('sheen'));

            $shameOnId = trim($getJson->get('coresameon'));
            $coreSameOnbe = trim($getJson->get('coresameonbe'));
            $coreSameOnte = trim($getJson->get('coresameonte'));
            $coreSameOnre = trim($getJson->get('coresameonre'));
            $coreSameOnle = trim($getJson->get('coresameonle'));

            $backerId = trim($getJson->get('backergrade'));
            $edgeDetail = trim($getJson->get('egdedetail'));
            $edgeSameOnB = trim($getJson->get('edgeSameOnB'));
            $edgeSameOnR = trim($getJson->get('edgeSameOnR'));
            $edgeSameOnL = trim($getJson->get('edgeSameOnL'));

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
            $unitMesureCostId = trim($getJson->get('unitmeasurecost'));
            $running = trim($getJson->get('running'));
            $runningDescription = trim($getJson->get('runningDescription'));
            $unitmeasurecostR = trim($getJson->get('unitmeasurecostR'));
            $isLabels = trim($getJson->get('isLabels'));
            $numberLabels = trim($getJson->get('labels'));
//            print_r($numberLabels);die;
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
            empty($grainDirectionId) || empty($patternMatch) || empty($gradeId) ||  empty($thicknessId) || empty($plywoodWidth)
            || empty($plywoodLength) || empty($finishThickId) || empty($backerId)  || empty($coreType)
            || empty($thickness) || empty($finish) || empty($facPaint) || empty($uvCuredId) || empty($sheenId) || empty($topEdge) || empty($edgeMaterialId) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved plywood data.';
                $statusCode = 200;
                $this->editPlywoodData($id,$quantity, $speciesId, 
                $patternId, $grainDirectionId, $patternMatch, $gradeId, $thicknessId, $plywoodWidth,$widthFraction,$netsize,
                $plywoodLength,$lengthFraction,$finishThickId,$finishThicktype,$backerId,$isSequenced,$coreType, $thickness, $finish,$facPaint,$uvCuredId,$uvColorId, $sheenId,
                $shameOnId,$coreSameOnbe,$coreSameOnte,$coreSameOnre,$coreSameOnle,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
                $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
                $milling,$millingDescription,$unitMesureCostId,$running,$runningDescription,$unitmeasurecostR,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$fileId,$createdAt,$edgeSameOnB,$edgeSameOnR,$edgeSameOnL);
            }

        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("api/plywood/savePlywoodCalculatedPrice")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     */
    public function savePlywoodCalculatedPriceAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        if ( empty($data['plyId']) || $data['custMarkupPer'] == null || empty($data['venCost']) || empty($data['venWaste'])) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Please provide all the details.';
            $statusCode = 422;
        } else {
            try {
                $updatePlywood = $this->updatePlywoodCalculatedPrice($data);
                if ($updatePlywood) {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully saved calculated price.';
                }
            }
            catch(Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function updatePlywoodCalculatedPrice($data) {
        $em = $this->getDoctrine()->getManager();
        $plyData = $em->getRepository(Plywood::class)->find($data['plyId']);
        $plyData->setCustMarkupPer($data['custMarkupPer']);
        $plyData->setCalcTW($data['calcTW']);
        $plyData->setVenCost($data['venCost']);
        $plyData->setVenWaste($data['venWaste']);
        $plyData->setSubTotalVen($data['subTotVen']);
        $plyData->setCoreCost($data['corCost']);
        $plyData->setCoreWaste($data['corWaste']);
        $plyData->setSubTotalCore($data['subTotCor']);
        $plyData->setBackrCost($data['bakrCost']);
        $plyData->setBackrWaste($data['bakrWaste']);
        $plyData->setSubTotalBackr($data['subTotBackr']);
        $plyData->setFinishCost($data['finishCostPly']);
        $plyData->setFinishWaste($data['finishWastePly']);
        $plyData->setSubTotalFinish($data['subTotalFinish']);
        $plyData->setEdgeintCost($data['edgeIntCostPly']);
        $plyData->setEdgeintWaste($data['edgeIntWastePly']);
        $plyData->setSubTotalEdgeint($data['subTotalEdgeIntPly']);
        $plyData->setEdgevCost($data['edgeVCostPly']);
        $plyData->setEdgevWaste($data['edgeVWastePly']);
        $plyData->setSubTotalEdgev($data['subTotalEdgeIntV']);
        $plyData->setFinishEdgeCost($data['finishEdgeCostPly']);
        $plyData->setFinishEdgeWaste($data['finishEdgeWastePly']);
        $plyData->setSubTotalFinishEdge($data['subTotalFinishEdge']);
        $plyData->setMillingCost($data['millingCostPly']);
        $plyData->setMillingWaste($data['millingVWastePly']);
        $plyData->setSubTotalMilling($data['subTotalMilling']);
        $plyData->setRunningCost($data['rfCostP']);
        $plyData->setRunningWaste($data['rfWasteP']);
        $plyData->setSubTotalrunning($data['rfSubTotP']);
        $plyData->setTotalcostPerPiece($data['totalCostPerPiece']);
        $plyData->setMarkup($data['markup']);
        $plyData->setSellingPrice($data['sellingPrice']);
        $plyData->setLineitemTotal($data['lineitemTotal']);
        $plyData->setMachineSetup($data['machnStp']);
        $plyData->setMachineTooling($data['machnTlng']);
        $plyData->setPreFinishSetup($data['preFnshStp']);
        $plyData->setColorMatch($data['clrMatch']);
        $plyData->setTotalCost($data['totalCost']);
        $plyData->setPanelCost($data['panelCost']);
        $plyData->setPanelWaste($data['panelWaste']);
        $plyData->setSubTotalPanel($data['panelSubTotal']);
        $em->persist($plyData);
        $em->flush();
        return 1;
    }


    private function editPlywoodData($id,$quantity, $speciesId, 
    $patternId, $grainDirectionId, $patternMatch, $gradeId, $thicknessId, $plywoodWidth,$widthFraction,$netsize,
    $plywoodLength,$lengthFraction,$finishThickId,$finishThicktype,$backerId,$isSequenced,$coreType, $thickness, $finish,$facPaint,$uvCuredId,$uvColorId, $sheenId,
    $shameOnId,$coreSameOnbe,$coreSameOnte,$coreSameOnre,$coreSameOnle,$edgeDetail,$topEdge,$edgeMaterialId,$edgeFinishSpeciesId,$bottomEdge,$bedgeMaterialId,$bedgeFinishSpeciesId,$rightEdge,
    $redgeMaterialId,$redgeFinishSpeciesId,$leftEdge,$ledgeMaterialId,$ledgeFinishSpeciesId,
    $milling,$millingDescription,$unitMesureCostId,$running,$runningDescription,$unitmeasurecostR,$isLabels,$numberLabels,$lumberFee,$autoNumber,$comments,$fileId,$createdAt,$edgeSameOnB,$edgeSameOnR,$edgeSameOnL)
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
        $plywood->setPatternMatch($patternMatch);
        $plywood->setGradeId($gradeId);
        $plywood->setThicknessId($thicknessId);
        $plywood->setPlywoodWidth($plywoodWidth);
        $plywood->setWidthFraction($widthFraction);
        $plywood->setIsNetSize($netsize);
        $plywood->setLengthFraction($lengthFraction);
        $plywood->setPlywoodLength($plywoodLength);
        $plywood->setFinishThickId($finishThickId);
        $plywood->setFinishThickType($finishThicktype);
        $plywood->setBackerId($backerId);
        $plywood->setIsSequenced($isSequenced);
        $plywood->setCoreType($coreType);
        $plywood->setThickness($thickness);
        $plywood->setFinish($finish);
        $plywood->setFacPaint($facPaint);
        $plywood->setUvCuredId($uvCuredId);
        $plywood->setUvColorId($uvColorId);
        $plywood->setSheenId($sheenId);
        $plywood->setShameOnId($shameOnId);
        $plywood->setCoreSameOnbe($coreSameOnbe);
        $plywood->setCoreSameonte($coreSameOnte);
        $plywood->setCoreSameOnre($coreSameOnre);
        $plywood->setCoreSameOnle($coreSameOnle);
        $plywood->setEdgeDetail($edgeDetail);
        $plywood->setEdgeSameOnB($edgeSameOnB);
        $plywood->setEdgeSameOnR($edgeSameOnR);
        $plywood->setEdgeSameOnL($edgeSameOnL);
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
        $plywood->setUnitMesureCostId($unitMesureCostId);
        $plywood->setRunning($running);
        $plywood->setRunningDescription($runningDescription);
        $plywood->setUnitMesureCostIdR($unitmeasurecostR);
        $plywood->setIsLabels($isLabels);
        $plywood->setNumberLabels($numberLabels);
        $plywood->setLumberFee($lumberFee); 
        $plywood->setAutoNumber($autoNumber);
        //$plywood->setQuoteId('1');
        $plywood->setComments($comments);
        $plywood->setFileId(0);
       //$veneer->setQuoteId('1');
        //$plywood->setCreatedAt($createdAt);
        $plywood->setUpdatedAt($createdAt);

        $em->flush();

        $lastInserted = $id;
        //var_dump($lastInserted);
        //var_dump($fileId);
        if (!empty($fileId)) {
            $fileId_ar = explode(',', $fileId);
            //print_r($fileId_ar);
            for($i=0;$i<count($fileId_ar);$i++)
            {
                //var_dump($fileId_ar[$i]);
                $em2 = $this->getDoctrine()->getManager();
                $file =  $this->getDoctrine()->getRepository('AppBundle:Files')->find($fileId_ar[$i]);

                $file->setAttachableId($lastInserted);
                $em2->persist($file);
                $em2->flush();

            }
        }
    }

    function getFileUrl($fileId,$request)
    {
        
       /*  $files = $this->getDoctrine()->getRepository('AppBundle:Files')->findOneById($fileId);
         */
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
       
        return $baseurl.'/api/fileDownload/'.$fileId;

    }
    
    /**
     * @Route("api/plywood/getPlywoodDataByQuoteId")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     * 
     * Date: 20-02-2018
     * Author: Mohit Kumar
     */
    public function getPlywoodDataByQuoteId(Request $request){
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
//        $userId=!empty($data->get('user_id'))?trim($data->get('user_id')):'';
        $quoteId=!empty($data->get('quote_id'))?trim($data->get('quote_id')):'';
        if(!empty($quoteId)){
            $query = $this->getDoctrine()->getManager();
            $result = $query->createQueryBuilder()
                ->select(['v.quantity','v.plywoodWidth as width','v.plywoodLength as length','v.comments','v.speciesId','v.topEdge','v.bottomEdge',
                    'v.rightEdge','v.leftEdge'])
                ->from('AppBundle:Plywood', 'v')
                ->leftJoin('AppBundle:Quotes', 'q', 'WITH', 'v.quoteId = q.id')
                ->addSelect(['q.refNum','q.deliveryDate'])
                ->leftJoin('AppBundle:User', 'u', 'WITH', "q.customerId = u.id and u.userType='customer' and u.roleId=11")
                ->addSelect(['u.username'])
                ->leftJoin('AppBundle:Species', 's', 'WITH', "v.speciesId = s.id")
                ->addSelect(['s.name as SpecieName'])
                ->leftJoin('AppBundle:Pattern', 'p', 'WITH', "v.patternId = p.id")
                ->addSelect(['p.name as patternName'])
                ->leftJoin('AppBundle:Thickness', 't', 'WITH', "v.thicknessId = t.id")
                ->addSelect(['t.name as thicknessName'])
                ->leftJoin('AppBundle:FaceGrade', 'fg', 'WITH', "v.gradeId = fg.id")
                ->addSelect(['fg.name as faceGradeName'])
                ->leftJoin('AppBundle:Backer', 'b', 'WITH', "v.backerId = b.id")
                ->addSelect(['b.name as backerName'])
                ->leftJoin('AppBundle:GrainDirection', 'gd', 'WITH', "v.grainDirectionId = gd.id")
                ->addSelect(['gd.name as grainDirectionName'])
                ->leftJoin('AppBundle:Orders', 'o', 'WITH', "q.id = o.quoteId")
                ->addSelect(['o.orderDate as orderDate','o.estNumber as estNumber'])
                ->leftJoin('AppBundle:EdgeFinish', 'tef', 'WITH', "v.topEdge = tef.id")
                ->addSelect(['tef.name as topEdgeName'])
                ->leftJoin('AppBundle:EdgeFinish', 'bef', 'WITH', "v.bottomEdge = bef.id")
                ->addSelect(['bef.name as bottomEdgeName'])
                ->leftJoin('AppBundle:EdgeFinish', 'ref', 'WITH', "v.rightEdge = ref.id")
                ->addSelect(['ref.name as rightEdgeName'])
                ->leftJoin('AppBundle:EdgeFinish', 'lef', 'WITH', "v.leftEdge = lef.id")
                ->addSelect(['lef.name as leftEdgeName'])
                    
                ->leftJoin('AppBundle:SizeEdgeMaterial', 'tem', 'WITH', "v.edgeMaterialId = tem.id")
                ->addSelect(['tem.name as topEdgeMaterialName'])
                ->leftJoin('AppBundle:SizeEdgeMaterial', 'bem', 'WITH', "v.bedgeMaterialId = bem.id")
                ->addSelect(['bem.name as bottomEdgeMaterialName'])
                ->leftJoin('AppBundle:SizeEdgeMaterial', 'rem', 'WITH', "v.redgeMaterialId = rem.id")
                ->addSelect(['rem.name as rightEdgeMaterialName'])
                ->leftJoin('AppBundle:SizeEdgeMaterial', 'lem', 'WITH', "v.ledgeMaterialId = lem.id")
                ->addSelect(['lem.name as leftEdgeMaterialName'])
                    
                ->leftJoin('AppBundle:Species', 'ts', 'WITH', "v.edgeFinishSpeciesId = ts.id")
                ->addSelect(['ts.name as topSpeciesName'])
                ->leftJoin('AppBundle:Species', 'bs', 'WITH', "v.bedgeFinishSpeciesId = bs.id")
                ->addSelect(['bs.name as bottomSpeciesName'])
                ->leftJoin('AppBundle:Species', 'rs', 'WITH', "v.redgeFinishSpeciesId = rs.id")
                ->addSelect(['rs.name as rightSpeciesName'])
                ->leftJoin('AppBundle:Species', 'ls', 'WITH', "v.ledgeFinishSpeciesId = ls.id")
                ->addSelect(['ls.name as leftSpeciesName'])
                ->where('v.quoteId = '.$quoteId)
                ->getQuery()
                ->getResult();
//                ->getSQL();
            if (empty($result) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no quote.';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the quote list.';
                $arrApi['data']['plywood']=$result;
            }
        } else {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Quote Id is missing.';
            $statusCode = 422;
        }
        return new JsonResponse($arrApi, $statusCode);
    }

}