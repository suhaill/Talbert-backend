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
use AppBundle\Entity\Veneer;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
        try {

            $jsontoarraygenerator = new JsonToArrayGenerator();
            $getJson = $jsontoarraygenerator->getJson($request);

            $quantity = trim($getJson->get('quantity'));
            $speciesId = trim($getJson->get('species'));
            $grainPatternId = trim($getJson->get('grainpattern'));
            $flakexfigured = trim($getJson->get('flakexfigured'));
            $pattern = trim($getJson->get('pattern'));
            $grainDirectionId = trim($getJson->get('graindirection'));
            $gradeId = trim($getJson->get('facegrade'));
            $thicknessId = trim($getJson->get('thickness'));
            $width = trim($getJson->get('width'));
            $isNetSize = trim($getJson->get('netsize'));
            $length = trim($getJson->get('length'));
            $coreTypeId = trim($getJson->get('coretype'));
            $backer = trim($getJson->get('backer'));
            $isFlexSanded = trim($getJson->get('flexsanded'));
            $sequenced = trim($getJson->get('sequenced'));
            $lumberFee = trim($getJson->get('lumberfee'));
            $comments = trim($getJson->get('comment'));
            $fileId = trim($getJson->get('fileId'));
            //$quoteId = trim($getJson->get('quoteId'));
            $createdAt = new \DateTime('now');
            
            
            if (empty($quantity) || empty($speciesId) || empty($grainPatternId) 
            || empty($pattern) || empty($grainDirectionId) || empty($gradeId) || 
            empty($thicknessId) || empty($width) || empty($length) || empty($coreTypeId) 
            || empty($backer) || empty($lumberFee) || empty($comments )) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {

                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved veneer data.';
                $statusCode = 200;
                $this->saveVeneerData($quantity, $speciesId, $grainPatternId, $flakexfigured, 
                $pattern, $grainDirectionId, $gradeId, $thicknessId, $width, $isNetSize, 
                $length, $coreTypeId, $backer, $isFlexSanded, $sequenced, $lumberFee,
                $comments,$createdAt,$fileId);
            
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        return new JsonResponse($arrApi, $statusCode);
    }

    private function saveVeneerData($quantity, $speciesId, $grainPatternId, $flakexfigured,$pattern, $grainDirectionId, $gradeId, $thicknessId, $width, $isNetSize,$length, $coreTypeId, $backer, $isFlexSanded, $sequenced, $lumberFee,$comments,$createdAt,$fileId)
    {
        $em = $this->getDoctrine()->getManager();
        $veneer = new Veneer();
        $veneer->setQuantity($quantity);
        $veneer->setSpeciesId($speciesId);
        $veneer->setGrainPatternId($grainPatternId);
        //$veneer->setGrainPatternId($flakexfigured);
        $veneer->setPatternId($pattern);
        $veneer->setGrainDirectionId($grainDirectionId);
        $veneer->setGradeId($gradeId);
        $veneer->setThicknessId($thicknessId);
        $veneer->setWidth($width);
        $veneer->setIsNetSize($isNetSize);
        $veneer->setLength($length);
        $veneer->setCoreTypeId($coreTypeId);
        $veneer->setBacker($backer);
        $veneer->setIsFlexSanded($isFlexSanded);
        $veneer->setSequenced($sequenced);
        $veneer->setLumberFee($lumberFee);
        $veneer->setComments($comments);
        $veneer->setFileId($fileId);
        $veneer->setQuoteId('1');
        $veneer->setCreatedAt($createdAt);
        $veneer->setUpdatedAt($createdAt);

        $em->persist($veneer);
        $em->flush();
    }

}