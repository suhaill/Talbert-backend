<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Plywood;
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
use AppBundle\Entity\Veneer;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class QuoteController extends Controller
{
    /**
     * @Route("/api/quote/addQuote")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: Various
     */
    public function addQuoteAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $qDate = trim($data->get('date'));
            $quoteAddedby = trim($data->get('current_user_id'));
            $ver = 1;
            $custId = trim($data->get('customer_id'));
            $refNo = trim($data->get('reference_no'));
            $salsManId = trim($data->get('salesman'));
            $job = trim($data->get('job'));
            $termId = trim($data->get('term_id'));
            $shipMethod = trim($data->get('ship_method'));
            $shipAddId = trim($data->get('ship_add_id'));
            $leadTime = trim($data->get('lead_time'));
            $status = trim($data->get('status'));
            $datime = new \DateTime('now');
            if (empty($qDate) || empty($quoteAddedby) || empty($custId) || empty($refNo) || empty($salsManId) || empty($job) || empty($termId) || empty($shipMethod) || empty($shipAddId) || empty($leadTime) || empty($status) || empty($datime)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the details';
                $statusCode = 422;
            } else {
                $lastCtrlNo = $this->getLastControlNumber();
                if (empty($lastCtrlNo)) {
                    $ctrlNo = 1;
                } else {
                    $ctrlNo = $lastCtrlNo+1;
                }
                $refNocount = $this->checkIfRefNoExists($refNo);
                if (!empty($refNocount)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'This reference number already exists';
                    $statusCode = 422;
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully saved quote';
                    $statusCode = 200;
                    $arrApi['data']['lastInsertId'] = $this->saveQuoteData($qDate, $quoteAddedby, $ctrlNo, $ver, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $datime);
                }
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/quote/getQuotesList")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getQuotesAction() {
        $arrApi = array();
        $statusCode = 200;
            $quotes = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findBy(array(),array('id'=>'desc'));
            if (empty($quotes) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no quote.';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the quote list.';
                for($i=0;$i<count($quotes);$i++) {
                    $arrApi['data']['quotes'][$i]['id'] = $quotes[$i]->getId();
                    $arrApi['data']['quotes'][$i]['estimateNumber'] = 'E-'.$quotes[$i]->getControlNumber().'-'.$quotes[$i]->getVersion();
                    $arrApi['data']['quotes'][$i]['customername'] = $this->getCustomerNameById($quotes[$i]->getCustomerId());
                    $arrApi['data']['quotes'][$i]['status'] = $quotes[$i]->getStatus();
                    $arrApi['data']['quotes'][$i]['estDate'] = $this->getEstimateDateFormate($quotes[$i]->getEstimateDate());
                }
            }
         return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/quote/getQuoteDetails")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getQuoteDetailsAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $quoteId = $request->query->get('id');
            if (empty($quoteId)) {
                $quotes = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findBy(array(),array('id'=>'desc'));
                if (!empty($quotes)) {
                    $quoteId = $quotes[0]->getId();
                }
            }
            $quoteData = $this->getQuoteDataById($quoteId);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This quote does not exists';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived quote details';
                $arrApi['data']['id'] = $quoteData->getId();
                $arrApi['data']['date'] = $quoteData->getEstimateDate();
                //$arrApi['data']['estimatorId'] = $quoteData->getEstimatorId();
                $arrApi['data']['controlNumber'] = $quoteData->getControlNumber();
                $arrApi['data']['version'] = $quoteData->getVersion();
                $arrApi['data']['customer'] = $this->getCustomerNameById($quoteData->getCustomerId());
                $arrApi['data']['customerId'] = $quoteData->getCustomerId();
                $arrApi['data']['referenceNumber'] = $quoteData->getRefNum();
                $arrApi['data']['salesman'] = $this->getSalesmanNameById($quoteData->getSalesmanId());
                $arrApi['data']['salesmanId'] = $quoteData->getSalesmanId();
                $arrApi['data']['job'] = $quoteData->getJobName();
                $arrApi['data']['term'] = $quoteData->getTermId();
                $arrApi['data']['shipMethod'] = $this->getShipMethodNamebyId($quoteData->getShipMethdId());
                $arrApi['data']['shipMethodId'] = $quoteData->getShipMethdId();
                $arrApi['data']['billAdd'] = $this->getBillAddById($quoteData->getCustomerId());
                $arrApi['data']['shipAdd'] = $this->getShippingAddById($quoteData->getShipAddId());
                $arrApi['data']['shipAddId'] = $quoteData->getShipAddId();
                $arrApi['data']['leadTime'] = $quoteData->getLeadTime();
                $arrApi['data']['status'] = $quoteData->getStatus();
                $arrApi['data']['lineitems'] = $this->getVeneerslistbyQuoteId($quoteId);
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/quote/cloneQuote")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function cloneQuoteWithLineItemsAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $quoteId = $request->query->get('id');
            $datime = new \DateTime('now');
            $quoteData = $this->getQuoteDataById($quoteId);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This quote does not exists';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully cloned quote';
                $clonedQuoteId = $this->cloneQuoteData($quoteData, $datime);
                //$clonedQuoteId = 99;
                $this->clonePlywoodData($quoteId, $clonedQuoteId, $datime);
                $this->cloneVeneerData($quoteId, $clonedQuoteId, $datime);
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/quote/getCustomerNameByQuoteId")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getCustomerNameByQuoteIdAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $quoteId = $request->query->get('id');
            $quoteData = $this->getQuoteDataById($quoteId);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This quote does not exists';
                $statusCode = 422;
            } else {
                $custName = $this->getCustomerNameByQuote($quoteId);
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully cloned quote';
                $arrApi['data']['customerName'] = $custName;
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }


    //Reusable codes

    private function cloneVeneerData($quoteId, $clonedQuoteId, $datime) {
        $veneeerData = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId'=>$quoteId));
        if (!empty($veneeerData)) {
            $em = $this->getDoctrine()->getManager();
            for ($i=0; $i< count($veneeerData); $i++) {
                $veneer = new Veneer();
                $veneer->setQuantity($veneeerData[$i]->getQuantity());
                $veneer->setSpeciesId($veneeerData[$i]->getSpeciesId());
                $veneer->setGrainPatternId($veneeerData[$i]->getGrainPatternId());
                $veneer->setFlakexFiguredId($veneeerData[$i]->getFlakexFiguredId());
                $veneer->setPatternId($veneeerData[$i]->getPatternId());
                $veneer->setGrainDirectionId($veneeerData[$i]->getGrainDirectionId());
                $veneer->setGradeId($veneeerData[$i]->getGradeId());
                $veneer->setThicknessId($veneeerData[$i]->getThicknessId());
                $veneer->setWidth($veneeerData[$i]->getWidth());
                $veneer->setIsNetSize($veneeerData[$i]->getIsNetSize());
                $veneer->setLength($veneeerData[$i]->getLength());
                $veneer->setCoreTypeId($veneeerData[$i]->getCoreTypeId());
                $veneer->setBacker($veneeerData[$i]->getBacker());
                $veneer->setIsFlexSanded($veneeerData[$i]->getIsFlexSanded());
                $veneer->setSequenced($veneeerData[$i]->getSequenced());
                $veneer->setLumberFee($veneeerData[$i]->getLumberFee());
                $veneer->setComments($veneeerData[$i]->getComments());
                $veneer->setQuoteId($clonedQuoteId);
                $veneer->setFileId($veneeerData[$i]->getFileId());
                $veneer->setCreatedAt($datime);
                $veneer->setUpdatedAt($datime);
                $em->persist($veneer);
                $em->flush();
            }
        }
    }

    private function clonePlywoodData($quoteId, $clonedQuoteId, $datime) {
        $ply = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId'=>$quoteId));
        if (!empty($ply)) {
            $em = $this->getDoctrine()->getManager();
            for ($i=0; $i< count($ply); $i++) {
                $plywd = new Plywood();
                $plywd->setQuantity($ply[$i]->getQuantity());
                $plywd->setSpeciesId($ply[$i]->getSpeciesId());
                $plywd->setGrainPatternId($ply[$i]->getGrainPatternId());
                $plywd->setPatternId($ply[$i]->getPatternId());
                $plywd->setGrainDirectionId($ply[$i]->getGrainDirectionId());
                $plywd->setFlakexFiguredId($ply[$i]->getFlakexFiguredId());
                $plywd->setGradeId($ply[$i]->getGradeId());
                $plywd->setThicknessId($ply[$i]->getThicknessId());
                $plywd->setPlywoodWidth($ply[$i]->getPlywoodWidth());
                $plywd->setPlywoodLength($ply[$i]->getPlywoodLength());
                $plywd->setFinishThickId($ply[$i]->getFinishThickId());
                $plywd->setBackerId($ply[$i]->getBackerId());
                $plywd->setIsSequenced($ply[$i]->getIsSequenced());
                $plywd->setCoreType($ply[$i]->getCoreType());
                $plywd->setThickness($ply[$i]->getThickness());
                $plywd->setFinish($ply[$i]->getFinish());
                $plywd->setUvCuredId($ply[$i]->getUvCuredId());
                $plywd->setSheenId($ply[$i]->getSheenId());
                $plywd->setShameOnId($ply[$i]->getShameOnId());
                $plywd->setEdgeDetail($ply[$i]->getEdgeDetail());
                $plywd->setTopEdge($ply[$i]->getTopEdge());
                $plywd->setBottomEdge($ply[$i]->getBottomEdge());
                $plywd->setRightEdge($ply[$i]->getRightEdge());
                $plywd->setLeftEdge($ply[$i]->getLeftEdge());
                $plywd->setEdgeMaterialId($ply[$i]->getEdgeMaterialId());
                $plywd->setBedgeMaterialId($ply[$i]->getBedgeMaterialId());
                $plywd->setRedgeMaterialId($ply[$i]->getRedgeMaterialId());
                $plywd->setLedgeMaterialId($ply[$i]->getLedgeMaterialId());
                $plywd->setEdgeFinishSpeciesId($ply[$i]->getEdgeFinishSpeciesId());
                $plywd->setBedgeFinishSpeciesId($ply[$i]->getBedgeFinishSpeciesId());
                $plywd->setRedgeFinishSpeciesId($ply[$i]->getRedgeFinishSpeciesId());
                $plywd->setLedgeFinishSpeciesId($ply[$i]->getLedgeFinishSpeciesId());
                $plywd->setMilling($ply[$i]->getMilling());
                $plywd->setMillingDescription($ply[$i]->getMillingDescription());
                $plywd->setCost($ply[$i]->getCost());
                $plywd->setUnitMesureCostId($ply[$i]->getUnitMesureCostId());
                $plywd->setIsLabels($ply[$i]->getIsLabels());
                $plywd->setNumberLabels($ply[$i]->getNumberLabels());
                $plywd->setAutoNumber($ply[$i]->getAutoNumber());
                $plywd->setLumberFee($ply[$i]->getLumberFee());
                $plywd->setComments($ply[$i]->getComments());
                $plywd->setQuoteId($clonedQuoteId);
                $plywd->setCreatedAt($datime);
                $plywd->setUpdatedAt($datime);
                $plywd->setFileId($ply[$i]->getFileId());
                $em->persist($plywd);
                $em->flush();
            }
        }
    }

    private function cloneQuoteData($qData, $datime) {
        $em = $this->getDoctrine()->getManager();
        $quote = new Quotes();
        $quote->setRefid($qData->getId());
        $quote->setEstimatedate($qData->getEstimatedate());
        $quote->setEstimatorId($qData->getEstimatorId());
        $quote->setControlNumber($qData->getControlNumber());
        $quote->setVersion($qData->getVersion());
        $quote->setCustomerId($qData->getCustomerId());
        $quote->setRefNum($qData->getRefNum());
        $quote->setSalesmanId($qData->getSalesmanId());
        $quote->setJobName($qData->getJobName());
        $quote->setTermId($qData->getTermId());
        $quote->setShipMethdId($qData->getShipMethdId());
        $quote->setShipAddId($qData->getShipAddId());
        $quote->setLeadTime($qData->getLeadTime());
        $quote->setStatus($qData->getStatus());
        $quote->setCreatedAt($datime);
        $quote->setUpdatedAt($datime);
        $em->persist($quote);
        $em->flush();
        return $quote->getId();
    }

    private function getLastControlNumber() {
        $lastQuote = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneBy(array(),array('id'=>'desc'));
        if (!empty($lastQuote)) {
            return $lastQuote->getControlNumber();
        }
    }

    private  function checkIfRefNoExists($refNo) {
        return $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneBy(array('refNum' => $refNo));
    }

    private function saveQuoteData($qDate, $quoteAddedby, $ctrlNo, $ver, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $datime) {
        $em = $this->getDoctrine()->getManager();
        $quote = new Quotes();
        $quote->setEstimatedate($qDate);
        $quote->setEstimatorId($quoteAddedby);
        $quote->setControlNumber($ctrlNo);
        $quote->setVersion($ver);
        $quote->setCustomerId($custId);
        $quote->setRefNum($refNo);
        $quote->setSalesmanId($salsManId);
        $quote->setJobName($job);
        $quote->setTermId($termId);
        $quote->setShipMethdId($shipMethod);
        $quote->setShipAddId($shipAddId);
        $quote->setLeadTime($leadTime);
        $quote->setStatus($status);
        $quote->setCreatedAt($datime);
        $quote->setUpdatedAt($datime);
        $em->persist($quote);
        $em->flush();
        return $quote->getId();
    }

    private function getCustomerNameById($customer_id) {
        if (!empty($customer_id)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $customer_id));
            $customerName =  $profileObj->getFname();
            if (!empty($customerName)) {
                return $customerName;
            }
        }
    }

    private function getSalesmanNameById($salesman_id) {
        if (!empty($salesman_id)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $salesman_id));
            $customerName =  $profileObj->getFname();
            if (!empty($customerName)) {
                return $customerName;
            }
        }
    }

    private function getEstimateDateFormate($date) {
        $dateArr =  explode('-', explode('T',$date)[0]);
        return $d = $dateArr[1].'/'.$dateArr[2].'/'.$dateArr[0];
    }

    private function getQuoteDataById($qId) {
        return $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneById($qId);
    }

    private function getBillAddById($cust_id) {
        $addArr = array();
        $addData = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findOneBy(array('userId' => $cust_id));
        if (!empty($addData)) {
            $addArr['street'] = $addData->getStreet();
            $addArr['state'] = $this->getStateNamebyId($addData->getStateId());
            $addArr['city'] = $addData->getCity();
            $addArr['zip'] = $addData->getZip();
            return $addArr;
        }
    }

    private function getShippingAddById($add_id) {
        $addArr = array();
        $addData = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findOneById($add_id);
        if (!empty($addData)) {
            $addArr['nickname'] = $addData->getNickname();
            $addArr['street'] = $addData->getStreet();
            $addArr['state'] = $this->getStateNamebyId($addData->getStateId());
            $addArr['city'] = $addData->getCity();
            $addArr['zip'] = $addData->getZip();
            return $addArr;
        }
    }

    private function getStateNamebyId($state_id) {
        $stateRecord = $this->getDoctrine()->getRepository('AppBundle:State')->findOneById($state_id);
        if (!empty($stateRecord)) {
            return $stateRecord->getStateName();
        }
    }

    private function getShipMethodNamebyId($hipmethod_id) {
        $shippmethodRecord = $this->getDoctrine()->getRepository('AppBundle:ShippingMethods')->findOneById($hipmethod_id);
        if (!empty($shippmethodRecord)) {
            return $shippmethodRecord->getName();
        }
    }

    private function getVeneerslistbyQuoteId($qId) {
        $lineItem = array();
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $qId));
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $qId));
        $i=0;
        if (!empty($plywoodRecords) || !empty($veneerRecords)) {
            if (!empty($plywoodRecords)) {
                foreach ($plywoodRecords as $p) {
                    $lineItem[$i]['id'] = $p->getId();
                    $lineItem[$i]['type'] = 'plywood';
                    $lineItem[$i]['url'] = 'line-item/edit-plywood';
                    $lineItem[$i]['quantity'] = $p->getQuantity();
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($p->getSpeciesId());
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($p->getPatternId());
                    $lineItem[$i]['grade'] = $this->getGradeNameById($p->getGradeId());
                    $lineItem[$i]['back'] = $this->getBackNameById($p->getBackerId());
                    $lineItem[$i]['thickness'] = $this->getThicknessNameById($p->getThicknessId());
                    $lineItem[$i]['width'] = $p->getPlywoodWidth();
                    $lineItem[$i]['length'] = $p->getPlywoodLength();
                    $lineItem[$i]['core'] = $this->getCoreNameById($p->getCoreType());
                    $lineItem[$i]['edge'] = $this->getEdgeNameById($p->getEdgeDetail());
                    $lineItem[$i]['unitPrice'] = $p->getCost();
                    $lineItem[$i]['totalPrice'] = $p->getCost();
                    $i++;
                }
            }
            if (!empty($veneerRecords)) {
                foreach ($veneerRecords as $v) {
                    $lineItem[$i]['id'] = $v->getId();
                    $lineItem[$i]['type'] = 'veneer';
                    $lineItem[$i]['url'] = 'line-item/edit-veneer';
                    $lineItem[$i]['quantity'] = $v->getQuantity();
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($v->getSpeciesId());
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($v->getPatternId());
                    $lineItem[$i]['grade'] = $this->getGradeNameById($v->getGradeId());
                    $lineItem[$i]['back'] = $this->getBackNameById($v->getBacker());
                    $lineItem[$i]['thickness'] = $this->getThicknessNameById($v->getThicknessId());
                    $lineItem[$i]['width'] = $v->getWidth();
                    $lineItem[$i]['length'] = $v->getLength();
                    $lineItem[$i]['core'] = $this->getCoreNameById($v->getCoreTypeId());
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = 'NA';
                    $lineItem[$i]['totalPrice'] = 'NA';
                    $i++;
                }
            }
            return $lineItem;
        }
    }

    private function getSpeciesNameById($species_id) {
        $productRecord = $this->getDoctrine()->getRepository('AppBundle:Species')->findOneById($species_id);
        if (!empty($productRecord)) {
            return $productRecord->getName();
        }
    }

    private function getPatternNameById($pattern_id) {
        $patternRecord = $this->getDoctrine()->getRepository('AppBundle:Pattern')->findOneById($pattern_id);
        if (!empty($patternRecord)) {
            return $patternRecord->getName();
        }
    }

    private function getGradeNameById($gId) {
        $gradeRecord = $this->getDoctrine()->getRepository('AppBundle:FaceGrade')->findOneById($gId);
        if (!empty($gradeRecord)) {
            return $gradeRecord->getName();
        }
    }

    private function getBackNameById($bId) {
        $backRecord = $this->getDoctrine()->getRepository('AppBundle:Backer')->findOneById($bId);
        if (!empty($backRecord)) {
            return $backRecord->getName();
        }
    }

    private function getThicknessNameById($tId) {
        $thicknessRecord = $this->getDoctrine()->getRepository('AppBundle:Thickness')->findOneById($tId);
        if (!empty($thicknessRecord)) {
            return $thicknessRecord->getName();
        }
    }

    private function getCoreNameById($cId) {
        $cTypeRecord = $this->getDoctrine()->getRepository('AppBundle:CoreType')->findOneById($cId);
        if (!empty($cTypeRecord)) {
            return $cTypeRecord->getName();
        }
    }

    private function getEdgeNameById($eId) {
        $eFinishRecord = $this->getDoctrine()->getRepository('AppBundle:EdgeFinish')->findOneById($eId);
        if (!empty($eFinishRecord)) {
            return $eFinishRecord->getName();
        }
    }

    private function getCustomerNameByQuote($quoteId) {
        $quoteRecord = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneById($quoteId);
        $custId = $quoteRecord->getCustomerId();
        return $this->getCustomerNameById($custId);

    }
}