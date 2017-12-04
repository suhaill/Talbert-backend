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
use AppBundle\Entity\Plywood;
use AppBundle\Entity\User;
use AppBundle\Entity\Files;
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
            //$grainPatternId = trim($getJson->get('grainpattern'));
            //$flakexfigured = trim($getJson->get('flakexfigured'));
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
            $quoteId = trim($getJson->get('quoteId'));
            $createdAt = new \DateTime('now');
            $formtype = trim($getJson->get('formtype'));
            
            
            if (empty($quantity) || empty($speciesId) || empty($pattern) || 
            empty($grainDirectionId) || empty($gradeId) || 
            empty($thicknessId) || empty($width) || empty($length) || empty($coreTypeId) 
            || empty($backer) || empty($lumberFee)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {

                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved veneer data.';
                $statusCode = 200;
                $lastInserted = $this->saveVeneerData($quantity, $speciesId, 
                $pattern, $grainDirectionId, $gradeId, $thicknessId, $width, $isNetSize, 
                $length, $coreTypeId, $backer, $isFlexSanded, $sequenced, $lumberFee,
                $comments,$createdAt,$fileId,$quoteId,$formtype);
                $arrApi['lastInserted'] = $lastInserted;
            
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        return new JsonResponse($arrApi, $statusCode);
    }

    private function saveVeneerData($quantity, $speciesId,$pattern, $grainDirectionId, $gradeId, $thicknessId, $width, $isNetSize,$length, $coreTypeId, $backer, $isFlexSanded, $sequenced, $lumberFee,$comments,$createdAt,$fileId,$quoteId,$formtype=null)
    {
        $em = $this->getDoctrine()->getManager();
        $veneer = new Veneer();
        $veneer->setQuantity($quantity);
        $veneer->setSpeciesId($speciesId);
        $veneer->setGrainPatternId('');
        $veneer->setFlakexFiguredId('');
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
        $veneer->setFileId(0);
        $veneer->setQuoteId($quoteId);
        $veneer->setCreatedAt($createdAt);
        $veneer->setUpdatedAt($createdAt);
        $veneer->setIsActive(1);
        $veneer->setCustMarkupPer(25);
        $veneer->setVenCost(0);
        $veneer->setVenWaste(0);
        $veneer->setSubTotalVen(0);
        $veneer->setCoreCost(0);
        $veneer->setSubTotalCore(0);
        $veneer->setCoreWaste(0);
        $veneer->setSubTotalBackr(0);
        $veneer->setBackrCost(0);
        $veneer->setBackrWaste(0);
        $veneer->setTotCostPerPiece(0);
        $veneer->setRunningCost(0);
        $veneer->setRunningWaste(0);
        $veneer->getSubTotalrunning(0);
        $veneer->setMarkup(0);
        $veneer->setMarkup(0);
        $veneer->setSellingPrice(0);
        $veneer->setLineitemTotal(0);
        $veneer->setMachineSetup(0);
        $veneer->setMachineTooling(0);
        $veneer->setPreFinishSetup(0);
        $veneer->setColorMatch(0);
        $veneer->setTotalCost(0);
        $em->persist($veneer);
        $em->flush();
        $lastInserted = $veneer->getId();
        //var_dump($fileId);
        $fileId_ar = explode(',', $fileId);
        //var_dump($fileId_ar);
        //echo count($fileId_ar);
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
     * @Route("api/veneer/getVeneerData")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     */

     public function getVeneerDataAction(Request $request) {
        if ($request->getMethod() == 'POST') {

            $uploadDirectory = $this->container->getParameter('upload_file_destination');

            $_DATA = file_get_contents('php://input');
            $_DATA = json_decode($_DATA, true);
            $arrApi = array();
            $currLoggedInUserId = $_DATA['current_user_id'];
            $currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);
            if ( $currLoggedInUserRoleId != 1 ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no access.';
            } else {
                $veneer = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findOneById($_DATA['id']);
                if (empty($veneer)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'This veneer does not exists.';
                } else {
                    if (count($_DATA) == 2 && array_key_exists('id', $_DATA) && array_key_exists('current_user_id', $_DATA)) {
                        if (empty($_DATA['id']) || empty($currLoggedInUserRoleId)) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Parameter missing.';
                        } else {
                            $arrApi['status'] = 1;
                            $arrApi['message'] = 'Successfully retrerived the veneer details.';
                            $userId = $_DATA['id'];
                            //$profileObj = $this->getProfileDataOfUser($userId);

                            $arrApi['data']['id'] = $userId;
                            $arrApi['data']['quantity'] = $veneer->getQuantity();
                            $arrApi['data']['type'] = 'veneer';
                            $arrApi['data']['speciesId'] = $veneer->getSpeciesId();
                            $arrApi['data']['grainPatternId'] = $veneer->getGrainPatternId();
                            $arrApi['data']['flakexFiguredId'] = $veneer->getFlakexFiguredId();
                            $arrApi['data']['patternId'] = $veneer->getPatternId();
                            $arrApi['data']['grainDirectionId'] = $veneer->getGrainDirectionId();
                            $arrApi['data']['gradeId'] = $veneer->getGradeId();
                            $arrApi['data']['thicknessId'] = $veneer->getThicknessId();
                            $arrApi['data']['width'] = $veneer->getWidth();
                            $arrApi['data']['isNetSize'] = $veneer->getIsNetSize();
                            $arrApi['data']['length'] = $veneer->getLength();
                            $arrApi['data']['coreTypeId'] = $veneer->getCoreTypeId();
                            $arrApi['data']['backer'] = $veneer->getBacker();
                            $arrApi['data']['isFlexSanded'] = $veneer->getIsFlexSanded();
                            $arrApi['data']['sequenced'] = $veneer->getSequenced();
                            $arrApi['data']['lumberFee'] = $veneer->getLumberFee();
                            $arrApi['data']['comments'] = $veneer->getComments();
                            $arrApi['data']['quoteId'] = $veneer->getQuoteId();
                            $arrApi['data']['fileId'] = $veneer->getFileId();

                            $allfiles = $this->getDoctrine()->getRepository("AppBundle:Files")->findBy(array('attachableid'=>$_DATA['id'],'attachabletype'=>'Veneer'));
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
                            $arrApi['data']['isactive'] = $veneer->getIsActive();
                            $arrApi['data']['custMarkupPer'] = $veneer->getCustMarkupPer();
                            $arrApi['data']['venCost'] = $veneer->getVenCost();
                            $arrApi['data']['venWaste'] = $veneer->getVenWaste();
                            $arrApi['data']['subTotVen'] = $veneer->getSubTotalVen();
                            $arrApi['data']['coreCost'] = $veneer->getCoreCost();
                            $arrApi['data']['coreWaste'] = $veneer->getCoreWaste();
                            $arrApi['data']['subTotCore'] = $veneer->getSubTotalCore();
                            $arrApi['data']['backrCost'] = $veneer->getBackrCost();
                            $arrApi['data']['backrWaste'] = $veneer->getBackrWaste();
                            $arrApi['data']['subTotBackr'] = $veneer->getSubTotalBackr();
                            $arrApi['data']['rfCost'] = $veneer->getRunningCost();
                            $arrApi['data']['rfWaste'] = $veneer->getRunningWaste();
                            $arrApi['data']['subTotRf'] = $veneer->getSubTotalrunning();

                            $arrApi['data']['totCostPerPiece'] = $veneer->getTotCostPerPiece();
                            $arrApi['data']['macrkup'] = $veneer->getMarkup();
                            $arrApi['data']['sellingPrice'] = $veneer->getSellingPrice();
                            $arrApi['data']['lineitemTotal'] = $veneer->getLineitemTotal();
                            $arrApi['data']['machineSetup'] = $veneer->getMachineSetup();
                            $arrApi['data']['machineTooling'] = $veneer->getMachineTooling();
                            $arrApi['data']['preFinishSetup'] = $veneer->getPreFinishSetup();
                            $arrApi['data']['colorMatch'] = $veneer->getColorMatch();
                            $arrApi['data']['totalCost'] = $veneer->getTotalCost();

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
     * @Route("/api/veneer/edit" , name="edit_veneer")
     * @Method({"POST"})
     * Security("is_granted('ROLE_USER')")
     */
     public function editVeneerAction(Request $request) {
        
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
            
            
            if (empty($id) || empty($quantity) || empty($speciesId)
            || empty($pattern) || empty($grainDirectionId) || empty($gradeId) || 
            empty($thicknessId) || empty($width) || empty($length) || empty($coreTypeId) 
            || empty($backer) || empty($lumberFee)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the fields.';
                $statusCode = 422;
            } else {

                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully saved veneer data.';
                $statusCode = 200;
                $this->editVeneerData($id,$quantity, $speciesId,
                $pattern, $grainDirectionId, $gradeId, $thicknessId, $width, $isNetSize, 
                $length, $coreTypeId, $backer, $isFlexSanded, $sequenced, $lumberFee,
                $comments,$fileId,$createdAt);
            
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        return new JsonResponse($arrApi, $statusCode);
    }

    private function editVeneerData($id,$quantity, $speciesId,$pattern, $grainDirectionId, $gradeId, $thicknessId, $width, $isNetSize,$length, $coreTypeId, $backer, $isFlexSanded, $sequenced, $lumberFee,$comments,$fileId,$createdAt) 
    {
        $em = $this->getDoctrine()->getManager();
        $veneer =  $this->getDoctrine()->getRepository('AppBundle:Veneer')->find($id);

        //$veneer = new Veneer();
        $veneer->setQuantity($quantity);
        $veneer->setSpeciesId($speciesId);
        $veneer->setGrainPatternId('');
        $veneer->setFlakexFiguredId('');
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
        $veneer->setFileId(0);
        //$veneer->setQuoteId('1');
        //$veneer->setCreatedAt($createdAt);
        $veneer->setUpdatedAt($createdAt);

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
     * @Route("api/veneer/softDeleteVeneer")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     */

     public function softDeleteVeneerAction(Request $request) {
        
        $_DATA = file_get_contents('php://input');
        $_DATA = json_decode($_DATA, true);
        $arrApi = array();
        $id = $_DATA['id'];
        $type = $_DATA['type'];
        $createdAt = new \DateTime('now');

        $em = $this->getDoctrine()->getManager();
        
        if($type == 'veneer')
        {
            $veneer =  $this->getDoctrine()->getRepository('AppBundle:Veneer')->find($id);
            $veneer->setIsActive(0);
            $veneer->setUpdatedAt($createdAt);
            
        }
        else if($type == 'plywood')
        {
            $plywood =  $this->getDoctrine()->getRepository('AppBundle:Plywood')->find($id);
            $plywood->setIsActive(0);
            $plywood->setUpdatedAt($createdAt);
            
        } else {
            $door =  $this->getDoctrine()->getRepository('AppBundle:Doors')->find($id);
            $door->setStatus(0);
            $door->setUpdatedAt($createdAt);
        }

        try{
            $em->flush();
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        $statusCode = 200;
        $arrApi['status'] = 1;
        $arrApi['message'] = 'Line Item deleted Successfully.';
            
        return new JsonResponse($arrApi,$statusCode);
    }

    /**
     * @Route("api/veneer/saveVeneerCalculatedPrice")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     */
    public function saveVeneerCalculatedPriceAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        //print_r($data);die;
        if ( empty($data['veneerId']) || $data['custMarkupPer'] == null || empty($data['venCost']) || empty($data['venWaste'])) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Please provide all the details.';
            $statusCode = 422;
        } else {
            try {
                $updateVeneer = $this->updateVeneerCalculatedPrice($data);
                if ($updateVeneer) {
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

    private function updateVeneerCalculatedPrice($data) {
        //$venData = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findOneById($data['veneerId']);
        $em = $this->getDoctrine()->getManager();
        $venData = $em->getRepository(Veneer::class)->find($data['veneerId']);
        $venData->setCustMarkupPer($data['custMarkupPer']);
        $venData->setVenCost($data['venCost']);
        $venData->setVenWaste($data['venWaste']);
        $venData->setSubTotalVen($data['subTotVen']);
        $venData->setCoreCost($data['corCost']);
        $venData->setCoreWaste($data['corWaste']);
        $venData->setSubTotalCore($data['subTotCor']);
        $venData->setBackrCost($data['bakrCost']);
        $venData->setBackrWaste($data['bakrWaste']);
        $venData->setSubTotalBackr($data['subTotBackr']);

        $venData->setRunningCost($data['rfCost']);
        $venData->setRunningWaste($data['rfWaste']);
        $venData->setSubTotalrunning($data['subTotRf']);

        $venData->setTotCostPerPiece($data['totalCostPerPiece']);
        $venData->setMarkup($data['markup']);
        $venData->setSellingPrice($data['sellingPrice']);
        $venData->setLineitemTotal($data['lineitemTotal']);
        $venData->setMachineSetup($data['machnStp']);
        $venData->setMachineTooling($data['machnTlng']);
        $venData->setPreFinishSetup($data['preFnshStp']);
        $venData->setColorMatch($data['clrMatch']);
        $venData->setTotalCost($data['totalCost']);
        $em->persist($venData);
        $em->flush();
        return 1;
    }

}