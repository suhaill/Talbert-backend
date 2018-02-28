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
use PDO;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Orders;
use AppBundle\Entity\Quotes;
use AppBundle\Entity\Profile;

class OrderController extends Controller
{
    /**
     * @Route("/api/order/getOrders")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getOrdersListAction(Request $request) {
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $orders = $this->getDoctrine()->getRepository('AppBundle:Orders')->findBy(array(), array('id' => 'DESC'));
            if (empty($orders)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no order right now';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the orders list';
                $i=0;
                foreach ($orders as $orDat) {
                    $arrApi['data']['orders'][$i]['id'] = $orDat->getId();
                    $arrApi['data']['orders'][$i]['estNumber'] = $orDat->getEstNumber();
                    $arrApi['data']['orders'][$i]['orderDate'] = $orDat->getOrderDate()->format('m/d/y');
                    $arrApi['data']['orders'][$i]['productname'] = $orDat->getProductName();
                    $arrApi['data']['orders'][$i]['poNumber'] = $orDat->getPoNumber();
                    $arrApi['data']['orders'][$i]['shipDate'] = $orDat->getShipDate()->format('m/d/y');
                    $i++;
                }
            }
            return new JsonResponse($arrApi);
        }
    }

    /**
     * @Route("/api/order/searchOrders")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     */
    public function searchOrdersAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try{
            $estNo = $request->query->get('est');
            $orderRecords = $this->getRecordsBasedOnEstimateNo($estNo);
            if (empty($orderRecords)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no such order';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived orders list';
                $i=0;
                foreach ($orderRecords as $orDat) {
                    $arrApi['data']['orders'][$i]['id'] = $orDat['id'];
                    $arrApi['data']['orders'][$i]['estNumber'] = $orDat['est_number'];
                    $arrApi['data']['orders'][$i]['orderDate'] = $this->formateDate($orDat['order_date']);
                    $arrApi['data']['orders'][$i]['productname'] = $orDat['product_name'];
                    $arrApi['data']['orders'][$i]['poNumber'] = $orDat['po_number'];
                    $arrApi['data']['orders'][$i]['shipDate'] = $this->formateDate($orDat['ship_date']);
                    $i++;
                }
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function getRecordsBasedOnEstimateNo($estNo) {
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select * from orders WHERE est_number like '$estNo%' order by id desc";
        $stmt=$conn->prepare($SQL);
        //$stmt->bindParam(':est',$estNo,PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function formateDate($date) {
        $expDate = explode(' ', $date)[0];
        $expDate1 = explode('-',$expDate);
        return $expDate1[1].'/'.$expDate1[2].'/'.$expDate1[0];
    }
    
    /**
     * @Route("/api/order/getOrderList")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function getOrderListAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $columnName = trim($data->get('columnName'));
        $orderBy = trim($data->get('orderBy'));
        $requestColumnName='';
        if($columnName=='' || $columnName=='customer'){
            $requestColumnName=$columnName;
            $requestOrderBy=$orderBy;
            $columnName='id';
            $orderBy='DESC';
        }
        if($columnName=='estimatorId'){
            $sortArray=['controlNumber'=>$orderBy];
        } else {
            $sortArray=[$columnName=>$orderBy];
        }
        $quotes = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findBy(array('status'=> array('Approved')),$sortArray);
        if (empty($quotes) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no order.';
            $statusCode = 422;
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the order list.';
            $quoteList=[];
            for($i=0;$i<count($quotes);$i++) {
                $quoteList[$i]['id'] = $quotes[$i]->getId();
                $quoteList[$i]['estimateNumber'] = 'E-'.$quotes[$i]->getControlNumber().'-'.$quotes[$i]->getVersion();
                $quoteList[$i]['customername'] = strtoupper($this->getCustomerNameById($quotes[$i]->getCustomerId()));
                $quoteList[$i]['companyname'] = strtoupper($this->getCompanyById($quotes[$i]->getCustomerId()));
                $quoteList[$i]['status'] = $quotes[$i]->getStatus();
                $quoteList[$i]['estDate'] = $this->getEstimateDateFormate($quotes[$i]->getEstimateDate());
            }
            
            if($requestColumnName=='customer'){
                $arrApi['data']['orders'] = $this->__arraySortByColumn($quoteList, 'customername',$requestOrderBy);
            } else {
                $arrApi['data']['orders'] = $quoteList;
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }
    
    /**
     * @Route("/api/order/getOrderDetails")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getOrderDetailsAction(Request $request) {
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
            $this->updateQuoteData($quoteId);
            $quoteData = $this->getOrderDataById($quoteId);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This order does not exists';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived order details';
                $arrApi['data']['id'] = $quoteData->getId();
                $arrApi['data']['date'] = $quoteData->getEstimateDate();
                $arrApi['data']['estimatorId'] = $quoteData->getEstimatorId();
                $arrApi['data']['controlNumber'] = $quoteData->getControlNumber();
                $arrApi['data']['version'] = $quoteData->getVersion();
                $arrApi['data']['customer'] = $this->getCustomerNameById($quoteData->getCustomerId());
                $arrApi['data']['userEmail'] = $this->getCustomerEmailById($quoteData->getEstimatorId());
                $arrApi['data']['customerEmail'] = $this->getCustomerEmailById($quoteData->getCustomerId());
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
                $arrApi['data']['comment'] = $quoteData->getComment();
                $arrApi['data']['deliveryDate'] = $quoteData->getDeliveryDate();
                $arrApi['data']['quoteSubTot'] = $quoteData->getQuoteTot();
                $arrApi['data']['expFee'] = $quoteData->getExpFee();
                $arrApi['data']['discount'] = $quoteData->getDiscount();
                $arrApi['data']['lumFee'] = $quoteData->getLumFee();
                $arrApi['data']['shipCharge'] = $quoteData->getShipCharge();
                $arrApi['data']['salesTax'] = $quoteData->getSalesTax();
                $arrApi['data']['projectTot'] = $quoteData->getProjectTot();
                $arrApi['data']['lineitems'] = $this->getVeneerslistbyQuoteId($quoteId);
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }
    
    /**
     * @Route("/api/order/cloneOrder")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function cloneOrderWithLineItemsAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $quoteId = $request->query->get('id');
            $datime = new \DateTime('now');
            $quoteData = $this->getOrderDataById($quoteId);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This order does not exists';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully cloned order';
                $clonedQuoteId = $this->cloneOrderData($quoteData, $datime);
                $this->clonePlywoodData($quoteId, $clonedQuoteId, $datime);
                $this->cloneVeneerData($quoteId, $clonedQuoteId, $datime);
                $this->cloneDoorData($quoteId, $clonedQuoteId, $datime);
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }
    
    /**
     * @Route("/api/order/getEmailOrderData")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function getEmailOrderDataAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $qId = $data->get('qId');
        $currUserId = $data->get('currentuserId');
        if (empty($qId) || empty($currUserId)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing.';
            $statusCode = 422;
        } else {
            try {
                $custName = $this->getCustomerNameByQuote($qId);
                $userName = $this->getCustomerNameById($currUserId);
                $projName = 'Talbert Order Project';
                $messageTemplate = $this->replaceShortcodeFromMessage($custName,$userName,$projName,$this->getMessageTemplateforEmailQuote());
                if (!empty($messageTemplate)) {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully reterived email order data.';
                    $arrApi['data']['id'] = $qId;
                    $arrApi['data']['template'] = $messageTemplate;
                    $attachmentData = $this->getLineitemAttachmentsList($request,$qId);
                    $arrApi['data']['attachments'] = $attachmentData;
                }
            }
            catch(Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }
    
    /**
     * @Route("/api/quote/emailOrder")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function emailOrderAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $_DATA = file_get_contents('php://input');
        $_DATA = json_decode($_DATA, true);
        $qId = $_DATA['qId'];
        $currUserId = $_DATA['currentuserId'];
        $currUserEmail = $_DATA['currUserEmail'];
        $custEmail = $_DATA['custEmail'];
        $msg = $_DATA['msg'];
        $cmt = $_DATA['cmt'];
        $chkVal = $_DATA['chkVal'];
        $html = $_DATA['html'];
        $datime = new \DateTime('now');
        if (empty($qId) || empty($currUserId) || empty($currUserEmail) || empty($custEmail) || empty($msg) || empty($html)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing.';
            $statusCode = 422;
        } else {
            $pdfName = 'uploads/order_screenshots/OrderPDF-'.$qId.'-'.time().'.pdf';
            $quotePdfUrl = $this->createOrderLineitemPDF($html, $pdfName, $request);
            $newMessage = $this->createMessageToBeSent($msg, $cmt);
            $urls = $this->getAttachmentUrls($chkVal, $request);
            $urls[] = $quotePdfUrl;
            $message = \Swift_Message::newInstance()
                ->setFrom($currUserEmail)
                ->setTo($custEmail)
                ->setSubject('Order Email')
                ->setBody($newMessage, 'text/plain');
            for ($i=0;$i<count($urls);$i++) {
                $message->attach(\Swift_Attachment::fromPath($urls[$i]));
            }
            try {
                $mailSent = $this->get('mailer')->send($message);
                if ($mailSent) {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully sent email.';
                    $statusCode = 200;
                    $this->saveSentEmailDetails($qId, $currUserId, $cmt, $chkVal, $datime);
                    $fs = new Filesystem();
                    $fs->remove($pdfName);
                }
            }
            catch(Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
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
                $plywd->setUvColorId($ply[$i]->getUvColorId());
                $plywd->setCoreSameOnbe($ply[$i]->getCoreSameOnbe());
                $plywd->setCoreSameOnte($ply[$i]->getCoreSameOnte());
                $plywd->setCoreSameOnre($ply[$i]->getCoreSameOnre());
                $plywd->setCoreSameOnle($ply[$i]->getCoreSameOnle());
                $plywd->setCoreSameOnle($ply[$i]->getCoreSameOnle());
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
                $plywd->setCustMarkupPer($ply[$i]->getCustMarkupPer());
                $plywd->setVenCost($ply[$i]->getVenCost());
                $plywd->setVenWaste($ply[$i]->getVenWaste());
                $plywd->setSubTotalVen($ply[$i]->getSubTotalVen());
                $plywd->setCoreCost($ply[$i]->getCoreCost());
                $plywd->setCoreWaste($ply[$i]->getCoreWaste());
                $plywd->setSubTotalCore($ply[$i]->getSubTotalCore());
                $plywd->setBackrCost($ply[$i]->getBackrCost());
                $plywd->setBackrWaste($ply[$i]->getBackrWaste());
                $plywd->setSubTotalBackr($ply[$i]->getSubTotalBackr());
                $plywd->setFinishCost($ply[$i]->getFinishCost());
                $plywd->setFinishWaste($ply[$i]->getFinishWaste());
                $plywd->setSubTotalFinish($ply[$i]->getSubTotalFinish());
                $plywd->setEdgeintCost($ply[$i]->getEdgeintCost());
                $plywd->setEdgeintWaste($ply[$i]->getEdgeintWaste());
                $plywd->setSubTotalEdgeint($ply[$i]->getSubTotalEdgeint());
                $plywd->setEdgevCost($ply[$i]->getEdgevCost());
                $plywd->setEdgevWaste($ply[$i]->getEdgevWaste());
                $plywd->setSubTotalEdgev($ply[$i]->getSubTotalEdgev());
                $plywd->setFinishEdgeCost($ply[$i]->getFinishEdgeCost());
                $plywd->setFinishEdgeWaste($ply[$i]->getFinishEdgeWaste());
                $plywd->setSubTotalFinishEdge($ply[$i]->getSubTotalFinishEdge());
                $plywd->setMillingCost($ply[$i]->getMillingCost());
                $plywd->setMillingWaste($ply[$i]->getMillingWaste());
                $plywd->setSubTotalMilling($ply[$i]->getSubTotalMilling());
                $plywd->setRunningCost($ply[$i]->getRunningCost());
                $plywd->setRunningWaste($ply[$i]->getRunningWaste());
                $plywd->setSubTotalrunning($ply[$i]->getSubTotalrunning());
                $plywd->setTotalcostPerPiece($ply[$i]->getTotalcostPerPiece());
                $plywd->setMarkup($ply[$i]->getMarkup());
                $plywd->setSellingPrice($ply[$i]->getSellingPrice());
                $plywd->setLineitemTotal($ply[$i]->getLineitemTotal());
                $plywd->setMachineSetup($ply[$i]->getMachineSetup());
                $plywd->setMachineTooling($ply[$i]->getMachineTooling());
                $plywd->setPreFinishSetup($ply[$i]->getPreFinishSetup());
                $plywd->setColorMatch($ply[$i]->getColorMatch());
                $plywd->setTotalCost($ply[$i]->getTotalCost());
                $em->persist($plywd);
                $em->flush();
                $this->cloneAttachments($ply[$i]->getId(), $plywd->getId(), 'Plywood', $datime);
            }
        }
    }
    
    private function cloneOrderData($qData, $datime) {
        $em = $this->getDoctrine()->getManager();
        $quote = new Quotes();
        $quote->setRefid($qData->getId());
        $quote->setEstimatedate($qData->getEstimatedate());
        $quote->setEstimatorId($qData->getEstimatorId());
        $quote->setControlNumber($this->getLastControlNumber()+1);
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
        $quote->setComment($qData->getComment());
        $quote->setCreatedAt($datime);
        $quote->setUpdatedAt($datime);
        $quote->setQuoteTot($qData->getQuoteTot());
        $quote->setExpFee($qData->getExpFee());
        $quote->setDiscount($qData->getDiscount());
        $quote->setLumFee($qData->getLumFee());
        $quote->setShipCharge($qData->getShipCharge());
        $quote->setSalesTax($qData->getSalesTax());
        $quote->setProjectTot($qData->getProjectTot());
        $em->persist($quote);
        $em->flush();
        return $quote->getId();
    }
    
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
                $veneer->setCustMarkupPer($veneeerData[$i]->getCustMarkupPer());
                $veneer->setVenCost($veneeerData[$i]->getVenCost());
                $veneer->setVenWaste($veneeerData[$i]->getVenWaste());
                $veneer->setSubTotalVen($veneeerData[$i]->getSubTotalVen());
                $veneer->setCoreCost($veneeerData[$i]->getCoreCost());
                $veneer->setSubTotalCore($veneeerData[$i]->getSubTotalCore());
                $veneer->setCoreWaste($veneeerData[$i]->getCoreWaste());
                $veneer->setSubTotalBackr($veneeerData[$i]->getSubTotalBackr());
                $veneer->setBackrCost($veneeerData[$i]->getBackrCost());
                $veneer->setBackrWaste($veneeerData[$i]->getBackrWaste());
                $veneer->setRunningCost($veneeerData[$i]->getRunningCost());
                $veneer->setRunningWaste($veneeerData[$i]->getRunningWaste());
                $veneer->setSubTotalrunning($veneeerData[$i]->getSubTotalrunning());
                $veneer->setTotCostPerPiece($veneeerData[$i]->getTotCostPerPiece());
                $veneer->setMarkup($veneeerData[$i]->getMarkup());
                $veneer->setSellingPrice($veneeerData[$i]->getSellingPrice());
                $veneer->setLineitemTotal($veneeerData[$i]->getLineitemTotal());
                $veneer->setMachineSetup($veneeerData[$i]->getMachineSetup());
                $veneer->setMachineTooling($veneeerData[$i]->getMachineTooling());
                $veneer->setPreFinishSetup($veneeerData[$i]->getPreFinishSetup());
                $veneer->setColorMatch($veneeerData[$i]->getColorMatch());
                $veneer->setTotalCost($veneeerData[$i]->getTotalCost());
                $veneer->setCreatedAt($datime);
                $veneer->setUpdatedAt($datime);
                $em->persist($veneer);
                $em->flush();
                $this->cloneAttachments($veneeerData[$i]->getId(), $veneer->getId(), 'Veneer', $datime);
            }
        }
    }

    private function cloneDoorData($quoteId, $clonedQuoteId, $datime) {
        $doorData = $this->getDoctrine()->getRepository('AppBundle:Doors')->findBy(array('quoteId' => $quoteId));
        if (!empty($doorData)) {
            $em = $this->getDoctrine()->getManager();
            for ($i=0; $i< count($doorData); $i++) {
                $door = new Doors();
                $door->setQuoteId($clonedQuoteId);
                $door->setQty($doorData[$i]->getQty());
                $door->setPair($doorData[$i]->getPair());
                $door->setSwing($doorData[$i]->getSwing());
                $door->setWidth($doorData[$i]->getWidth());
                $door->setLength($doorData[$i]->getLength());
                $door->setThickness($doorData[$i]->getThickness());
                $door->setDoorUse($doorData[$i]->getDoorUse());
                $door->setConstruction($doorData[$i]->getConstruction());
                $door->setFireRating($doorData[$i]->getFireRating());
                $door->setDoorCore($doorData[$i]->getDoorCore());
                $door->setSequence($doorData[$i]->getSequence());
                $door->setSound($doorData[$i]->getSound());
                $door->setSoundDrop($doorData[$i]->getSoundDrop());
                $door->setLouvers($doorData[$i]->getLouvers());
                $door->setLouversDrop($doorData[$i]->getLouversDrop());
                $door->setBevel($doorData[$i]->getBevel());
                $door->setBevelDrop($doorData[$i]->getBevelDrop());
                $door->setEdgeFinish($doorData[$i]->getEdgeFinish());
                $door->setTopEdge($doorData[$i]->getTopEdge());
                $door->setTopEdgeMaterial($doorData[$i]->getTopEdgeMaterial());
                $door->setTopEdgeSpecies($doorData[$i]->getTopEdgeSpecies());
                $door->setBottomEdge($doorData[$i]->getBottomEdge());
                $door->setBottomEdgeMaterial($doorData[$i]->getBottomEdgeMaterial());
                $door->setBottomEdgeSpecies($doorData[$i]->getBottomEdgeSpecies());
                $door->setRightEdge($doorData[$i]->getRightEdge());
                $door->setREdgeMat($doorData[$i]->getREdgeMat());
                $door->setEEdgeSp($doorData[$i]->getEEdgeSp());
                $door->setLeftEdge($doorData[$i]->getLeftEdge());
                $door->setLEdgeMat($doorData[$i]->getLEdgeMat());
                $door->setLEdgeSp($doorData[$i]->getLEdgeSp());
                $door->setLightOpening($doorData[$i]->getLightOpening());
                $door->setLightOpDrop($doorData[$i]->getLightOpDrop());
                $door->setLocationFromTop($doorData[$i]->getLocationFromTop());
                $door->setLocFromLockEdge($doorData[$i]->getLocFromLockEdge());
                $door->setOpeningSize($doorData[$i]->getOpeningSize());
                $door->setStopSize($doorData[$i]->getStopSize());
                $door->setGlass($doorData[$i]->getGlass());
                $door->setGlassDrop($doorData[$i]->getGlassDrop());
                $door->setFinish($doorData[$i]->getFinish());
                $door->setFacPaint($doorData[$i]->getFacPaint());
                $door->setUvCured($doorData[$i]->getUvCured());
                $door->setColor($doorData[$i]->getColor());
                $door->setSheen($doorData[$i]->getSheen());
                $door->setSameOnBack($doorData[$i]->getSameOnBack());
                $door->setSameOnBottom($doorData[$i]->getSameOnBottom());
                $door->setSameOnTop($doorData[$i]->getSameOnTop());
                $door->setSameOnRight($doorData[$i]->getSameOnRight());
                $door->setSameOnLeft($doorData[$i]->getSameOnLeft());
                $door->setDoorFrame($doorData[$i]->getDoorFrame());
                $door->setDoorDrop($doorData[$i]->getDoorDrop());
                $door->setSurfaceMachning($doorData[$i]->getSurfaceMachning());
                $door->setSurfaceStyle($doorData[$i]->getSurfaceStyle());
                $door->setSurfaceDepth($doorData[$i]->getSurfaceDepth());
                $door->setSurfaceSides($doorData[$i]->getSurfaceSides());
                $door->setStyles($doorData[$i]->getStyles());
                $door->setStyleWidth($doorData[$i]->getStyleWidth());
                $door->setMachning($doorData[$i]->getMachning());
                $door->setHindgeModelNo($doorData[$i]->getHindgeModelNo());
                $door->setHindgeWeight($doorData[$i]->getHindgeWeight());
                $door->setPosFromTop($doorData[$i]->getPosFromTop());
                $door->setHindgeSize($doorData[$i]->getHindgeSize());
                $door->setBackSet($doorData[$i]->getBackSet());
                $door->setHandleBolt($doorData[$i]->getHandleBolt());
                $door->setPosFromTopMach($doorData[$i]->getPosFromTopMach());
                $door->setVerticalRod($doorData[$i]->getVerticalRod());
                $door->setIsLabel($doorData[$i]->getIsLabel());
                $door->setLabels($doorData[$i]->getLabels());
                $door->setFacePreps($doorData[$i]->getFacePreps());
                $door->setBlockingCharge($doorData[$i]->getBlockingCharge());
                $door->setBlockingUpcharge($doorData[$i]->getBlockingUpcharge());
                $door->setLumFee($doorData[$i]->getLumFee());
                $door->setComment($doorData[$i]->getComment());
                $door->setCreatedAt($datime);
                $door->setUpdatedAt($datime);
                $em->persist($door);
                $em->flush();
                $this->cloneSkinData($clonedQuoteId, $doorData[$i]->getId(), $door->getId(), $datime);
            }
        }
    }
    
    private function getCustomerNameByQuote($quoteId) {
        $quoteRecord = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneById($quoteId);
        $custId = $quoteRecord->getCustomerId();
        return $this->getCustomerNameById($custId);

    }
    
    private function getCustomerNameById($customer_id) {
        if (!empty($customer_id)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $customer_id));
            $customerName =  $profileObj->getFname();
            $custArr = explode(' ', $customerName);
            if (!empty($custArr)) {
                return $custArr[0];
            }
        }
    }
    
    private function replaceShortcodeFromMessage($custName,$userName,$projName,$message) {
        $shortCodes = array('{first_name_customer}','{project_name}','{user_first_name}');
        $values  = array($custName, $projName, $userName);
        return str_replace($shortCodes, $values, $message);
    }
    
    private function getPlywoodIds($ply) {
        $ids = array();
        for ($i=0;$i<count($ply);$i++) {
            $ids[] = $ply[$i]->getId();
        }
        return $ids;
    }
    
    private function getVeneerIds($ven) {
        $ids = array();
        for ($i=0;$i<count($ven);$i++) {
            $ids[] = $ven[$i]->getId();
        }
        return $ids;
    }
    
    private function wordLimit($text) {
         if (!empty($text)) {
             if (strlen($text) > 20) {
               $txt = substr($text, 0,20).'...';
             } else {
                 $txt = $text;
             }
             return $txt;
         }
    }
    
    private function getLineitemAttachmentsList($request, $qId) {
        $attachmentArr = array();
        $ply = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId'=>$qId));
        $ven = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId'=>$qId));
        if (!empty($ply)) {
            $plyIds = $this->getPlywoodIds($ply);
            $plyFiles = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('attachableid'=>$plyIds, 'attachabletype' => 'plywood'));
        }
        if (!empty($ven)) {
            $venIds = $this->getVeneerIds($ven);
            $venFiles = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('attachableid'=>$venIds, 'attachabletype' => 'veneer'));
        }
        $i=0;
        if (!empty($plyFiles) || !empty($venFiles)) {
            if (!empty($plyFiles)) {
                foreach ($plyFiles as $p) {
                    $attachmentArr[$i]['id'] = $p->getId();
                    $attachmentArr[$i]['name'] = $this->wordLimit($p->getOriginalName());
                    $attachmentArr[$i]['origName'] = $p->getOriginalName();
                    $attachmentArr[$i]['url'] = $request->getHost().'/'.$request->getBasePath().'/uploads/'.$p->getFileName();
                    $i++;
                }
            }
            if (!empty($venFiles)) {
                foreach ($venFiles as $v) {
                    $attachmentArr[$i]['id'] = $v->getId();
                    $attachmentArr[$i]['name'] = $this->wordLimit($v->getOriginalName());
                    $attachmentArr[$i]['origName'] = $v->getOriginalName();
                    $attachmentArr[$i]['url'] = $request->getHost().'/'.$request->getBasePath().'/uploads/'.$v->getFileName();
                    $i++;
                }
            }
            return $attachmentArr;
        }
    }
    
    private function createOrderLineitemPDF($html,$pdfName, $request) {
        $fs = new Filesystem();
        $snappy = new Pdf(  '../vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=$pdfName');
        $snappy->generateFromHtml($html, $pdfName);
        $fs->chmod($pdfName, 0777);
        return 'http://'.$request->getHost().'/'.$request->getBasePath().'/'.$pdfName;
    }
    
    private function createMessageToBeSent($msg, $cmt) {
        $nStr = "Please call me with any questions.\r\n\r\n".$cmt."\r\n\r\n";
        return str_replace("Please call me with any questions.",$nStr, $msg);
    }
    
    private function getAttachmentUrls($idArr, $request) {
        $urlArr = array();
        $attachments = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('id'=>$idArr));
        if (!empty($attachments)) {
            //print_r($attachments);die;
            $i=0;
            foreach ($attachments as $a) {
                $urlArr[$i] = 'http://'.$request->getHost().'/'.$request->getBasePath().'/uploads/'.$a->getFileName();
                $i++;
            }
        }
        return $urlArr;
    }
    
    private function strignfyCHKArr($chkVal) {
        return implode(',', $chkVal);
    }
    
    private function saveSentEmailDetails($qId, $currUserId, $cmt, $chkVal, $datime) {
        $custId = $this->getCustomerIdByQuoteId($qId);
        $chkBxVal = $this->strignfyCHKArr($chkVal);
        $em = $this->getDoctrine()->getManager();
        $sentQuote = new SentQuotes();
        $sentQuote->setQuote($qId);
        $sentQuote->setCurrLoggedinUser($currUserId);
        $sentQuote->setCustomer($custId);
        $sentQuote->setComment($cmt);
        $sentQuote->setAttachment($chkBxVal);
        $sentQuote->setCreatedAt($datime);
        $sentQuote->setUpdatedAt($datime);
        $em->persist($sentQuote);
        $em->flush();
        return;
    }
    
    private function getEstimateDateFormate($date) {
        $dateArr =  explode('-', explode('T',$date)[0]);
        return $d = $dateArr[1].'/'.$dateArr[2].'/'.$dateArr[0];
    }
    
    private function getOrderDataById($qId) {
        return $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneById($qId);
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

    
    private function getVeneerslistbyQuoteId($qId) {
        $lineItem = array();
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $qId,'isActive'=>1));
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $qId,'isActive'=>1));
        $doorRecords = $this->getDoctrine()->getRepository('AppBundle:Doors')->findBy(array('quoteId' => $qId, 'status'=> 1));
        $i=0;
        if (!empty($plywoodRecords) || !empty($veneerRecords) || !empty($doorRecords)) {
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
                    $lineItem[$i]['unitPrice'] = $p->getTotalcostPerPiece();
                    $lineItem[$i]['totalPrice'] = $p->getTotalCost();
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
                    $lineItem[$i]['unitPrice'] = $v->getTotCostPerPiece();
                    $lineItem[$i]['totalPrice'] = $v->getTotalCost();
                    $i++;
                }
            }
            if (!empty($doorRecords)) {
                foreach ($doorRecords as $d) {
                    $lineItem[$i]['id'] = $d->getId();
                    $lineItem[$i]['type'] = 'door';
                    $lineItem[$i]['url'] = 'door/edit-door';
                    $lineItem[$i]['quantity'] = $d->getQty();
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($this->getSpeciesIdByDoorId($d->getId()));
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($this->getPatternIdByDoorId($d->getId()));
                    $lineItem[$i]['grade'] = $this->getGradeNameById($this->getGradeIdByDoorId($d->getId()));
                    $lineItem[$i]['back'] = 'NA';//$this->getBackNameById($this->getBackerIdByDoorId($d->getId()));
                    $lineItem[$i]['thickness'] = 'NA';//$this->getThicknessNameById($d->getThicknessId());
                    $lineItem[$i]['width'] = $d->getWidth();
                    $lineItem[$i]['length'] = $d->getLength();
                    $lineItem[$i]['core'] = 'NA';//$this->getCoreNameById($d->getCoreTypeId());
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = '0';
                    $lineItem[$i]['totalPrice'] = '0';
                    $i++;
                }
            }
            return $lineItem;
        }
    }
    
    private function getCustomerEmailById($customer_id) {
        if (!empty($customer_id)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $customer_id));
            $customerName =  $profileObj->getEmail();
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
    
    private function getShipMethodNamebyId($hipmethod_id) {
        $shippmethodRecord = $this->getDoctrine()->getRepository('AppBundle:ShippingMethods')->findOneById($hipmethod_id);
        if (!empty($shippmethodRecord)) {
            return $shippmethodRecord->getName();
        }
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
    
    private function cloneAttachments($doorId, $newdoorId, $type, $datime) {
        $files = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('attachabletype' => $type, 'attachableid' => $doorId));
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
    
    private function cloneSkinData($clonedQuoteId, $oldDoorId, $newDoorId, $datime) {
        $skinData = $this->getDoctrine()->getRepository('AppBundle:Skins')->findBy(array('doorId' => $oldDoorId));
        if (!empty($skinData)) {
            $em = $this->getDoctrine()->getManager();
            for ($i=0; $i< count($skinData); $i++) {
                $skin = new Skins();
                $skin->setQuoteId($clonedQuoteId);
                $skin->setDoorId($newDoorId);
                $skin->setSkinType($skinData[$i]->getSkinType());
                $skin->setSpecies($skinData[$i]->getSpecies());
                $skin->setGrain($skinData[$i]->getGrain());
                $skin->setGrainDir($skinData[$i]->getGrainDir());
                $skin->setPattern($skinData[$i]->getPattern());
                $skin->setGrade($skinData[$i]->getGrade());
                $skin->setLeedReqs($skinData[$i]->getLeedReqs());
                $skin->setManufacturer($skinData[$i]->getManufacturer());
                $skin->setColor($skinData[$i]->getColor());
                $skin->setEdge($skinData[$i]->getEdge());
                $skin->setThickness($skinData[$i]->getThickness());
                $skin->setSkinTypeBack($skinData[$i]->getSkinTypeBack());
                $skin->setBackSpecies($skinData[$i]->getBackSpecies());
                $skin->setBackGrain($skinData[$i]->getBackGrain());
                $skin->setBackGrainDir($skinData[$i]->getBackGrainDir());
                $skin->setBackPattern($skinData[$i]->getBackPattern());
                $skin->setBackGrade($skinData[$i]->getBackGrade());
                $skin->setBackLeedReqs($skinData[$i]->getBackLeedReqs());
                $skin->setBackManufacturer($skinData[$i]->getBackManufacturer());
                $skin->setBackColor($skinData[$i]->getBackColor());
                $skin->setBackEdge($skinData[$i]->getBackEdge());
                $skin->setBackThickness($skinData[$i]->getBackThickness());
                $em->persist($skin);
                $em->flush();
                $this->cloneAttachments($oldDoorId, $newDoorId, 'door', $datime);
            }
        }
    }
    
    
    private function getCustomerIdByQuoteId($qId) {
        $quoteRecord = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findById($qId);
        if (!empty($quoteRecord)) {
            return $quoteRecord[0]->getCustomerId();
        }
    }
    
    private function updateQuoteData($quoteId) {
        $salesTaxRate = 0;
        $salesTaxAmount = 0;
        $quoteSubTotal = $this->getPlywoodSubTotalByQuoteId($quoteId) + $this->getVeneerSubTotalByQuoteId($quoteId);
        $quoteData = $this->getOrderDataById($quoteId);
        $shipAddId = $quoteData->getShipAddId();
        $expFee = $quoteData->getExpFee();
        $discount = $quoteData->getDiscount();
        if (!empty($shipAddId)) {
            $salesTaxRate = $this->getSalesTaxRateByAddId($shipAddId);
        }
        $salesTaxAmount = (($quoteSubTotal + $expFee - $discount ) * ($salesTaxRate)) / 100;
        $shipCharge = $this->getShippingChargeByAddId($shipAddId);
        $lumFee = $this->getPlywoodLumberFeeByQuoteId($quoteId) + $this->getVeneerLumberFeeByQuoteId($quoteId);
        $projectTotal = ($quoteSubTotal + $expFee - $discount + $salesTaxAmount + $shipCharge + $lumFee);
        $this->saveQuoteCalculatedData($quoteId, $quoteSubTotal, $salesTaxAmount, $shipCharge, $lumFee, $projectTotal);
    }
    
    private function saveQuoteCalculatedData($quoteId, $quoteSubTotal, $salesTaxAmount, $shipCharge, $lumFee, $projectTotal) {
        $em = $this->getDoctrine()->getManager();
        $quote = $em->getRepository(Quotes::class)->findOneById($quoteId);
        $datime = new \DateTime('now');
        if (!empty($quote)) {
            $quote->setQuoteTot($quoteSubTotal);
            $quote->setSalesTax($salesTaxAmount);
            $quote->setShipCharge($shipCharge);
            $quote->setLumFee($lumFee);
            $quote->setProjectTot($projectTotal);
            $quote->setUpdatedAt($datime);
            $em->persist($quote);
            $em->flush();
        }
    }
    
    private function getVeneerLumberFeeByQuoteId($quoteId) {
        $lumFee = 0;
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $quoteId,'isActive'=>1));
        if (!empty($veneerRecords)) {
            $i=0;
            foreach ($veneerRecords as $v) {
                $lumFee += ($v->getTotalCost() * $v->getLumberFee()) / 100;
                $i++;
            }
        } else {
            $lumFee = 0;
        }
        return  $lumFee;
    }
    
    private function getPlywoodLumberFeeByQuoteId($quoteId) {
        $lumFee = 0;
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $quoteId,'isActive'=>1));
        if (!empty($plywoodRecords)) {
            $i=0;
            foreach ($plywoodRecords as $p) {
                $lumFee += ($p->getTotalCost() * $p->getLumberFee()) / 100;
                $i++;
            }
        } else {
            $lumFee = 0;
        }
        return $lumFee;
    }
    
    private function getShippingChargeByAddId($shipAddId) {
        $shipCharge = 0;
        $add = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findById($shipAddId);
        if (!empty($add)) {
            $shipCharge = $add[0]->getDeliveryCharge();
        }
        return $shipCharge;
    }
    
    private function getSalesTaxRateByAddId($shipAddId) {
        $salesTaxRate = 0;
        $add = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findById($shipAddId);
        if (!empty($add)) {
            $salesTaxRate = $add[0]->getSalesTaxRate();
        }
        return $salesTaxRate;
    }
    
    private function getPlywoodSubTotalByQuoteId($quoteId) {
        $subtotal = '';
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $quoteId,'isActive'=>1));
        if (!empty($plywoodRecords)) {
            $i=0;
            foreach ($plywoodRecords as $p) {
                $subtotal += $p->getTotalCost();
                $i++;
            }
        } else {
            $subtotal = 0;
        }
        return $subtotal;
    }
    
    private function getVeneerSubTotalByQuoteId($quoteId) {
        $subtotal = '';
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $quoteId,'isActive'=>1));
        if (!empty($veneerRecords)) {
            $i=0;
            foreach ($veneerRecords as $v) {
                $subtotal += $v->getTotalCost();
                $i++;
            }
        } else {
            $subtotal = 0;
        }
        return $subtotal;
    }
    
    private function __arraySortByColumn($array, $index, $order, $natsort=FALSE, $case_sensitive=FALSE) {
        if (is_array($array) && count($array) > 0) {
            foreach (array_keys($array) as $key) {
                $temp[$key] = $array[$key][$index];
            }
            
            if (!$natsort) {                
                if ($order == 'ASC') {
                    asort($temp);
                } else {
                    arsort($temp);
                }
            } else {
                if ($case_sensitive === true) {
                    natsort($temp);
                } else {
                    natcasesort($temp);
                }
                if ($order != 'ASC') {
                    $temp = array_reverse($temp, TRUE);
                }
            }
            foreach (array_keys($temp) as $key) {
                if (is_numeric($key)) {
                    $sorted[] = $array[$key];
                } else {
                    $sorted[$key] = $array[$key];
                }
            }
            return $sorted;
        }
        return $sorted;
//        if(!empty($arr)){
//            $sort_col = array();
//            foreach ($arr as $key=> $row) {
//                $sort_col[$key] = $row[$col];
//            }
//            array_multisort($sort_col, $dir, $arr);
//        }        
//        return $arr;
    }
    
    private function getCompanyById($userid) {
        $profileObj = $this->getDoctrine()
            ->getRepository('AppBundle:Profile')
            ->findOneBy(array('userId' => $userid));
        return $profileObj->getCompany();
    }
    
    private function getSpeciesIdByDoorId($doorId) {
        $skinRecord = $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(array('doorId' => $doorId));
        if (!empty($skinRecord)) {
            return $skinRecord->getSpecies();
        }
    }
    
    private function getPatternIdByDoorId($doorId) {
        $skinRecord = $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(array('doorId' => $doorId));
        if (!empty($skinRecord)) {
            return $skinRecord->getPattern();
        }
    }

    private function getGradeIdByDoorId($doorId) {
        $skinRecord = $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(array('doorId' => $doorId));
        if (!empty($skinRecord)) {
            return $skinRecord->getGrade();
        }
    }
}
