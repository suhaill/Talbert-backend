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
        $fileIds = $data->get('fileId');
        $qid = trim($data->get('qid'));
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
                    $this->updateFilesIdsInFilesTable($fileIds, $isDoorSaved);
                    $this->saveDoorSkinData($data);
                }
            }
        }
        return new JsonResponse($arrApi, $statusCode);
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
        $length = trim($data->get('length'));
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
        $door->setLength($length);
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
        $em->persist($door);
        $em->flush();
        return $door->getId();
    }

    private function saveDoorSkinData($data) {
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
}
