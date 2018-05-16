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
use AppBundle\Entity\DoorCalculator;
use AppBundle\Entity\State;
use AppBundle\Entity\Plywood;
use AppBundle\Entity\Veneer;
use AppBundle\Entity\Doors;
use Knp\Snappy\Pdf;
use Symfony\Component\Filesystem\Filesystem;

class OrderController extends Controller {

    /**
     * @Route("/api/order/getOrders")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getOrdersListAction(Request $request) {
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $orders = $this->getDoctrine()->getRepository('AppBundle:Orders')->findBy(['isActive' => 1], ['id' => 'DESC']);
            if (empty($orders)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no order right now';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the orders list';
                $i = 0;
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
        try {
            $estNo = $request->query->get('est');
            $orderRecords = $this->getRecordsBasedOnEstimateNo($estNo);
            if (empty($orderRecords)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no such order';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived orders list';
                $i = 0;
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
        } catch (Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function getRecordsBasedOnEstimateNo($estNo) {
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL = "select * from orders WHERE est_number like '$estNo%' order by id desc";
        $stmt = $conn->prepare($SQL);
        //$stmt->bindParam(':est',$estNo,PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function formateDate($date) {
        $expDate = explode(' ', $date)[0];
        $expDate1 = explode('-', $expDate);
        return $expDate1[1] . '/' . $expDate1[2] . '/' . $expDate1[0];
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
        $requestColumnName = '';
        if ($columnName == '' || $columnName == 'customer') {
            $columnName = 'u.fname';
        } else if ($columnName == 'estimatorId') {
            $columnName = 'q.controlNumber';
        } else if ($columnName == 'status') {
            $columnName = 's.statusName';
        } else if ($columnName == 'estimatedate') {
            $columnName = 'q.estimatedate';
        } else if ($columnName == 'id') {
            $columnName = 'o.id';
        } else {
            $columnName = 'q.estimatedate';
            $orderBy = 'DESC';
        }
        $query = $this->getDoctrine()->getManager();
        $quotes = $query->createQueryBuilder()
                ->select(['o.id as orderId', 'q.controlNumber', 'q.version', 'q.customerId', 'q.estimatedate', 'q.id',
                    'os.statusId',
                    's.statusName as status',
                    'u.company as companyname', 'u.fname', 'u.lname'
                ])
                ->from('AppBundle:Orders', 'o')
                ->leftJoin('AppBundle:Quotes', 'q', 'WITH', "q.id = o.quoteId")
                ->innerJoin('AppBundle:OrderStatus', 'os', 'WITH', "o.id = os.orderId")
                ->leftJoin('AppBundle:Status', 's', 'WITH', "os.statusId=s.id ")
                ->leftJoin('AppBundle:Profile', 'u', 'WITH', "q.customerId = u.userId")
                ->where("o.isActive = 1 and q.status='Approved' and os.isActive=1")
                ->orderBy($columnName, $orderBy)
                ->getQuery()
                ->getResult()
        ;
        if (empty($quotes)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no order.';
            $statusCode = 422;
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the order list.';
            $quoteList = [];
            for ($i = 0; $i < count($quotes); $i++) {
                $quoteList[$i] = [
                    'id' => $quotes[$i]['id'],
                    'estimateNumber' => 'O-' . $quotes[$i]['controlNumber'] . '-' . $quotes[$i]['version'],
                    'customername' => $quotes[$i]['fname'],
                    'companyname' => $quotes[$i]['companyname'],
                    'orderId' => $quotes[$i]['orderId'],
                    'status' => $quotes[$i]['status'],
                    'estDate' => $this->getEstimateDateFormate($quotes[$i]['estimatedate']),
                ];
            }
            $arrApi['data']['orders'] = $quoteList;
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
//                $quotes = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findBy(array('status'=>['Approved']),array('id'=>'desc'));
                $query = $this->getDoctrine()->getManager();
                $quotes = $query->createQueryBuilder()
                        ->select(['q.id'])
                        ->from('AppBundle:Quotes', 'q')
                        ->leftJoin('AppBundle:Orders', 'o', 'WITH', "q.id = o.quoteId")
                        ->where("o.isActive = 1 and q.status='Approved'")
                        ->orderBy('o.id', 'desc')
                        ->getQuery()
                        ->getResult();
                if (!empty($quotes)) {
                    $quoteId = $quotes[0]['id'];
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
                $arrApi['data']['company'] = $this->getCustomerCompanyById($quoteData->getCustomerId());
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
                $arrApi['data']['quoteSubTot'] = !empty($quoteData->getQuoteTot()) ? str_replace(',', '', number_format($quoteData->getQuoteTot(), 0)) : '00.00';
                $arrApi['data']['expFee'] = !empty($quoteData->getExpFee()) ? str_replace(',', '', number_format($quoteData->getExpFee(), 2)) : '00.00';
                $arrApi['data']['discount'] = !empty($quoteData->getDiscount()) ? str_replace(',', '', number_format($quoteData->getDiscount(), 2)) : '00.00';
                $arrApi['data']['lumFee'] = !empty($quoteData->getLumFee()) ? str_replace(',', '', number_format($quoteData->getLumFee(), 2)) : '00.00';
                $arrApi['data']['shipCharge'] = !empty($quoteData->getShipCharge()) ? str_replace(',', '', number_format($quoteData->getShipCharge(), 2)) : '00.00';
                $arrApi['data']['salesTax'] = !empty($quoteData->getSalesTax()) ? str_replace(',', '', number_format($quoteData->getSalesTax(), 2)) : '00.00';
                $arrApi['data']['projectTot'] = ($quoteData->getProjectTot() != $quoteData->getShipCharge()) ? !empty($quoteData->getProjectTot()) ? str_replace(',', '', number_format($quoteData->getProjectTot(), 2)) : '00.00' : 0;
                $arrApi['data']['lineitems'] = $this->getVeneerslistbyQuoteId($quoteId);
            }
        } catch (Exception $e) {
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
        } catch (Exception $e) {
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
                $messageTemplate = $this->replaceShortcodeFromMessage($custName, $userName, $projName, $this->getMessageTemplateforEmailQuote());
                if (!empty($messageTemplate)) {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully reterived email order data.';
                    $arrApi['data']['id'] = $qId;
                    $arrApi['data']['template'] = $messageTemplate;
                    $attachmentData = $this->getLineitemAttachmentsList($request, $qId);
                    $arrApi['data']['attachments'] = $attachmentData;
                }
            } catch (Exception $e) {
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
            $pdfName = 'uploads/order_screenshots/OrderPDF-' . $qId . '-' . time() . '.pdf';
            $quotePdfUrl = $this->createOrderLineitemPDF($html, $pdfName, $request);
            $newMessage = $this->createMessageToBeSent($msg, $cmt);
            $urls = $this->getAttachmentUrls($chkVal, $request);
            $urls[] = $quotePdfUrl;
            $message = \Swift_Message::newInstance()
                    ->setFrom($currUserEmail)
                    ->setTo($custEmail)
                    ->setSubject('Order Email')
                    ->setBody($newMessage, 'text/plain');
            for ($i = 0; $i < count($urls); $i++) {
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
            } catch (Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function clonePlywoodData($quoteId, $clonedQuoteId, $datime) {
        $ply = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $quoteId));
        if (!empty($ply)) {
            $em = $this->getDoctrine()->getManager();
            for ($i = 0; $i < count($ply); $i++) {
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
        $quote->setControlNumber($this->getLastControlNumber() + 1);
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
        $veneeerData = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $quoteId));
        if (!empty($veneeerData)) {
            $em = $this->getDoctrine()->getManager();
            for ($i = 0; $i < count($veneeerData); $i++) {
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
            for ($i = 0; $i < count($doorData); $i++) {
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
            $customerName = $profileObj->getFname();
            $custArr = explode(' ', $customerName);
            if (!empty($custArr)) {
                return $custArr[0];
            }
        }
    }

    private function replaceShortcodeFromMessage($custName, $userName, $projName, $message) {
        $shortCodes = array('{first_name_customer}', '{project_name}', '{user_first_name}');
        $values = array($custName, $projName, $userName);
        return str_replace($shortCodes, $values, $message);
    }

    private function getPlywoodIds($ply) {
        $ids = array();
        for ($i = 0; $i < count($ply); $i++) {
            $ids[] = $ply[$i]->getId();
        }
        return $ids;
    }

    private function getVeneerIds($ven) {
        $ids = array();
        for ($i = 0; $i < count($ven); $i++) {
            $ids[] = $ven[$i]->getId();
        }
        return $ids;
    }

    private function wordLimit($text) {
        if (!empty($text)) {
            if (strlen($text) > 20) {
                $txt = substr($text, 0, 20) . '...';
            } else {
                $txt = $text;
            }
            return $txt;
        }
    }

    private function getLineitemAttachmentsList($request, $qId) {
        $attachmentArr = array();
        $ply = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $qId));
        $ven = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $qId));
        if (!empty($ply)) {
            $plyIds = $this->getPlywoodIds($ply);
            $plyFiles = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('attachableid' => $plyIds, 'attachabletype' => 'plywood'));
        }
        if (!empty($ven)) {
            $venIds = $this->getVeneerIds($ven);
            $venFiles = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('attachableid' => $venIds, 'attachabletype' => 'veneer'));
        }
        $i = 0;
        if (!empty($plyFiles) || !empty($venFiles)) {
            if (!empty($plyFiles)) {
                foreach ($plyFiles as $p) {
                    $attachmentArr[$i]['id'] = $p->getId();
                    $attachmentArr[$i]['name'] = $this->wordLimit($p->getOriginalName());
                    $attachmentArr[$i]['origName'] = $p->getOriginalName();
                    $attachmentArr[$i]['url'] = $request->getHost() . '/' . $request->getBasePath() . '/uploads/' . $p->getFileName();
                    $i++;
                }
            }
            if (!empty($venFiles)) {
                foreach ($venFiles as $v) {
                    $attachmentArr[$i]['id'] = $v->getId();
                    $attachmentArr[$i]['name'] = $this->wordLimit($v->getOriginalName());
                    $attachmentArr[$i]['origName'] = $v->getOriginalName();
                    $attachmentArr[$i]['url'] = $request->getHost() . '/' . $request->getBasePath() . '/uploads/' . $v->getFileName();
                    $i++;
                }
            }
            return $attachmentArr;
        }
    }

    private function createOrderLineitemPDF($html, $pdfName, $request) {
        $fs = new Filesystem();
        $snappy = new Pdf('../vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=$pdfName');
        $snappy->generateFromHtml($html, $pdfName);
        $fs->chmod($pdfName, 0777);
        return 'http://' . $request->getHost() . '/' . $request->getBasePath() . '/' . $pdfName;
    }

    private function createMessageToBeSent($msg, $cmt) {
        $nStr = "Please call me with any questions.\r\n\r\n" . $cmt . "\r\n\r\n";
        return str_replace("Please call me with any questions.", $nStr, $msg);
    }

    private function getAttachmentUrls($idArr, $request) {
        $urlArr = array();
        $attachments = $this->getDoctrine()->getRepository('AppBundle:Files')->findBy(array('id' => $idArr));
        if (!empty($attachments)) {
            //print_r($attachments);die;
            $i = 0;
            foreach ($attachments as $a) {
                $urlArr[$i] = 'http://' . $request->getHost() . '/' . $request->getBasePath() . '/uploads/' . $a->getFileName();
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
        $dateArr = explode('-', explode('T', $date)[0]);
        return $d = $dateArr[1] . '/' . $dateArr[2] . '/' . $dateArr[0];
    }

    private function getOrderDataById($qId) {
        return $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneById($qId, ['estimatedate' => 'DESC']);
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
        $query = $this->getDoctrine()->getManager();
        $plywoodRecords = $query->createQueryBuilder()
                ->select(['p.id, p.quantity, p.speciesId, p.patternMatch, p.gradeId,p.backerId,p.finishThickId,p.finishThickType,
                p.finThickFraction, p.plywoodWidth, p.plywoodLength, p.coreType,p.sellingPrice,p.totalCost,p.widthFraction,
                p.lengthFraction,p.grainPatternId,p.backOrderEstNo, s.statusName'])
                ->from('AppBundle:Plywood', 'p')
                ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = p.id")
                ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
                ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND p.quoteId = :quoteId and lis.statusId=1")
                ->orderBy('p.id', 'ASC')
                ->setParameter('quoteId', $qId)
                ->setParameter('lineItemType', 'Plywood')
                ->getQuery()
                ->getResult();
        $veneerRecords = $query->createQueryBuilder()
                ->select(['v.id, v.quantity, v.speciesId, v.patternId, v.gradeId, v.backer, v.thicknessId,v.width, v.length,v.coreTypeId,v.sellingPrice,v.totalCost,v.widthFraction, v.lengthFraction,v.grainPatternId,v.backOrderEstNo, s.statusName'])
                ->from('AppBundle:Veneer', 'v')
                ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = v.id")
                ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
                ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND v.quoteId = :quoteId and lis.statusId=1")
                ->orderBy('v.id', 'ASC')
                ->setParameter('quoteId', $qId)
                ->setParameter('lineItemType', 'Veneer')
                ->getQuery()
                ->getResult();
        $doorRecords = $query->createQueryBuilder()
                ->select(['d.id, d.qty, d.width, d.length,d.widthFraction, d.lengthFraction,d.finishThickType,d.finishThickId,d.finThickFraction,d.backOrderEstNo, s.statusName'])
                ->from('AppBundle:Doors', 'd')
                ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = d.id")
                ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
                ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND d.quoteId = :quoteId and lis.statusId=1")
                ->orderBy('d.id', 'ASC')
                ->setParameter('quoteId', $qId)
                ->setParameter('lineItemType', 'Door')
                ->getQuery()
                ->getResult();
        $i = 0;
        if (!empty($plywoodRecords) || !empty($veneerRecords) || !empty($doorRecords)) {
            if (!empty($plywoodRecords)) {
                foreach ($plywoodRecords as $p) {
                    if ($p['finishThickType'] == 'inch') {
                        if ($p['finishThickId'] > 0) {
                            $thickness = $p['finishThickId'] . ($p['finThickFraction'] != 0 ? ' ' . $this->float2rat($p['finThickFraction']) : '') . '"';
                        } else {
                            $thickness = $this->float2rat($p['finThickFraction']) . '"';
                        }
                    } else {
                        $thickness = $p['finishThickId'] . ' ' . $p['finishThickType'];
                    }
                    $lineItem[$i]['id'] = $p['id'];
                    $lineItem[$i]['type'] = 'plywood';
                    $lineItem[$i]['url'] = 'line-item/edit-plywood';
                    $lineItem[$i]['quantity'] = $p['quantity'];
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($p['speciesId']);
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($p['patternMatch']);
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($p['gradeId']))[0];
                    $lineItem[$i]['back'] = $this->getBackNameById($p['backerId']);
                    $lineItem[$i]['thickness'] = $thickness;
                    $lineItem[$i]['width'] = $p['plywoodWidth'];
                    $lineItem[$i]['length'] = $p['plywoodLength'];
                    $lineItem[$i]['core'] = $this->getCoreNameById($p['coreType']);
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $p['sellingPrice'];
                    $lineItem[$i]['totalPrice'] = $p['totalCost'];
                    $lineItem[$i]['widthFraction'] = $this->float2rat($p['widthFraction']);
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($p['lengthFraction']);
                    $lineItem[$i]['grain'] = $this->getGrainPattern($p['grainPatternId']);
                    $lineItem[$i]['isGreyedOut'] = $p['statusName'];
                    $lineItem[$i]['greyedOutClass'] = ($p['statusName'] == 'LineItemBackOrder') ? 'greyedOut' : '';
                    $lineItem[$i]['greyedOutEstNo'] = $p['backOrderEstNo'];
                    $i++;
                }
            }
            if (!empty($veneerRecords)) {
                foreach ($veneerRecords as $v) {
                    $lineItem[$i]['id'] = $v['id'];
                    $lineItem[$i]['type'] = 'veneer';
                    $lineItem[$i]['url'] = 'line-item/edit-veneer';
                    $lineItem[$i]['quantity'] = $v['quantity'];
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($v['speciesId']);
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($v['patternId']);
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($v['gradeId']))[0];
                    $lineItem[$i]['back'] = $this->getBackNameById($v['backer']);
                    $lineItem[$i]['thickness'] = $this->getThicknessNameById($v['thicknessId']);
                    $lineItem[$i]['width'] = $v['width'];
                    $lineItem[$i]['length'] = $v['length'];
                    $lineItem[$i]['core'] = $this->getCoreNameById($v['coreTypeId']);
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $v['sellingPrice'];
                    $lineItem[$i]['totalPrice'] = $v['totalCost'];
                    $lineItem[$i]['widthFraction'] = $this->float2rat($v['widthFraction']);
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($v['lengthFraction']);
                    $lineItem[$i]['grain'] = $this->getGrainPattern($v['grainPatternId']);
                    $lineItem[$i]['isGreyedOut'] = $v['statusName'];
                    $lineItem[$i]['greyedOutClass'] = ($v['statusName'] == 'LineItemBackOrder') ? 'greyedOut' : '';
                    $lineItem[$i]['greyedOutEstNo'] = $v['backOrderEstNo'];
                    $i++;
                }
            }
            if (!empty($doorRecords)) {
                foreach ($doorRecords as $d) {
                    $doorCosts = $this->getDoctrine()->getRepository('AppBundle:DoorCalculator')->
                            findOneBy(['doorId' => $d['id']]);
                    if (!empty($doorCosts)) {
                        $totalcostPerPiece = !empty($doorCosts->getSellingPrice()) ? $doorCosts->getSellingPrice() : 0;
                        $totalCost = !empty($doorCosts->getTotalCost()) ? $doorCosts->getTotalCost() : 0;
                    } else {
                        $totalcostPerPiece = 0;
                        $totalCost = 0;
                    }
                    if ($d['finishThickType'] == 'inch') {
                        if ($d['finishThickId'] > 0) {
                            $thickness = $d['finishThickId'] . ($d['finThickFraction'] != 0 ? ' ' . $this->float2rat($d['finThickFraction']) : '') . '"';
                        } else {
                            $thickness = $this->float2rat($d['finThickFraction']) . '"';
                        }
                    } else {
                        $thickness = $d['finishThickId'] . ' ' . $d['finishThickType'];
                    }
                    $lineItem[$i]['id'] = $d['id'];
                    $lineItem[$i]['type'] = 'door';
                    $lineItem[$i]['url'] = 'door/edit-door';
                    $lineItem[$i]['quantity'] = $d['qty'];
                    if ($this->getSpeciesNameById($this->getSpeciesIdByDoorId($d['id'])) == null) {
                        $lineItem[$i]['species'] = 'Other';
                    } else {
                        $lineItem[$i]['species'] = $this->getSpeciesNameById($this->getSpeciesIdByDoorId($d['id']));
                    }
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($this->getPatternIdByDoorId($d['id']));
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($this->getGradeIdByDoorId($d['id'])))[0];
                    $lineItem[$i]['back'] = 'NA'; //$this->getBackNameById($this->getBackerIdByDoorId($d->getId()));
                    $lineItem[$i]['thickness'] = $thickness;
                    $lineItem[$i]['width'] = $d['width'];
                    $lineItem[$i]['length'] = $d['length'];
                    $lineItem[$i]['core'] = 'NA'; //$this->getCoreNameById($d->getCoreTypeId());
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $totalcostPerPiece;
                    $lineItem[$i]['totalPrice'] = $totalCost;
                    $lineItem[$i]['widthFraction'] = $this->float2rat($d['widthFraction']);
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($d['lengthFraction']);
                    $lineItem[$i]['grain'] = $this->getGrainPattern($d['id'], 'door');
                    $lineItem[$i]['isGreyedOut'] = $d['statusName'];
                    $lineItem[$i]['greyedOutClass'] = ($d['statusName'] == 'LineItemBackOrder') ? 'greyedOut' : '';
                    $lineItem[$i]['greyedOutEstNo'] = $d['backOrderEstNo'];
                    $i++;
                }
            }
            return $lineItem;
        }
    }

    private function getGrainPattern($id, $type = '') {
        if ($type == 'door') {
            $grainId = $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(['id' => $id]);
            if (!empty($grainId)) {
                $id = $grainId->getGrain();
            } else {
                $id = 0;
            }
        }
        $data = $this->getDoctrine()->getRepository('AppBundle:GrainDirection')->findOneBy(['id' => $id]);
        if (!empty($data)) {
            return $data->getName();
        } else {
            return '';
        }
    }

    private function getCustomerEmailById($customer_id) {
        if (!empty($customer_id)) {
            $profileObj = $this->getDoctrine()
                    ->getRepository('AppBundle:Profile')
                    ->findOneBy(array('userId' => $customer_id));
            $customerName = $profileObj->getEmail();
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
            $customerName = $profileObj->getFname();
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
            for ($i = 0; $i < count($files); $i++) {
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
            for ($i = 0; $i < count($skinData); $i++) {
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
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $quoteId, 'isActive' => 1));
        if (!empty($veneerRecords)) {
            $i = 0;
            foreach ($veneerRecords as $v) {
                $lumFee += ($v->getTotalCost() * $v->getLumberFee()) / 100;
                $i++;
            }
        } else {
            $lumFee = 0;
        }
        return $lumFee;
    }

    private function getPlywoodLumberFeeByQuoteId($quoteId) {
        $lumFee = 0;
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $quoteId, 'isActive' => 1));
        if (!empty($plywoodRecords)) {
            $i = 0;
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
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $quoteId, 'isActive' => 1));
        if (!empty($plywoodRecords)) {
            $i = 0;
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
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $quoteId, 'isActive' => 1));
        if (!empty($veneerRecords)) {
            $i = 0;
            foreach ($veneerRecords as $v) {
                $subtotal += $v->getTotalCost();
                $i++;
            }
        } else {
            $subtotal = 0;
        }
        return $subtotal;
    }

    private function __arraySortByColumn($array, $index, $order, $natsort = FALSE, $case_sensitive = FALSE) {
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

    private function getCustomerCompanyById($customer_id) {
        $com = '';
        if (!empty($customer_id)) {
            $profileObj = $this->getDoctrine()
                    ->getRepository('AppBundle:Profile')
                    ->findOneBy(array('userId' => $customer_id));
            if (!empty($profileObj->getCompany())) {
                $com = $profileObj->getCompany();
            }
        }
        return $com;
    }

    private function float2rat($num = 0.0, $err = 0.001) {
        if ($err <= 0.0 || $err >= 1.0) {
            $err = 0.001;
        }

        $sign = ($num > 0) ? 1 : (($num < 0) ? - 1 : 0);

        if ($sign === - 1) {
            $num = abs($num);
        }

        if ($sign !== 0) {
            // $err is the maximum relative $err; convert to absolute
            $err *= $num;
        }

        $n = (int) floor($num);
        $num -= $n;

        if ($num < $err) {
            return (string) ($sign * $n);
        }

        if (1 - $err < $num) {
            return (string) ($sign * ($n + 1));
        }

        // The lower fraction is 0/1
        $lower_n = 0;
        $lower_d = 1;

        // The upper fraction is 1/1
        $upper_n = 1;
        $upper_d = 1;

        while (true) {
            // The middle fraction is ($lower_n + $upper_n) / (lower_d + $upper_d)
            $middle_n = $lower_n + $upper_n;
            $middle_d = $lower_d + $upper_d;

            if ($middle_d * ($num + $err) < $middle_n) {
                // real + $err < middle : middle is our new upper
                $upper_n = $middle_n;
                $upper_d = $middle_d;
            } elseif ($middle_n < ($num - $err) * $middle_d) {
                // middle < real - $err : middle is our new lower
                $lower_n = $middle_n;
                $lower_d = $middle_d;
            } else {
                // Middle is our best fraction
                return (string) (($n * $middle_d + $middle_n) * $sign) . '/' . (string) $middle_d;
            }
        }

        return '0'; // should be unreachable.
    }

    private function convertMmToInches($mm) {
        return $mm * 0.0393701;
    }

    /**
     * @Route("/api/order/searchOrder")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function searchOrderAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
//        $pageNo = $data->get('current_page');
//        $limit = $data->get('limit');
//        $sortBy = $data->get('sort_by');
//        $order = $data->get('order');
        $searchVal = trim($data->get('searchVal'));
        $startDate = $data->get('startDate');
        $endDate = $data->get('endDate');
        $type = $data->get('type');
//        $offset = ($pageNo - 1)  * $limit;
        if (false) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing.';
            $statusCode = 422;
        } else {
            try {
                $searchType = $this->checkIfSearchValIsEstOrCompany($searchVal);
                $condition = "o.isActive = :active and q.status= :status and os.statusId!='' and os.isActive=1 ";
                $concat = ' and ';
                if ($searchType == 'estNo') {
                    $estimate = explode('-', $searchVal);
                    $condition .= $concat . "q.controlNumber= :searchVal ";
                    $keyword = $estimate[1];
//                    $concat = " AND ";
                } else if ($searchType == 'company') {
                    if ($type == 'status') {
                        if ($searchVal != 'all') {
                            $keyword = $searchVal;
                            $condition .= $concat . "os.statusId = :searchVal ";
//                            $concat = " AND ";
                        }
                    } else {
                        $keyword = '%' . $searchVal . '%';
                        $condition .= $concat . "u.company Like :searchVal ";
//                        $concat = " AND ";
                    }
                }
                if (!empty($startDate) && !empty($endDate)) {
                    $condition = $condition . $concat . " q.estimatedate >= :from AND q.estimatedate <= :to ";
                } else if (!empty($startDate) && empty($endDate) || ($startDate == $endDate && !empty($endDate) && !empty($startDate))) {
                    $condition = $condition . $concat . " q.estimatedate >= :from ";
                } else if (empty($startDate) && !empty($endDate)) {
                    $condition = $condition . $concat . " q.estimatedate <= :to ";
                }
                $query = $this->getDoctrine()->getManager();
                $query1 = $query->createQueryBuilder()
                        ->select(['o.id as orderId', 'q.controlNumber', 'q.version', 'q.customerId', 'q.estimatedate', 'q.id',
                            'os.statusId',
                            's.statusName as status',
                            'u.company as companyname', 'u.fname', 'u.lname'
                        ])
                        ->from('AppBundle:Orders', 'o')
                        ->leftJoin('AppBundle:Quotes', 'q', 'WITH', "q.id = o.quoteId")
                        ->innerJoin('AppBundle:OrderStatus', 'os', 'WITH', "o.id = os.orderId")
                        ->leftJoin('AppBundle:Status', 's', 'WITH', "os.statusId=s.id ")
                        ->leftJoin('AppBundle:Profile', 'u', 'WITH', "q.customerId = u.userId")
                        ->where($condition)
                        ->setParameter('active', 1)
                        ->setParameter('status', 'Approved');
                if ($searchVal != 'all') {
                    $query1->setParameter('searchVal', $keyword);
                }
                if (!empty($startDate) && !empty($endDate)) {
                    $query1->setParameter('from', date('Y-m-d', strtotime($startDate)))
                            ->setParameter('to', date('Y-m-d', strtotime($endDate)));
                } else if (!empty($startDate) && empty($endDate) || ($startDate == $endDate && !empty($endDate) && !empty($startDate))) {
                    $query1->setParameter('from', date('Y-m-d', strtotime($startDate)));
                } else if (empty($startDate) && !empty($endDate)) {
                    $query1->setParameter('to', date('Y-m-d', strtotime($endDate)));
                }
                $quotes = $query1->orderBy('q.estimatedate', 'DESC')->getQuery()->getResult();
//                $quotes=$query1->getQuery()->getSQL();print_r($quotes);die;
                if (empty($quotes)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'There is no order.';
                    $statusCode = 422;
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully retreived the order list.';
                    $quoteList = [];
                    for ($i = 0; $i < count($quotes); $i++) {
                        $quoteList[$i] = [
                            'id' => $quotes[$i]['id'],
                            'estimateNumber' => 'O-' . $quotes[$i]['controlNumber'] . '-' . $quotes[$i]['version'],
                            'customername' => $quotes[$i]['fname'],
                            'companyname' => $quotes[$i]['companyname'],
                            'orderId' => $quotes[$i]['orderId'],
                            'status' => $quotes[$i]['status'],
                            'estDate' => $this->getEstimateDateFormate($quotes[$i]['estimatedate']),
                        ];
                    }
                    $arrApi['data']['orders'] = $quoteList;
                }
                return new JsonResponse($arrApi, $statusCode);
            } catch (Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function checkIfSearchValIsEstOrCompany($searchVal) {
        $type = '';
        if (preg_match_all("/(.)(-)(\\d+)/", $searchVal, $matches)) {
            $type = 'estNo';
        } else {
            $type = 'company';
        }
        return $type;
    }

    /**
     * @Route("/api/status/orderStatusList")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getOrderStatusListAction(Request $request) {
        $arrApi = [];
        $statusCode = 200;
        $status = $this->getStatusList('Order');
        if (!empty($status)) {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived order status list!';
            foreach ($status as $v) {
                $arrApi['data'][] = [
                    'id' => $v->getId(),
                    'statusName' => $v->getStatusName()
                ];
            }
        } else {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'The status list does not exists';
            $statusCode = 422;
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/status/quoteStatusList")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getQuoteStatusListAction(Request $request) {
        $arrApi = [];
        $statusCode = 200;
        $status = $this->getStatusList('Quote');

        if (!empty($status)) {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived quote status list!';
            foreach ($status as $v) {
                if ($v->getId() != 9) {
                    $arrApi['data'][] = [
                        'id' => $v->getId(),
                        'statusName' => $v->getStatusName()
                    ];
                }
            }
        } else {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'The status list does not exists';
            $statusCode = 422;
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function getStatusList($type) {
        $status = $this->getDoctrine()->getRepository('AppBundle:Status')->findBy(['type' => $type, 'isActive' => 1], ['statusName' => 'ASC']);
        return $status;
    }

    /**
     * @Route("/api/order/printOrderTicket")
     * @Method("POST")
     * params: None
     */
    public function printOrderTicketAction(Request $request) {

        $arrApi = [];
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $quoteId = !empty($data->get('quote_id')) ? trim($data->get('quote_id')) : '';
        $plywoodData = $this->getPlywoodDataByQuoteId($quoteId);
        $veneerData = $this->getVeneerDataByQuoteId($quoteId);
//        $doorData= $this->getDoorDataByQuoteId($quoteId);
        $arr = array_merge($veneerData, $plywoodData);
        $final = $this->arraySortByColumn($arr, 'lineItemNum');
//        print_r($final);die;
        $images_destination = $this->container->getParameter('images_destination');
//        $veneerHtml = '';
//        $plywoodHtml='';
        $lineItemHTML = '';
        if (!empty($final)) {
            foreach ($final as $v) {
                if (!empty($v['type']) && $v['type'] == 'Plywood') {
                    $lineItemHTML .= $this->getOrderTicketPlywoodHTML($v, $images_destination);
                }
                if (!empty($v['type']) && $v['type'] == 'Veneer') {
                    $lineItemHTML .= $this->getOrderTicketVeneerHTML($v, $images_destination);
                }
            }
        }
        $html = $this->getOrderTicketHTML($lineItemHTML);
        
        return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($html, ['orientation' => 'Landscape', 'default-header' => false]), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="Test.pdf"']);
    }

    private function getPlywoodDataByQuoteId($quoteId) {
        if (!empty($quoteId)) {
            $query = $this->getDoctrine()->getManager();
            $result = $query->createQueryBuilder()
                    ->select(['v.quantity', 'v.plywoodWidth as width', 'v.plywoodLength as length', 'v.comments', 'v.speciesId', 'v.topEdge', 'v.bottomEdge',
                        'v.rightEdge', 'v.leftEdge', "v.finishThickId as pThicknessName", 'v.finishThickType', "'Plywood' as type",
                        "v.finThickFraction", "v.thickness as panelThicknessName", 'v.lineItemNum', 'v.isSequenced'])
                    ->from('AppBundle:Plywood', 'v')
                    ->leftJoin('AppBundle:Quotes', 'q', 'WITH', 'v.quoteId = q.id')
                    ->addSelect(['q.refNum', 'q.deliveryDate'])
                    ->leftJoin('AppBundle:Profile', 'u', 'WITH', "q.customerId = u.userId")
                    ->addSelect(['u.company as username'])
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
                    ->addSelect(['o.orderDate as orderDate', 'o.estNumber as estNumber'])
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
                    ->where('v.quoteId = ' . $quoteId)
                    ->getQuery()
                    ->getResult();
//                ->getSQL();            
        } else {
            $result = [];
        }
        return $result;
    }

    private function getVeneerDataByQuoteId($quoteId) {
        if (!empty($quoteId)) {
            $query = $this->getDoctrine()->getManager();
            $result = $query->createQueryBuilder()
                    ->select(['v.quantity', 'v.width', 'v.length', 'v.comments', 'v.sequenced', 'v.lineItemNum', "'Veneer' as type"])
                    ->from('AppBundle:Veneer', 'v')
                    ->leftJoin('AppBundle:Quotes', 'q', 'WITH', 'v.quoteId = q.id')
                    ->addSelect(['q.refNum', 'q.deliveryDate'])
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
                    ->leftJoin('AppBundle:Backer', 'b', 'WITH', "v.backer = b.id")
                    ->addSelect(['b.name as backerName'])
                    ->leftJoin('AppBundle:GrainDirection', 'gd', 'WITH', "v.grainDirectionId = gd.id")
                    ->addSelect(['gd.name as grainDirectionName'])
                    ->leftJoin('AppBundle:Orders', 'o', 'WITH', "q.id = o.quoteId")
                    ->addSelect(['o.orderDate as orderDate', 'o.estNumber as estNumber'])
                    ->where('v.quoteId = ' . $quoteId)
                    ->getQuery()
                    ->getResult();
        } else {
            $result = [];
        }
        return $result;
    }

    private function getDoorDataByQuoteId($quoteId) {
        if (!empty($quoteId)) {
//            $query = $this->getDoctrine()->getManager();
//            $result = $query->createQueryBuilder()
//                ->select(['d.qty as quantity','d.width','d.length','d.comment as comments','d.sequence as sequenced',
//                    'd.finishThickId as pThicknessName','d.panelThickness as panelThicknessName'])
//                ->from('AppBundle:Doors', 'd')
//                ->leftJoin('AppBundle:Quotes', 'q', 'WITH', 'd.quoteId = q.id')
//                ->addSelect(['q.refNum','q.deliveryDate'])
//                ->leftJoin('AppBundle:Profile', 'u', 'WITH', "q.customerId = u.userId")
//                ->addSelect(['u.company as username'])
//                ->leftJoin('AppBundle:Skins', 's', 'WITH', "s.doorId = d.id")
//                ->addSelect(['s.species','grade','pattern'])
//                ->leftJoin('AppBundle:GrainDirection', 'gd', 'WITH', "s.grain_dir = gd.id")
//                ->addSelect(['gd.name as grainDirectionName'])
//                ->leftJoin('AppBundle:Species', 'sp', 'WITH', "s.species = sp.id")
//                ->addSelect(['s.name as SpecieName'])
//                ->leftJoin('AppBundle:Pattern', 'p', 'WITH', "s.pattern = p.id")
//                ->addSelect(['p.name as patternName'])
//                ->leftJoin('AppBundle:Thickness', 't', 'WITH', "s.thicknessId = t.id")
//                ->addSelect(['t.name as thicknessName'])
//                ->leftJoin('AppBundle:FaceGrade', 'fg', 'WITH', "v.gradeId = fg.id")
//                ->addSelect(['fg.name as faceGradeName'])
//                ->where('d.quoteId = '.$quoteId)
//                ->getQuery()
//                ->getResult()
//            ;
//            print_r($result);die;
        }
    }

    private function createQuoteLineitemPDF($html, $pdfName, $request) {
        $fs = new Filesystem();
        $snappy = new Pdf('../vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=' . $pdfName);
        $snappy->generateFromHtml($html, $pdfName);
        $fs->chmod($pdfName, 0777);
        return true;
    }

    private function arraySortByColumn(&$arr, $col, $dir = SORT_ASC) {
        if (!empty($arr)) {
            $sort_col = array();
            foreach ($arr as $key => $row) {
                $sort_col[$key] = $row[$col];
            }
            array_multisort($sort_col, $dir, $arr);
        }
        return $arr;
    }

    private function getOrderTicketVeneerHTML($v, $images_destination) {
        $veneerHtml = '';
        $veneerHtml .= '<div style="page-break-after:always" *ngFor="let v of data">
            <div class="ticketWrap">
                <div class="ticketScreen veneer">
                    <table class="ticketHead">
                        <tr>
                            <td class="lftLogo"><img src="' . $images_destination . '/ticket-images/logo-ico.png" alt="Department Logo"></td>
                            <td class="dep" >Veneer Department</td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><label class="custName">' . $v['username'] . '</label></td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['refNum'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['estNumber'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><hr></td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"><label>Quantity</label></td>
                            <td class="cellDesc">' . $v['quantity'] . ' Pieces</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Species</label></td>
                            <td class="cellDesc">' . $v['SpecieName'] . ' - ' . $v['patternName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Size</label></td>
                            <td class="cellDesc">' . $v['width'] . ' - ' . $v['length'] . '"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Thickness</label></td>
                            <td class="cellDesc">' . $v['thicknessName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Grade</label></td>
                            <td class="cellDesc">' . $v['faceGradeName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Back</label></td>
                            <td class="cellDesc">' . $v['backerName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Grain Direction</label></td>
                            <td class="cellDesc">' . $v['grainDirectionName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Sequenced</label></td>
                            <td class="cellDesc">' . $v['sequenced'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Labels</label></td>
                            <td class="cellDesc">No</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Notes:</label></td>
                            <td class="cellDesc"><span class="noteTxt">' . $v['comments'] . '</span></td>
                        </tr>
                    </table>

                    <table class="ticketFoot">
                        <tr>
                            <td class="cellLabel"><label>Due</label></td>
                            <td class="cellDesc"><label class="custName">' . date('l', strtotime($v['deliveryDate'])) . ' | ' . date('M d', strtotime($v['deliveryDate'])) . '</label></td>
                        </tr>
                        <tr>
                            <td class="lftFoot"><!--<img src="' . $images_destination . '/ticket-images/veneer-lft-img.png" alt="">--></td>
                            <td class="dep footIN">
                                <table>
                                    <tr>
                                        <td class="itBx"><span class="itTxt">Item ' . $v['lineItemNum'] . '</span></td>
                                        <td class="footImg">
                                            <img src="' . $images_destination . '/ticket-images/foot-img-1.png" alt="">
                                            <!-- <img src="' . $images_destination . '/ticket-images/foot-img-2.png" alt=""> -->
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div><br/><br/>
            <!-- End: \ Ticket for Sanding Department -->
            <div class="ticketWrap">
                <div class="ticketScreen sanding">
                    <table class="ticketHead">
                        <tr>
                            <td class="lftLogo"><img src="' . $images_destination . '/ticket-images/logo-ico.png" alt="Department Logo"></td>
                            <td class="dep">Sanding Department</td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><label class="custName">' . $v['username'] . '</label></td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['refNum'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['estNumber'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><hr></td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"><label>Quantity</label></td>
                            <td class="cellDesc">' . $v['quantity'] . ' Pieces</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Species/Face</label></td>
                            <td class="cellDesc">' . $v['SpecieName'] . ' - ' . $v['patternName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Finished Size</label></td>
                            <td class="cellDesc">' . $v['width'] . '" x ' . $v['length'] . '"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Finished Thickness</label></td>
                            <td class="cellDesc">' . $v['thicknessName'] . '"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Notes:</label></td>
                            <td class="cellDesc"><span class="noteTxt">' . $v['comments'] . '</span></td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Labels</label></td>
                            <td class="cellDesc">No</td>
                        </tr>
                    </table>

                    <table class="ticketFoot">
                        <tr>
                            <td class="cellLabel"><label>Due</label></td>
                            <td class="cellDesc"><label class="custName">' . date('l', strtotime($v['deliveryDate'])) . ' | ' . date('M d', strtotime($v['deliveryDate'])) . '</label></td>
                        </tr>
                        <tr>
                            <td class="lftFoot">&nbsp;</td>
                            <td class="dep footIN">
                                <table>
                                    <tr>
                                        <td class="itBx"><span class="itTxt">Item ' . $v['lineItemNum'] . '</span></td>
                                        <td class="footImg">
                                            <img src="' . $images_destination . '/ticket-images/foot-img-1.png" alt="">
                                            <!-- <img src="' . $images_destination . '/ticket-images/foot-img-2.png" alt=""> -->
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div><br/>
        </div>';
        return $veneerHtml;
    }

    private function getOrderTicketPlywoodHTML($v, $images_destination) {
        $topEdgeName = !empty($v['topEdgeName']) ? $v['topEdgeName'] : 'N/A';
        $bottomEdgeName = !empty($v['bottomEdgeName']) ? $v['bottomEdgeName'] : 'N/A';
        $rightEdgeName = !empty($v['rightEdgeName']) ? $v['rightEdgeName'] : 'N/A';
        $topEdgeMaterialName = !empty($v['topEdgeMaterialName']) ? $v['topEdgeMaterialName'] : 'N/A';
        $rightEdgeMaterialName = !empty($v['rightEdgeMaterialName']) ? $v['rightEdgeMaterialName'] : 'N/A';
        $leftEdgeMaterialName = !empty($v['leftEdgeMaterialName']) ? $v['leftEdgeMaterialName'] : 'N/A';
        $topSpeciesName = !empty($v['topEdgeName']) ? $v['topEdgeName'] : 'N/A';
        $bottomSpeciesName = !empty($v['bottomSpeciesName']) ? $v['bottomSpeciesName'] : 'N/A';
        $rightSpeciesName = !empty($v['rightSpeciesName']) ? $v['rightSpeciesName'] : 'N/A';
        $leftSpeciesName = !empty($v['leftSpeciesName']) ? $v['leftSpeciesName'] : 'N/A';
        $leftEdgeName = !empty($v['leftEdgeName']) ? $v['leftEdgeName'] : 'N/A';
        $bottomEdgeMaterialName = !empty($v['bottomEdgeMaterialName']) ? $v['bottomEdgeMaterialName'] : 'N/A';


        $edge = '';
        if (($topEdgeName !== 'N/A' && $topEdgeName !== 'None') ||
                ($bottomEdgeName !== 'N/A' && $bottomEdgeName !== 'None') ||
                ($rightEdgeName !== 'N/A' && $rightEdgeName !== 'None') ||
                ($leftEdgeName !== ' N/A' && $leftEdgeName !== 'None')) {
            if ($topEdgeName !== 'N/A' && $topEdgeName !== 'None') {
                $edge .= 'TE: ' . $topEdgeName . ' &nbsp; |  &nbsp;' . $topEdgeMaterialName . ' &nbsp; |  &nbsp;' . $topSpeciesName . ' <br>';
            }
            if ($bottomEdgeName !== 'N/A' && $bottomEdgeName !== 'None') {
                $edge .= 'TE: ' . $bottomEdgeName . ' &nbsp; |  &nbsp;' . $bottomEdgeMaterialName . ' &nbsp; |  &nbsp;' . $bottomSpeciesName . ' <br>';
            }
            if ($rightEdgeName !== 'N/A' && $rightEdgeName !== 'None') {
                $edge .= 'TE: ' . $rightEdgeName . ' &nbsp; |  &nbsp;' . $rightEdgeMaterialName . ' &nbsp; |  &nbsp;' . $rightSpeciesName . ' <br>';
            }
            if ($leftEdgeName !== 'N/A' && $leftEdgeName !== 'None') {
                $edge .= 'TE: ' . $leftEdgeName . ' &nbsp; |  &nbsp;' . $leftEdgeMaterialName . ' &nbsp; |  &nbsp;' . $leftSpeciesName . ' <br>';
            }
        } else {
            $edge = 'No';
        }
        $plywoodHtml = '';
        $plywoodHtml .= '
                    <div style="page-break-after:always" *ngFor="let v of data">
            <div class="ticketWrap">
                <div class="ticketScreen veneer">
                    <table class="ticketHead">
                        <tr>
                            <td class="lftLogo"><img src="' . $images_destination . '/ticket-images/logo-ico.png" alt="Department Logo"></td>
                            <td class="dep" >Veneer Department</td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><label class="custName">' . $v['username'] . '</label></td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['refNum'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['estNumber'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><hr></td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"><label>Quantity</label></td>
                            <td class="cellDesc">' . $v['quantity'] . ' Pieces</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Species</label></td>
                            <td class="cellDesc">' . $v['SpecieName'] . ' - ' . $v['patternName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Size</label></td>
                            <td class="cellDesc">' . ($v['width'] + 1) . '" x ' . $v['length'] . '"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Thickness</label></td>
                            <td class="cellDesc">' . $v['thicknessName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Grade</label></td>
                            <td class="cellDesc">' . $v['faceGradeName'] . '</td>
                        </tr>
                       ';
        if (!empty($v['backerName'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc">' . $v['backerName'] . '</td>
                            </tr>';
        }
        $plywoodHtml .= '<tr>
                            <td class="cellLabel"><label>Grain Direction</label></td>
                            <td class="cellDesc">' . $v['grainDirectionName'] . '</td>
                        </tr>';
        if (!empty($v['isSequenced']) && $v['isSequenced'] == 1) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label>Sequenced</label></td>
                                <td class="cellDesc">' . ($v['isSequenced'] == 1 ? 'Yes' : '') . '</td>
                            </tr>';
        }
        if (!empty($v['comments'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label>Notes:</label></td>
                                <td class="cellDesc">' . ($v['comments']) . '</td>
                            </tr>';
        }
        if (!empty($v['backerName'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc">' . ($v['backerName']) . '</td>
                            </tr>';
        }
        if (empty($v['isSequenced']) && $v['isSequenced'] == 0) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc"></td>
                            </tr>';
        }
        if (empty($v['comments'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc"></td>
                            </tr>';
        }
        if (empty($v['backerName'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc"></td>
                            </tr>';
        }
        $plywoodHtml .= '<tr>
                            <td class="cellLabel"><label></label></td>
                            <td class="cellDesc"></td>
                        </tr></table>

                    <table class="ticketFoot">
                        <tr>
                            <td class="cellLabel"><label>Due</label></td>
                            <td class="cellDesc"><label class="custName">' . date('l', strtotime($v['deliveryDate'])) . ' | ' . date('M d', strtotime($v['deliveryDate'])) . '</label></td>
                        </tr>
                        <tr>
                            <td class="lftFoot"><!--<img src="' . $images_destination . '/ticket-images/veneer-lft-img.png" alt="">--></td>
                            <td class="dep footIN">
                                <table>
                                    <tr>
                                        <td class="itBx"><span class="itTxt">Item ' . $v['lineItemNum'] . '</span></td>
                                        <td class="footImg">
                                            <img src="' . $images_destination . '/ticket-images/foot-img-1.png" alt="">
                                            <!-- <img src="' . $images_destination . '/ticket-images/foot-img-2.png" alt=""> -->
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div><br/><br/>
            <div class="ticketWrap">
                <div class="ticketScreen core">
                    <table class="ticketHead">
                        <tr>
                            <td class="lftLogo"><img src="' . $images_destination . '/ticket-images/logo-ico.png" alt="Department Logo"></td>
                            <td class="dep">Core Department</td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><label class="custName">' . $v['username'] . '</label></td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['refNum'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['estNumber'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><hr></td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"><label>Quantity</label></td>
                            <td class="cellDesc">' . $v['quantity'] . ' Pieces</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Species</label></td>
                            <td class="cellDesc">' . $v['SpecieName'] . ' - ' . $v['patternName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Size</label></td>
                            <td class="cellDesc">' . ($v['width'] + 1) . '" x ' . $v['length'] . '"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Thickness</label></td>
                            <td class="cellDesc">' . $v['panelThicknessName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Grade</label></td>
                            <td class="cellDesc">' . $v['faceGradeName'] . '</td>
                        </tr>';
        if (!empty($v['backerName'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label>Back</label></td>
                                <td class="cellDesc">' . $v['backerName'] . '</td>
                            </tr>';
        }
        if (!empty($edge)) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label>Edge</label></td>
                                <td class="cellDesc">' . $edge . '</td>
                            </tr>';
        }
        if (!empty($v['comments'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label>Notes:</label></td>
                                <td class="cellDesc">' . $v['comments'] . '</td>
                            </tr>';
        }
        if (empty($v['backerName'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc"></td>
                            </tr>';
        }
        if (empty($edge)) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc"></td>
                            </tr>';
        }
        if (empty($v['comments'])) {
            $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label>:/label></td>
                                <td class="cellDesc"></td>
                            </tr>';
        }
        $plywoodHtml .= '<tr>
                            <td class="cellLabel"><label></label></td>
                            <td class="cellDesc"></td>
                        </tr>';
        $plywoodHtml .= '</table>
                        <table class="ticketFoot">
                        <tr>
                            <td class="cellLabel"><label>Due</label></td>
                            <td class="cellDesc"><label class="custName">' . date('l', strtotime($v['deliveryDate'])) . ' | ' . date('M d', strtotime($v['deliveryDate'])) . '</label></td>
                        </tr>
                        <tr>
                            <td class="lftFoot">&nbsp;</td>
                            <td class="dep footIN">
                                <table>
                                    <tr>
                                        <td class="itBx"><span class="itTxt">Item ' . $v['lineItemNum'] . '</span></td>
                                        <td class="footImg">
                                            <img src="' . $images_destination . '/ticket-images/foot-img-1.png" alt="">
                                            <img src="' . $images_destination . '/ticket-images/foot-img-2.png" alt="">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div><br/>
            <!-- End: \ Ticket for Sanding Department -->
            <div class="ticketWrap">
                <div class="ticketScreen sanding">
                    <table class="ticketHead">
                        <tr>
                            <td class="lftLogo"><img src="' . $images_destination . '/ticket-images/logo-ico.png" alt="Department Logo"></td>
                            <td class="dep">Sanding Department</td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><label class="custName">' . $v['username'] . '</label></td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['refNum'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">' . $v['estNumber'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><hr></td>
                        </tr>
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"><label>Quantity</label></td>
                            <td class="cellDesc">' . $v['quantity'] . ' Pieces</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Species/Face</label></td>
                            <td class="cellDesc">' . $v['SpecieName'] . ' - ' . $v['patternName'] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Finished Size</label></td>
                            <td class="cellDesc">' . $v['width'] . '" x ' . $v['length'] . '"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Finished Thickness</label></td>
                            <td class="cellDesc">' . $v['thicknessName'] . '"</td>
                        </tr>';
        if (!empty($v['comments'])) {
            $plywoodHtml .= '<tr>
                        <td class="cellLabel"><label>Notes:</label></td>
                        <td class="cellDesc">' . $v['comments'] . '</td>
                    </tr>
                    ';
        }
        if (empty($v['comments'])) {
            $plywoodHtml .= '<tr>
                        <td class="cellLabel"><label></label></td>
                        <td class="cellDesc"></td>
                    </tr>
                    ';
        }
        $plywoodHtml .= '<tr>
                                <td class="cellLabel"><label></label></td>
                                <td class="cellDesc"></td>
                            </tr>
                            </table>

                    <table class="ticketFoot">
                        <tr>
                            <td class="cellLabel"><label>Due</label></td>
                            <td class="cellDesc"><label class="custName">' . date('l', strtotime($v['deliveryDate'])) . ' | ' . date('M d', strtotime($v['deliveryDate'])) . '</label></td>
                        </tr>
                        <tr>
                            <td class="lftFoot">&nbsp;</td>
                            <td class="dep footIN">
                                <table>
                                    <tr>
                                        <td class="itBx"><span class="itTxt">Item ' . $v['lineItemNum'] . '</span></td>
                                        <td class="footImg">
                                            <img src="' . $images_destination . '/ticket-images/foot-img-1.png" alt="">
                                            <!-- <img src="' . $images_destination . '/ticket-images/foot-img-2.png" alt=""> -->
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div><br/>
        </div>';
        return $plywoodHtml;
    }
    
    private function getOrderTicketHTML($lineItemHTML){
        $html='';
        if(!empty($lineItemHTML)){
            $html .= '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                @media print {
                    body {
                       -webkit-print-color-adjust: exact;
                       -moz-print-color-adjust: exact;
                       print-color-adjust: exact;
                       margin:0mm;
                    }
                 }
                 @page {
                    size: A5 Portrait;
                    margin:0;
                }
                body{
                    font-family:"Libre Franklin",
                    Arial,sans-serif;font-weight:400;
                }
                table{width:100%;border-collapse:collapse;border-spacing:0;}
                h1,h2,h3,label,strong{font-weight:400;font-family:inherit;}
                table.ticketFoot{margin-top:50px;}
                .ticketWrap{width:600px; margin:auto; min-height:800px;}
                .ticketWrap.broad{width:1132px;}
                .ticketScreen{padding:40px 0;}
                .ticketHead{margin-bottom:30px;}
                .lftLogo,.cellLabel{width:121px;line-height:0;}
                .lftFoot{width:111px;line-height:0;}
                .ticketHead td,.ticketDesc td{padding:0 6px;vertical-align:middle;text-align:left;}
                td.dep{padding:0 30px;color:#ffffff;font-size:30px;font-weight:600;height:95px;}
                .lftLogo img{margin:0 0 0 6px;display:block;padding:0;}
                .ticketHead td.lftLogo,.ticketDesc td.cellLabel{padding-left:0;}
                td.cellDesc{font-size:20px; line-height:21px; color:#212121; vertical-align:top;}
                td.cellLabel{font-size:20px; line-height:21px; color:#999999;text-align:right; vertical-align:top;}
                .ticketDesc td.cellDesc{padding-left:0;}
                .cellLabel label{padding-right:10px;display:inline-block;padding-top:0px}
                label.custName,td.cellDesc label.custName{font-size:42px;font-weight:600;color:#212121;padding:0;line-height:1;}
                table.ticketDesc td{padding:3px 6px;}
                td.cellDesc label{
                    padding:0 8px 3px;display:inline-block;vertical-align:middle;color:#f02232;font-size:11px;font-weight:600;
                }
                table td hr{
                    height:1px;display:block;width:90%;background-color:#212121;border:0;margin:0 0 10px;
                }
                td.cellDesc .noteTxt{font-size:15px;display:inline-block;padding-bottom:3px;}
                td.cellDesc label.grnTxt{font-size:10px;color:#33b598;padding:0 0 3px;}
                td.dep.footIN{height:auto;padding:10px 15px 15px 0;}
                .footIN table td{padding:0;vertical-align:bottom;color:#f02232;font-size:13px;text-align:center;}
                td span.itTxt{font-size:32px;color:#ffffff;}td.itBx{width:110px;}
                .footIN table td.footImg{padding:10px 0 10px 20px;text-align:left;height:90px;}
                .footImg img{margin:0 16px;display:inline;}
                .ticketFoot td.cellDesc label.custName{font-size:32px;}
                .ticketWrap table.hlfGrdTable{width:100%;}
                .ticketWrap table.hlfGrdTable td{
                    width:50%;vertical-align:top;padding-left:20px;padding-right:20px;border-left:solid 1px #212121;
                }
                .ticketWrap table.hlfGrdTable td.cellLabel{width:121px;}
                .ticketWrap table.hlfGrdTable td.cellDesc{width:auto;}
                .ticketWrap table.hlfGrdTable td.cellLabel,.ticketWrap table.hlfGrdTable td.cellDesc{
                    padding-left:6px;padding-right:6px;border:0;
                }
                .ticketWrap table.hlfGrdTable td.fstGrd{
                    padding-left:0;padding-right:0;border:0;
                }
                .ticketWrap.broad table.ticketHead .lftLogo,
                .ticketWrap.broad table.ticketFoot .cellLabel,
                .ticketWrap.broad table.ticketFoot .lftFoot{
                    width:131px;
                }
                .ticketWrap.broad .lftLogo img{margin-left:16px;}
                .ticketWrap table.ticketDesc td.cellDesc td,
                .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td{padding:0;border:0;}
                .ticketWrap table.ticketDesc td.cellDesc td,
                .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td td.w20p,
                td.w20p{width:20%;}
                .veneer td.dep{background-color:#9c946b;}.core td.dep,.laminating td.dep{background-color:#efa500;}
                .sanding td.dep{background-color:#a5b500;}.door td.dep,.shipping td.dep{background-color:#808080;}
    }
                </style>
            </head>
            <body>
            ' . $lineItemHTML . '
            </body>
        </html>';
        }
        return $html;
    }
}
