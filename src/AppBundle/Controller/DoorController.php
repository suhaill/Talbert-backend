<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DoorCalculator;
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
use AppBundle\Entity\Quotes;
use AppBundle\Entity\Doors;
use AppBundle\Entity\Skins;
use AppBundle\Entity\Files;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\Filesystem\Filesystem;

class DoorController extends Controller
{

    /**
     * @Route("/api/doors/addDoor")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: Various
     */
    public function addDoorAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $addOrClone = $data->get('type');
        $fileIds = $data->get('fileId');
        $qid = trim($data->get('qid'));
        $doorId = trim($data->get('doorId'));
        $qty = trim($data->get('qty'));
        $width = trim($data->get('width'));
        $length = trim($data->get('length'));
        $thickness = trim($data->get('thickness'));
        $lumFee = trim($data->get('lumFee'));
        $createdAt = new \DateTime('now');
        if (empty($qid) || empty($qty) || empty($width) || empty($length) || empty($thickness) || empty($lumFee)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Please fill all the fields.';
            $statusCode = 422;
        } else {
            $quoteExists = $this->checkIfQuoteExists($qid);
            if (!$quoteExists) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This quote does not exists.';
                $statusCode = 422;
            } else {
                $isDoorSaved = $this->saveDoorData($data);
                if (empty($isDoorSaved)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Door can not be saved.';
                    $statusCode = 422;
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully saved door.';
                    if ($addOrClone == 'clone') {
                        $this->cloneFilesIdsInFilesTable($doorId, $isDoorSaved);
                        $this->addDefaultCalcData($isDoorSaved, $createdAt);
                    } else {
                        $arrApi['data']['lastInsertId'] = $isDoorSaved;
                        $this->updateFilesIdsInFilesTable($fileIds, $isDoorSaved);
                        $this->addDefaultCalcData($isDoorSaved, $createdAt);
                    }
                    $this->saveDoorSkinData($data,$isDoorSaved);
                }
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/doors/getDoorDetailsbyId")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     */
    public function getDoorDetailsAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $doorId = $request->query->get('id');
        if (!empty($doorId)) {
            $doorExists = $this->checkIfDoorExists($doorId);
            //print_r($doorExists);die;
            if (!empty($doorExists)) {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived door details';
                $arrApi['data']['id'] = $doorExists->getId();
                $arrApi['data']['quoteId'] = $doorExists->getQuoteId();
                $arrApi['data']['qty'] = $doorExists->getQty();
                $arrApi['data']['pair'] = $doorExists->getPair();
                $arrApi['data']['swing'] = $doorExists->getSwing();
                $arrApi['data']['width'] = $doorExists->getWidth();
                $arrApi['data']['widthFraction'] = $doorExists->getWidthFraction();
                $arrApi['data']['netsize'] = $doorExists->isNetSize();
                $arrApi['data']['length'] = $doorExists->getLength();
                $arrApi['data']['lengthFraction'] = $doorExists->getLengthFraction();
                $arrApi['data']['dthickness'] = $doorExists->getThickness();
                $arrApi['data']['doorUse'] = $doorExists->getDoorUse();
                $arrApi['data']['construction'] = $doorExists->getConstruction();
                $arrApi['data']['fireRating'] = $doorExists->getFireRating();
                $arrApi['data']['doorCore'] = $doorExists->getDoorCore();
                $arrApi['data']['sequence'] = $doorExists->getSequence();
                $arrApi['data']['sound'] = $doorExists->getSound();
                $arrApi['data']['soundDrop'] = $doorExists->getSoundDrop();
                $arrApi['data']['louvers'] = $doorExists->getLouvers();
                $arrApi['data']['louversDrop'] = $doorExists->getLouversDrop();
                $arrApi['data']['bevel'] = $doorExists->getBevel();
                $arrApi['data']['bevelDrop'] = $doorExists->getBevelDrop();
                $arrApi['data']['edgeFinish'] = $doorExists->getEdgeFinish();
                $arrApi['data']['tEdge'] = $doorExists->getTopEdge();
                $arrApi['data']['tEdgeMat'] = $doorExists->getTopEdgeMaterial();
                $arrApi['data']['tEdgeSp'] = $doorExists->getTopEdgeSpecies();
                $arrApi['data']['bEdge'] = $doorExists->getBottomEdge();
                $arrApi['data']['bEdgeMat'] = $doorExists->getBottomEdgeMaterial();
                $arrApi['data']['bEdgeSp'] = $doorExists->getBottomEdgeSpecies();
                $arrApi['data']['rEdge'] = $doorExists->getRightEdge();
                $arrApi['data']['lEdgeMat'] = $doorExists->getREdgeMat();
                $arrApi['data']['rEdgeSp'] = $doorExists->getEEdgeSp();
                $arrApi['data']['lEdge'] = $doorExists->getLeftEdge();
                $arrApi['data']['lEdgemat'] = $doorExists->getLEdgeMat();
                $arrApi['data']['lEdgeSp'] = $doorExists->getLEdgeSp();
                $arrApi['data']['lightOpening'] = $doorExists->getLightOpening();
                $arrApi['data']['lightOpDrop'] = $doorExists->getLightOpDrop();
                $arrApi['data']['locFromTop'] = $doorExists->getLocationFromTop();
                $arrApi['data']['locFromLockedge'] = $doorExists->getLocFromLockEdge();
                $arrApi['data']['openingSize'] = $doorExists->getOpeningSize();
                $arrApi['data']['stopSize'] = $doorExists->getStopSize();
                $arrApi['data']['glass'] = $doorExists->getGlass();
                $arrApi['data']['glassDrop'] = $doorExists->getGlassDrop();
                $arrApi['data']['finish'] = $doorExists->getFinish();
                $arrApi['data']['facPaint'] = $doorExists->getFacPaint();
                $arrApi['data']['uvCured'] = $doorExists->getUvCured();
                $arrApi['data']['dcolor'] = $doorExists->getColor();
                $arrApi['data']['sheen'] = $doorExists->getSheen();
                $arrApi['data']['sameOnBack'] = $doorExists->getSameOnBack();
                $arrApi['data']['sameOnBottom'] = $doorExists->getSameOnBottom();
                $arrApi['data']['sameOnTop'] = $doorExists->getSameOnTop();
                $arrApi['data']['sameOnRight'] = $doorExists->getSameOnRight();
                $arrApi['data']['sameOnLeft'] = $doorExists->getSameOnLeft();
                $arrApi['data']['doorframe'] = $doorExists->getDoorFrame();
                $arrApi['data']['doorDrop'] = $doorExists->getDoorDrop();
                $arrApi['data']['surfaceMachning'] = $doorExists->getSurfaceMachning();
                $arrApi['data']['surfaceStyles'] = $doorExists->getSurfaceStyle();
                $arrApi['data']['surfacedepth'] = $doorExists->getSurfaceDepth();
                $arrApi['data']['surfaceSides'] = $doorExists->getSurfaceSides();
                $arrApi['data']['styles'] = $doorExists->getStyles();
                $arrApi['data']['styleWidth'] = $doorExists->getStyleWidth();
                $arrApi['data']['machining'] = $doorExists->getMachning();
                $arrApi['data']['hindgeModelNo'] = $doorExists->getHindgeModelNo();
                $arrApi['data']['hindgeWeight'] = $doorExists->getHindgeWeight();
                $arrApi['data']['posFromTop'] = $doorExists->getPosFromTop();
                $arrApi['data']['hindgeSize'] = $doorExists->getHindgeSize();
                $arrApi['data']['backSet'] = $doorExists->getBackSet();
                $arrApi['data']['handleBolt'] = $doorExists->getHandleBolt();
                $arrApi['data']['posFromTopMach'] = $doorExists->getPosFromTopMach();
                $arrApi['data']['verticalRod'] = $doorExists->getVerticalRod();
                $arrApi['data']['isLabel'] = $doorExists->getIsLabel();
                $arrApi['data']['labels'] = $doorExists->getLabels();
                $arrApi['data']['facePreps'] = $doorExists->getFacePreps();
                $arrApi['data']['blockingCharge'] = $doorExists->getBlockingCharge();
                $arrApi['data']['blockingUpCharge'] = $doorExists->getBlockingUpcharge();
                $arrApi['data']['lumFee'] = $doorExists->getLumFee();
                $arrApi['data']['comment'] = $doorExists->getComment();
                $arrApi['data']['status'] = $doorExists->getStatus();
                $skinData = $this->getSkinDetailsbyDoorId($doorId);
                if (!empty($skinData)) {
                    $arrApi['data']['skinType'] = $skinData->getSkinType();
                    $arrApi['data']['species'] = $skinData->getSpecies();
                    $arrApi['data']['grain'] = $skinData->getGrain();
                    $arrApi['data']['grainDir'] = $skinData->getGrainDir();
                    $arrApi['data']['pattern'] = $skinData->getPattern();
                    $arrApi['data']['grade'] = $skinData->getGrade();
                    $arrApi['data']['leedReqs'] = $skinData->getLeedReqs();
                    $arrApi['data']['manufac'] = $skinData->getManufacturer();
                    $arrApi['data']['color'] = $skinData->getColor();
                    $arrApi['data']['edge'] = $skinData->getEdge();
                    $arrApi['data']['thickness'] = $skinData->getThickness();
                    $arrApi['data']['skinTypeBack'] = $skinData->getSkinTypeBack();
                    $arrApi['data']['backSpecies'] = $skinData->getBackSpecies();
                    $arrApi['data']['backGrain'] = $skinData->getBackGrain();
                    $arrApi['data']['backGrainDir'] = $skinData->getBackGrainDir();
                    $arrApi['data']['backPattern'] = $skinData->getBackPattern();
                    $arrApi['data']['backGrade'] = $skinData->getBackGrade();
                    $arrApi['data']['backLeedReqs'] = $skinData->getBackLeedReqs();
                    $arrApi['data']['backManufac'] = $skinData->getBackManufacturer();
                    $arrApi['data']['backColor'] = $skinData->getBackColor();
                    $arrApi['data']['backEdge'] = $skinData->getBackEdge();
                    $arrApi['data']['backThickness'] = $skinData->getBackThickness();
                }
                $allfiles = $this->getAttachmentsByDoorId($doorId);
                $filestring = '';
                for($i=0;$i<count($allfiles);$i++) {
                    $ext = pathinfo($allfiles[$i]->getOriginalName(), PATHINFO_EXTENSION);
                    $filestring = $filestring.$allfiles[$i]->getId().',';
                    $arrApi['data']['files'][$i]['id'] = $allfiles[$i]->getId();
                    $arrApi['data']['files'][$i]['originalname'] = $allfiles[$i]->getOriginalName();
                    $arrApi['data']['files'][$i]['type'] = $ext;
                    $arrApi['data']['files'][$i]['fileLink'] = $this->getFileUrl( $allfiles[$i]->getId(),$request );
                }
                $arrApi['data']['filestring'] = rtrim($filestring,',');
                $arrApi['data']['calc'] = $this->getCalcDataByDoorId($doorId);
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/doors/updateDoor")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: Various
     */
    public function updateDoorAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $fileIds = $data->get('fileId');
        $qid = trim($data->get('qid'));
        $doorId = trim($data->get('doorId'));
        $qty = trim($data->get('qty'));
        $width = trim($data->get('width'));
        $length = trim($data->get('length'));
        $thickness = trim($data->get('thickness'));
        $lumFee = trim($data->get('lumFee'));
        if (empty($qid) || empty($qty) || empty($width) || empty($length) || empty($thickness) || empty($lumFee)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Please fill all the fields.';
            $statusCode = 422;
        } else {
            $doorExists = $this->checkIfDoorExists($doorId);
            if (!$doorExists) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This quote does not exists.';
                $statusCode = 422;
            } else {
                $isDoorUpdated = $this->updateDoorData($data);
                if (!$isDoorUpdated) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Door can not be saved.';
                    $statusCode = 422;
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully updated door.';
                    $this->updateFilesIdsInFilesTable($fileIds, $doorId);
                    $this->updateDoorSkinData($data);
                }
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/doors/saveDoorCalcData")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     */
    public function saveDoorCalcDataAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        if (empty($data['doorId']) || empty($data['qty']) || empty($data['width']) || empty($data['length']) || empty($data['venCost']) || empty($data['venWaste'])) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing';
            $statusCode = 422;
        } else {
            $isUpdated = $this->updateDoorCalculatorData($data);
            if ($isUpdated) {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Success';
            } else {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Error saving data';
                $statusCode = 422;
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function updateDoorCalculatorData($data) {
        $em   = $this->getDoctrine()->getManager();
        $door =  $this->getDoctrine()->getRepository('AppBundle:DoorCalculator')->findOneBy(array('doorId'=> $data['doorId']));
        $door->setCustMarkupPer($data['custMarkupPer']);
        $door->setVenCost($data['venCost']);
        $door->setVenWaste($data['venWaste']);
        $door->setSubTotalVen($data['subTotVen']);
        $door->setCoreCost($data['corCost']);
        $door->setCoreWaste($data['corWaste']);
        $door->setSubTotalCore($data['subTotCor']);
        $door->setBackrCost($data['bakrCost']);
        $door->setBackrWaste($data['bakrWaste']);
        $door->setSubTotalBackr($data['subTotBackr']);
        $door->setFinishCost($data['finishCostPly']);
        $door->setFinishWaste($data['finishWastePly']);
        $door->setSubTotalFinish($data['subTotalFinish']);
        $door->setEdgeintCost($data['edgeIntCostPly']);
        $door->setEdgeintWaste($data['edgeIntWastePly']);
        $door->setSubTotalEdgeint($data['subTotalEdgeIntPly']);
        $door->setEdgevCost($data['edgeVCostPly']);
        $door->setEdgevWaste($data['edgeVWastePly']);
        $door->setSubTotalEdgev($data['subTotalEdgeIntV']);
        $door->setFinishEdgeCost($data['finishEdgeCostPly']);
        $door->setFinishEdgeWaste($data['finishEdgeWastePly']);
        $door->setSubTotalFinishEdge($data['subTotalFinishEdge']);
//        $door->setMillingCost($data['']);
//        $door->setMillingWaste($data['']);
//        $door->setSubTotalMilling($data['']);
        $door->setRunningCost($data['rfCostP']);
        $door->setRunningWaste($data['rfWasteP']);
        $door->setSubTotalrunning($data['rfSubTotP']);
        $door->setFlatPrice($data['flatPriceCost']);
        $door->setDoorFrame($data['doorFrameCost']);
        $door->setLouvers($data['louversCost']);
        $door->setLightOpening($data['lightOpeningCost']);
        $door->setSurfaceMachining($data['surfaceMachiningCost']);
        $door->setStyles($data['stylesCost']);
        $door->setMachining($data['machiningCost']);
        $door->setFacePreps($data['facePrepsCost']);
        $door->setGlass($data['glassCost']);
        $door->setBlocking($data['blockingCost']);
        $door->setTotalcostPerPiece($data['totalCostPerPiece']);
        $door->setMarkup($data['markup']);
        $door->setSellingPrice($data['sellingPrice']);
        $door->setLineitemTotal($data['lineitemTotal']);
        $door->setMachineSetup($data['machnStp']);
        $door->setMachineTooling($data['machnTlng']);
        $door->setPreFinishSetup($data['preFnshStp']);
        $door->setColorMatch($data['clrMatch']);
        $door->setTotalCost($data['totalCost']);
        $em->persist($door);
        $em->flush();
        return true;
    }

    private function updateFilesIdsInFilesTable($fileId_ar, $doorId) {
        if (!empty($fileId_ar)) {
            $fileId_ar = explode(',', $fileId_ar);
            for($i=0;$i<count($fileId_ar);$i++) {
                $em2 = $this->getDoctrine()->getManager();
                $file =  $this->getDoctrine()->getRepository('AppBundle:Files')->find($fileId_ar[$i]);
                $file->setAttachableId($doorId);
                $em2->persist($file);
                $em2->flush();
            }
        }
    }

    private function cloneFilesIdsInFilesTable($doorId, $newdoorId) {
        $files = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('attachabletype' => 'door', 'attachableid' => $doorId));
        $datime = new \DateTime('now');
        if (!empty($files)) {
            $em = $this->getDoctrine()->getManager();
            for ($i=0; $i< count($files); $i++) {
                $filesObj = new Files();
                $filesObj->setFileName($files[$i]->getFileName());
                $filesObj->setOriginalName($files[$i]->getOriginalName());
                $filesObj->setAttachableType($files[$i]->getAttachableType());
                $filesObj->setAttachableId($newdoorId);
                $filesObj->setCreatedAt($datime);
                $filesObj->setUpdatedAt($datime);
                $em->persist($filesObj);
                $em->flush();
            }
        }
    }

    private function checkIfQuoteExists($qid) {
        $quote = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findById($qid);
        if (empty($quote)) {
            return false;
        } else {
            return true;
        }
    }

    private function saveDoorData($data) {
        $qid = trim($data->get('qid'));
        $qty = trim($data->get('qty'));
        $pair = trim($data->get('pair'));
        $swing = trim($data->get('swing'));
        $width = trim($data->get('width'));
        $widthFraction = trim($data->get('widthFraction'));
        $netsize = trim($data->get('netsize'));
        $length = trim($data->get('length'));
        $lengthFraction = trim($data->get('lengthFraction'));
        $thickness = trim($data->get('thickness'));
        $use = trim($data->get('use'));
        $construction = trim($data->get('construction'));
        $fireRating = trim($data->get('fireRating'));
        $doorCore = trim($data->get('doorCore'));
        $sequesnce = trim($data->get('sequesnce'));
        $sound = trim($data->get('sound'));
        $soundDrop = trim($data->get('soundDrop'));
        $louvers = trim($data->get('louvers'));
        $louversDrop = trim($data->get('louversDrop'));
        $bevel = trim($data->get('bevel'));
        $bevelDrop = trim($data->get('bevelDrop'));
        $edgeFinish = trim($data->get('edgeFinish'));
        $topEdge = trim($data->get('topEdge'));
        $topEdgeMaterial = trim($data->get('topEdgeMaterial'));
        $topEdgeSpecies = trim($data->get('topEdgeSpecies'));
        $bottomEdge = trim($data->get('bottomEdge'));
        $bottomEdgeMaterial = trim($data->get('bottomEdgeMaterial'));
        $bottomEdgeSpecies = trim($data->get('bottomEdgeSpecies'));
        $rightEdge = trim($data->get('rightEdge'));
        $rightEdgeMaterial = trim($data->get('rightEdgeMaterial'));
        $rightEdheSpecies = trim($data->get('rightEdheSpecies'));
        $leftEdge = trim($data->get('leftEdge'));
        $leftEdgeMaterial = trim($data->get('leftEdgeMaterial'));
        $leftEdgeSpecies = trim($data->get('leftEdgeSpecies'));
        $lightOpening = trim($data->get('lightOpening'));
        $lightOpeningDrop = trim($data->get('lightOpeningDrop'));
        $locationFromTop = trim($data->get('locationFromTop'));
        $locationFromLockEdge = trim($data->get('locationFromLockEdge'));
        $openingSize = trim($data->get('openingSize'));
        $stopSize = trim($data->get('stopSize'));
        $glass = trim($data->get('glass'));
        $glassDrop = trim($data->get('glassDrop'));
        $finishCheck = trim($data->get('finishCheck'));
        $facPaint = trim($data->get('facPaint'));
        $uvcured = trim($data->get('uvcured'));
        $uvcolor = trim($data->get('uvcolor'));
        $sheen = trim($data->get('sheen'));
        $sameOnBack = trim($data->get('sameOnBack'));
        $sameOnBottom = trim($data->get('sameOnBottom'));
        $sameOnTop = trim($data->get('sameOnTop'));
        $sameOnRight = trim($data->get('sameOnRight'));
        $sameOnLeft = trim($data->get('sameOnLeft'));
        $doorFrame = trim($data->get('doorFrame'));
        $doorDrop = trim($data->get('doorDrop'));
        $surfaceMachining = trim($data->get('surfaceMachining'));
        $surfaceStyle = trim($data->get('surfaceStyle'));
        $surfaceDepth = trim($data->get('surfaceDepth'));
        $surfaceSides = trim($data->get('surfaceSides'));
        $styles = trim($data->get('styles'));
        $styleWidth = trim($data->get('styleWidth'));
        $machining = trim($data->get('machining'));
        $hindgeModelNumber = trim($data->get('hindgeModelNumber'));
        $hingeWeight = trim($data->get('hingeWeight'));
        $positionFromTop = trim($data->get('positionFromTop'));
        $hingeSize = trim($data->get('hingeSize'));
        $backSet = trim($data->get('backSet'));
        $handleBolt = trim($data->get('handleBolt'));
        $positionFromTopMach = trim($data->get('positionFromTopMach'));
        $verticalRod = trim($data->get('verticalRod'));
        $labelsYesNo = trim($data->get('labelsYesNo'));
        $labels = trim($data->get('labels'));
        $facePreps = trim($data->get('facePreps'));
        $blockingCharge = trim($data->get('blockingCharge'));
        $blockingUpcharge = trim($data->get('blockingUpcharge'));
        $lumFee = trim($data->get('lumFee'));
        $comment = trim($data->get('comment'));
        $datime = new \DateTime('now');
        //Save data
        $em = $this->getDoctrine()->getManager();
        $door = new Doors();
        $door->setQuoteId($qid);
        $door->setQty($qty);
        $door->setPair($pair);
        $door->setSwing($swing);
        $door->setWidth($width);
        $door->setWidthFraction($widthFraction);
        $door->setIsNetSize($netsize);
        $door->setLength($length);
        $door->setLengthFraction($lengthFraction);
        $door->setThickness($thickness);
        $door->setDoorUse($use);
        $door->setConstruction($construction);
        $door->setFireRating($fireRating);
        $door->setDoorCore($doorCore);
        $door->setSequence($sequesnce);
        $door->setSound($sound);
        $door->setSoundDrop($soundDrop);
        $door->setLouvers($louvers);
        $door->setLouversDrop($louversDrop);
        $door->setBevel($bevel);
        $door->setBevelDrop($bevelDrop);
        $door->setEdgeFinish($edgeFinish);
        $door->setTopEdge($topEdge);
        $door->setTopEdgeMaterial($topEdgeMaterial);
        $door->setTopEdgeSpecies($topEdgeSpecies);
        $door->setBottomEdge($bottomEdge);
        $door->setBottomEdgeMaterial($bottomEdgeMaterial);
        $door->setBottomEdgeSpecies($bottomEdgeSpecies);
        $door->setRightEdge($rightEdge);
        $door->setREdgeMat($rightEdgeMaterial);
        $door->setEEdgeSp($rightEdheSpecies);
        $door->setLeftEdge($leftEdge);
        $door->setLEdgeMat($leftEdgeMaterial);
        $door->setLEdgeSp($leftEdgeSpecies);
        $door->setLightOpening($lightOpening);
        $door->setLightOpDrop($lightOpeningDrop);
        $door->setLocationFromTop($locationFromTop);
        $door->setLocFromLockEdge($locationFromLockEdge);
        $door->setOpeningSize($openingSize);
        $door->setStopSize($stopSize);
        $door->setGlass($glass);
        $door->setGlassDrop($glassDrop);
        $door->setFinish($finishCheck);
        $door->setFacPaint($facPaint);
        $door->setUvCured($uvcured);
        $door->setColor($uvcolor);
        $door->setSheen($sheen);
        $door->setSameOnBack($sameOnBack);
        $door->setSameOnBottom($sameOnBottom);
        $door->setSameOnTop($sameOnTop);
        $door->setSameOnRight($sameOnRight);
        $door->setSameOnLeft($sameOnLeft);
        $door->setDoorFrame($doorFrame);
        $door->setDoorDrop($doorDrop);
        $door->setSurfaceMachning($surfaceMachining);
        $door->setSurfaceStyle($surfaceStyle);
        $door->setSurfaceDepth($surfaceDepth);
        $door->setSurfaceSides($surfaceSides);
        $door->setStyles($styles);
        $door->setStyleWidth($styleWidth);
        $door->setMachning($machining);
        $door->setHindgeModelNo($hindgeModelNumber);
        $door->setHindgeWeight($hingeWeight);
        $door->setPosFromTop($positionFromTop);
        $door->setHindgeSize($hingeSize);
        $door->setBackSet($backSet);
        $door->setHandleBolt($handleBolt);
        $door->setPosFromTopMach($positionFromTopMach);
        $door->setVerticalRod($verticalRod);
        $door->setIsLabel($labelsYesNo);
        $door->setLabels($labels);
        $door->setFacePreps($facePreps);
        $door->setBlockingCharge($blockingCharge);
        $door->setBlockingUpcharge($blockingUpcharge);
        $door->setLumFee($lumFee);
        $door->setComment($comment);
        $door->setCreatedAt($datime);
        $door->setUpdatedAt($datime);
        $door->setStatus(1);
        $em->persist($door);
        $em->flush();
        return $door->getId();
    }

    private function saveDoorSkinData($data, $doorId) {
        $qid = trim($data->get('qid'));
        $skinType = trim($data->get('skinType'));
        $species = trim($data->get('species'));
        $grainPattern = trim($data->get('grainPattern'));
        $grainDirection = trim($data->get('grainDirection'));
        $pattern = trim($data->get('pattern'));
        $grade = trim($data->get('grade'));
        $leedRegs = trim($data->get('leedRegs'));
        $manufacturer = trim($data->get('manufacturer'));
        $color = trim($data->get('color'));
        $edge = trim($data->get('edge'));
        $skinFrontthickness = trim($data->get('skinFrontthickness'));
        $skinTypeBack = trim($data->get('skinTypeBack'));
        $backSpecies = trim($data->get('backSpecies'));
        $backGrainPattern = trim($data->get('backGrainPattern'));
        $backGrainDirection = trim($data->get('backGrainDirection'));
        $backPattern = trim($data->get('backPattern'));
        $backGrade = trim($data->get('backGrade'));
        $backLeedRegs = trim($data->get('backLeedRegs'));
        $backManufacturer = trim($data->get('backManufacturer'));
        $backColor = trim($data->get('backColor'));
        $backEdge = trim($data->get('backEdge'));
        $skinBackthickness = trim($data->get('skinBackthickness'));
        // Save skin data
        $em = $this->getDoctrine()->getManager();
        $skin = new Skins();
        $skin->setQuoteId($qid);
        $skin->setDoorId($doorId);
        $skin->setSkinType($skinType);
        $skin->setSpecies($species);
        $skin->setGrain($grainPattern);
        $skin->setGrainDir($grainDirection);
        $skin->setPattern($pattern);
        $skin->setGrade($grade);
        $skin->setLeedReqs($leedRegs);
        $skin->setManufacturer($manufacturer);
        $skin->setColor($color);
        $skin->setEdge($edge);
        $skin->setThickness($skinFrontthickness);
        $skin->setSkinTypeBack($skinTypeBack);
        $skin->setBackSpecies($backSpecies);
        $skin->setBackGrain($backGrainPattern);
        $skin->setBackGrainDir($backGrainDirection);
        $skin->setBackPattern($backPattern);
        $skin->setBackGrade($backGrade);
        $skin->setBackLeedReqs($backLeedRegs);
        $skin->setBackManufacturer($backManufacturer);
        $skin->setBackColor($backColor);
        $skin->setBackEdge($backEdge);
        $skin->setBackThickness($skinBackthickness);
        $em->persist($skin);
        $em->flush();
        return $skin->getId();
    }

    private function updateDoorData($data) {
        $em = $this->getDoctrine()->getManager();
        $door = $em->getRepository(Doors::class)->find($data->get('doorId'));
        $datime = new \DateTime('now');
        $door->setQty($data->get('qty'));
        $door->setPair($data->get('pair'));
        $door->setSwing($data->get('swing'));
        $door->setWidth($data->get('width'));
        $door->setWidthFraction($data->get('widthFraction'));
        $door->setIsNetSize($data->get('netsize'));
        $door->setLength($data->get('length'));
        $door->setLengthFraction($data->get('lengthFraction'));
        $door->setThickness($data->get('thickness'));
        $door->setDoorUse($data->get('use'));
        $door->setConstruction($data->get('construction'));
        $door->setFireRating($data->get('fireRating'));
        $door->setDoorCore($data->get('doorCore'));
        $door->setSequence($data->get('sequesnce'));
        $door->setSound($data->get('sound'));
        $door->setSoundDrop($data->get('soundDrop'));
        $door->setLouvers($data->get('louvers'));
        $door->setLouversDrop($data->get('louversDrop'));
        $door->setBevel($data->get('bevel'));
        $door->setBevelDrop($data->get('bevelDrop'));
        $door->setEdgeFinish($data->get('edgeFinish'));
        $door->setTopEdge($data->get('topEdge'));
        $door->setTopEdgeMaterial($data->get('topEdgeMaterial'));
        $door->setTopEdgeSpecies($data->get('topEdgeSpecies'));
        $door->setBottomEdge($data->get('bottomEdge'));
        $door->setBottomEdgeMaterial($data->get('bottomEdgeMaterial'));
        $door->setBottomEdgeSpecies($data->get('bottomEdgeSpecies'));
        $door->setRightEdge($data->get('rightEdge'));
        $door->setREdgeMat($data->get('rightEdgeMaterial'));
        $door->setEEdgeSp($data->get('rightEdheSpecies'));
        $door->setLeftEdge($data->get('leftEdge'));
        $door->setLEdgeMat($data->get('leftEdgeMaterial'));
        $door->setLEdgeSp($data->get('leftEdgeSpecies'));
        $door->setLightOpening($data->get('lightOpening'));
        $door->setLightOpDrop($data->get('lightOpeningDrop'));
        $door->setLocationFromTop($data->get('locationFromTop'));
        $door->setLocFromLockEdge($data->get('locationFromLockEdge'));
        $door->setOpeningSize($data->get('openingSize'));
        $door->setStopSize($data->get('stopSize'));
        $door->setGlass($data->get('glass'));
        $door->setGlassDrop($data->get('glassDrop'));
        $door->setFinish($data->get('finishCheck'));
        $door->setFacPaint($data->get('facPaint'));
        $door->setUvCured($data->get('uvcured'));
        $door->setColor($data->get('uvcolor'));
        $door->setSheen($data->get('sheen'));
        $door->setSameOnBack($data->get('sameOnBack'));
        $door->setSameOnBottom($data->get('sameOnBottom'));
        $door->setSameOnTop($data->get('sameOnTop'));
        $door->setSameOnRight($data->get('sameOnRight'));
        $door->setSameOnLeft($data->get('sameOnLeft'));
        $door->setDoorFrame($data->get('doorFrame'));
        $door->setDoorDrop($data->get('doorDrop'));
        $door->setSurfaceMachning($data->get('surfaceMachining'));
        $door->setSurfaceStyle($data->get('surfaceStyle'));
        $door->setSurfaceDepth($data->get('surfaceDepth'));
        $door->setSurfaceSides($data->get('surfaceSides'));
        $door->setStyles($data->get('styles'));
        $door->setStyleWidth($data->get('styleWidth'));
        $door->setMachning($data->get('machining'));
        $door->setHindgeModelNo($data->get('hindgeModelNumber'));
        $door->setHindgeWeight($data->get('hingeWeight'));
        $door->setPosFromTop($data->get('positionFromTop'));
        $door->setHindgeSize($data->get('hingeSize'));
        $door->setBackSet($data->get('backSet'));
        $door->setHandleBolt($data->get('handleBolt'));
        $door->setPosFromTopMach($data->get('positionFromTopMach'));
        $door->setVerticalRod($data->get('verticalRod'));
        $door->setIsLabel($data->get('labelsYesNo'));
        $door->setLabels($data->get('labels'));
        $door->setFacePreps($data->get('facePreps'));
        $door->setBlockingCharge($data->get('blockingCharge'));
        $door->setBlockingUpcharge($data->get('blockingUpcharge'));
        $door->setLumFee($data->get('lumFee'));
        $door->setComment($data->get('comment'));
        $door->setUpdatedAt($datime);
        $em->persist($door);
        $em->flush();
        return true;
    }

    private function updateDoorSkinData($data) {
        $skinId = $this->getSkinIdbyDoorId($data->get('doorId'));
        $em = $this->getDoctrine()->getManager();
        $skin = $em->getRepository(Skins::class)->find($skinId);
        $skin->setSkinType($data->get('skinType'));
        $skin->setSpecies($data->get('species'));
        $skin->setGrain($data->get('grainPattern'));
        $skin->setGrainDir($data->get('grainDirection'));
        $skin->setPattern($data->get('pattern'));
        $skin->setGrade($data->get('grade'));
        $skin->setLeedReqs($data->get('leedRegs'));
        $skin->setManufacturer($data->get('manufacturer'));
        $skin->setColor($data->get('color'));
        $skin->setEdge($data->get('edge'));
        $skin->setThickness($data->get('skinFrontthickness'));
        $skin->setSkinTypeBack($data->get('skinTypeBack'));
        $skin->setBackSpecies($data->get('backSpecies'));
        $skin->setBackGrain($data->get('backGrainPattern'));
        $skin->setBackGrainDir($data->get('backGrainDirection'));
        $skin->setBackPattern($data->get('backPattern'));
        $skin->setBackGrade($data->get('backGrade'));
        $skin->setBackLeedReqs($data->get('backLeedRegs'));
        $skin->setBackManufacturer($data->get('backManufacturer'));
        $skin->setBackColor($data->get('backColor'));
        $skin->setBackEdge($data->get('backEdge'));
        $skin->setBackThickness($data->get('skinBackthickness'));
        $em->persist($skin);
        $em->flush();
    }

    private function getSkinIdbyDoorId($doorId) {
        $skinData = $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(array('doorId'=> $doorId));
        return $skinData->getId();
    }

    private function getFileUrl($fileId,$request) {
        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        return $baseurl.'/api/fileDownload/'.$fileId;
    }

    private function getAttachmentsByDoorId($doorId) {
        return $this->getDoctrine()->getRepository("AppBundle:Files")->findBy(array('attachableid'=> $doorId,'attachabletype'=>'door'));
    }

    private function getSkinDetailsbyDoorId($doorId) {
        return $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(array('doorId'=> $doorId));
    }

    private function checkIfDoorExists($doorId) {
        return $this->getDoctrine()->getRepository('AppBundle:Doors')->findOneById($doorId);
    }

    private function getCalcDataByDoorId($doorId) {
        $data = array();
        $em   = $this->getDoctrine()->getManager();
        $door =  $this->getDoctrine()->getRepository('AppBundle:DoorCalculator')->findOneBy(array('doorId'=> $doorId));
        $data['custMarkup'] = $door->getCustMarkupPer();
        $data['venCost'] = $door->getVenCost();
        $data['venWaste'] = $door->getVenWaste();
        $data['subTotVen'] = $door->getSubTotalVen();
        $data['coreCost'] = $door->getCoreCost();
        $data['coreWaste'] = $door->getCoreWaste();
        $data['subTotCore'] = $door->getSubTotalCore();
        $data['backrCost'] = $door->getBackrCost();
        $data['backrWaste'] = $door->getBackrWaste();
        $data['subTotBackr'] = $door->getSubTotalBackr();
        $data['finishCost'] = $door->getFinishCost();
        $data['finishWaste'] = $door->getFinishWaste();
        $data['subTotFinish'] = $door->getSubTotalFinish();
        $data['edgeIntCost'] = $door->getEdgeintCost();
        $data['edgeIntWaste'] = $door->getEdgeintWaste();
        $data['SubTotEdgeInt'] = $door->getSubTotalEdgeint();
        $data['edgevCost'] = $door->getEdgevCost();
        $data['edgevWaste'] = $door->getEdgevWaste();
        $data['subTotEdgeV'] = $door->getSubTotalEdgev();
        $data['finishEdgeCost'] = $door->getFinishEdgeCost();
        $data['finishEdgeWaste'] = $door->getFinishEdgeWaste();
        $data['subTotFinishEdge'] = $door->getSubTotalFinishEdge();
        $data['runningCost'] = $door->getRunningCost();
        $data['runningWaste'] = $door->getRunningWaste();
        $data['SubTotRunn'] = $door->getSubTotalrunning();
        $data['flatPrice'] = $door->getFlatPrice();
        $data['doorFrame'] = $door->getDoorFrame();
        $data['louvers'] = $door->getLouvers();
        $data['lightOpen'] = $door->getLightOpening();
        $data['surMach'] = $door->getSurfaceMachining();
        $data['styles'] = $door->getStyles();
        $data['machining'] = $door->getMachining();
        $data['facePreps'] = $door->getFacePreps();
        $data['glass'] = $door->getGlass();
        $data['blocking'] = $door->getBlocking();
        $data['totCostPerPiece'] = $door->getTotalcostPerPiece();
        $data['markup'] = $door->getMarkup();
        $data['sellingPrice'] = $door->getSellingPrice();
        $data['lineItemTot'] = $door->getLineitemTotal();
        $data['machineSetup'] = $door->getMachineSetup();
        $data['machineTooling'] = $door->getMachineTooling();
        $data['preFinishSetup'] = $door->getPreFinishSetup();
        $data['colorMatch'] = $door->getColorMatch();
        $data['totCost'] = $door->getTotalCost();
        return $data;
    }

    private function addDefaultCalcData($doorId, $createdAt) {
        $em = $this->getDoctrine()->getManager();
        $door = new DoorCalculator();
        $door->setDoorId($doorId);
        $door->setCustMarkupPer(25);
        $door->setVenCost(0.00);
        $door->setVenWaste(1);
        $door->setSubTotalVen(0.00);
        $door->setCoreCost(0.00);
        $door->setCoreWaste(1);
        $door->setSubTotalCore(0.00);
        $door->setBackrCost(0.00);
        $door->setBackrWaste(1);
        $door->setSubTotalBackr(0.00);
        $door->setFinishCost(0.00);
        $door->setFinishWaste(1);
        $door->setSubTotalFinish(0.00);
        $door->setEdgeintCost(0.00);
        $door->setEdgeintWaste(1);
        $door->setSubTotalEdgeint(0.00);
        $door->setEdgevCost(0.00);
        $door->setEdgevWaste(1);
        $door->setSubTotalEdgev(0.00);
        $door->setFinishEdgeCost(0.00);
        $door->setFinishEdgeWaste(1);
        $door->setSubTotalFinishEdge(0.00);
        $door->setRunningCost(0.00);
        $door->setRunningWaste(1);
        $door->setSubTotalrunning(0.00);
        $door->setFlatPrice(0.00);
        $door->setDoorFrame(0.00);
        $door->setLouvers(0.00);
        $door->setLightOpening(0.00);
        $door->setSurfaceMachining(0.00);
        $door->setStyles(0.00);
        $door->setMachining(0.00);
        $door->setFacePreps(0.00);
        $door->setGlass(0.00);
        $door->setBlocking(0.00);
        $door->setTotalcostPerPiece(0.00);
        $door->setMarkup(0.00);
        $door->setSellingPrice(0.00);
        $door->setLineitemTotal(0.00);
        $door->setMachineSetup(0.00);
        $door->setMachineTooling(0.00);
        $door->setPreFinishSetup(0.00);
        $door->setColorMatch(0.00);
        $door->setTotalCost(0.00);
        $door->setCreatedAt($createdAt);
        $door->setUpdatedAt($createdAt);
        $em->persist($door);
        $em->flush();
    }
}
