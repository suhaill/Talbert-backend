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

use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

                    $quoteData = $this->getOrderDataById( $orDat->getQuoteId() );
                        
                    $arrApi['data']['orders'][$i]['estNumber'] = $orDat->getEstNumber();
                    $arrApi['data']['orders'][$i]['orderDate'] = $orDat->getOrderDate()?date('m-d-Y',strtotime($orDat->getOrderDate())):'-';//$orDat->getOrderDate();
                    $arrApi['data']['orders'][$i]['productname'] = $quoteData->getJobName();
                    $arrApi['data']['orders'][$i]['poNumber'] = $quoteData->getRefNum();
                    $arrApi['data']['orders'][$i]['shipDate'] = $orDat->getShipDate()?date('m-d-Y',strtotime($orDat->getShipDate())):'-';
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
                    's.abbr as status',
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
                    'estimateNumber' => $quotes[$i]['controlNumber'] . '-' . $quotes[$i]['version'],
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
                // $quotes = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findBy(array('status'=>['Approved']),array('id'=>'desc'));
                $quoteStatus    = $this->getDoctrine()->getManager();
                $status         = $quoteStatus->createQueryBuilder()
                    ->select(['s.id'])
                    ->from('AppBundle:Status', 's')
                    ->where("s.type = 'Quote' and s.statusName = 'Approved' and s.isActive=1")
                    ->getQuery()
                    ->getResult();

                $query = $this->getDoctrine()->getManager();
                $quotes = $query->createQueryBuilder()
                        ->select(['q.id', 'qs.updatedAt', 'qs.statusId'])
                        ->from('AppBundle:Quotes', 'q')
                        ->leftJoin('AppBundle:Orders', 'o', 'WITH', "q.id = o.quoteId")
                        ->leftJoin('AppBundle:QuoteStatus', 'qs', 'WITH', "q.id = qs.quoteId")
                        ->leftJoin('AppBundle:Status', 's', 'WITH', "qs.statusId=s.id ")
                        ->where("o.isActive = 1 and q.status='Approved' and qs.statusId = ".$status[0]['id'])
                        ->groupBy('qs.quoteId')
                        ->orderBy('o.id', 'desc')
                        ->orderBy('qs.updatedAt', 'desc')
                        ->getQuery()
                        ->getResult();

                if (!empty($quotes)) {
                    $quoteId = $quotes[0]['id'];
                }
            }
            $this->updateQuoteData($quoteId);
            $quoteData = $this->getOrderDataById($quoteId);//print_r($quoteData);die();
            $orderData = $this->getOrderDetailsById($quoteId);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This order does not exists';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived order details';
                $arrApi['data']['id'] = $quoteData->getId();
                //$arrApi['data']['date'] = $quoteData->getEstimateDate();
                $arrApi['data']['date'] = $quoteData->getUpdatedAt()->format('Y-m-d H:i:s');
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
                //$arrApi['data']['lineitems'] = $this->getVeneerslistbyQuoteId($quoteId);
                $lineitems =  $this->getVeneerslistbyQuoteId($quoteId);
                $arrApi['data']['lineitems'] = $lineitems['lineItem'];
                $arrApi['data']['calSqrft'] = $lineitems['calSqrft'];
                $arrApi['data']['orderDate'] = date("d.m.y",strtotime($orderData->getOrderDate()))." | ".date("h:i:s a",strtotime($orderData->getOrderDate()));
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

    /**
     * @Route("/api/order/printWorkOrder")
     * @Method("POST")
     */
    public function testingAction(Request $request) {
        $images_destination = $this->container->getParameter('images_destination');
        $arrApi = array();
        $statusCode = 200;
        $_DATA = file_get_contents('php://input');
        $_DATA = json_decode($_DATA, true);
        $quoteId = $_DATA['orderId'];
        $printType = ($_DATA['type'] == 'printShipper') ? 'SHIPPER' : (($_DATA['type'] == 'printInvoice') ? 'INVOICE' : 'ORDER');
        $orderData = $this->getOrderDetailsById($quoteId);
        try {
            $quoteData = $this->getQuoteDataById($quoteId);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This quote does not exists';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived quote details';
                $arrApi['data']['id'] = $quoteData->getId();
                $arrApi['data']['date'] = date("M d, Y",strtotime($quoteData->getEstimateDate()));
                $arrApi['data']['estimatorId'] = $quoteData->getEstimatorId();
                $arrApi['data']['controlNumber'] = $quoteData->getControlNumber();
                $arrApi['data']['version'] = $quoteData->getVersion();
                $arrApi['data']['customer'] = $this->getCustomerNameById($quoteData->getCustomerId());
                $arrApi['data']['company'] = $this->getCustomerCompanyById($quoteData->getCustomerId());
                $arrApi['data']['userEmail'] = $this->getCustomerEmailById($quoteData->getEstimatorId());
                $arrApi['data']['customerEmail'] = $this->getCustomerEmailById($quoteData->getCustomerId());
                $arrApi['data']['customerId'] = $quoteData->getCustomerId();
                $arrApi['data']['referenceNumber'] = ($quoteData->getRefNum()) ? 'PO-'.$quoteData->getRefNum() : 'None';
                $arrApi['data']['salesman'] = $this->getSalesmanNameById($quoteData->getSalesmanId());
                $arrApi['data']['salesmanId'] = $quoteData->getSalesmanId();
                $arrApi['data']['job'] = ($quoteData->getJobName()) ? $quoteData->getJobName() : 'None';
                $arrApi['data']['term'] = (string) $quoteData->getTermId();
                $arrApi['data']['shipMethod'] = $this->getShipMethodNamebyId($quoteData->getShipMethdId());
                $arrApi['data']['shipMethodId'] = $quoteData->getShipMethdId();
                $arrApi['data']['billAdd'] = $this->getBillAddById($quoteData->getCustomerId());
                $arrApi['data']['shipAdd'] = $this->getShippingAddById($quoteData->getShipAddId());
                $arrApi['data']['shipAddId'] = $quoteData->getShipAddId();
                $arrApi['data']['leadTime'] = $quoteData->getLeadTime();
                $arrApi['data']['status'] = $quoteData->getStatus();
                $arrApi['data']['comment'] = $quoteData->getComment();
                $arrApi['data']['deliveryDate'] = date("M d, Y",strtotime($quoteData->getDeliveryDate()));
                $arrApi['data']['quoteSubTot'] = !empty($quoteData->getQuoteTot())?str_replace(',','',number_format($quoteData->getQuoteTot(),0)):'00.00';
                $arrApi['data']['expFee'] = !empty($quoteData->getExpFee())?str_replace(',','',number_format($quoteData->getExpFee(),2)):'00.00';
                $arrApi['data']['discount'] = !empty($quoteData->getDiscount())?str_replace(',','',number_format($quoteData->getDiscount(),2)):'00.00';
                $arrApi['data']['lumFee'] = !empty($quoteData->getLumFee())?str_replace(',','',number_format($quoteData->getLumFee(),2)):'00.00';
                $arrApi['data']['shipCharge'] = !empty($quoteData->getShipCharge())?str_replace(',','',number_format($quoteData->getShipCharge(),2)):'00.00';
                $arrApi['data']['salesTax'] = !empty($quoteData->getSalesTax())?str_replace(',','',number_format($quoteData->getSalesTax(),2)):'00.00';
                $arrApi['data']['termName'] = $this->getTermName($quoteData->getTermId());
                if ($quoteData->getQuoteTot() == 0) {
                    $arrApi['data']['projectTot'] = '00.00';
                } else {
                    $arrApi['data']['projectTot'] = str_replace(',','',number_format($quoteData->getProjectTot(),2));
                }
                $lineitems =  $this->getVeneerslistbyQuoteId($quoteId,$printType);
                $arrApi['data']['lineitems'] = $lineitems['lineItem'];
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        $calSqrft = 0;
        foreach($arrApi['data']['lineitems'] as $key=>$qData){
            $calSqrft += ((float)($qData['width'] + $qData['widthFraction'])*(float)($qData['length'] + $qData['lengthFraction']))/144;
        }

        $html = "<!DOCTYPE html>
                <html>
                <head>
                    <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700' rel='stylesheet'>
                    <style>
                        body{font-family:'Roboto Condensed',sans-serif;font-weight:400;line-height:1.4;font-size:14px;}table{width:100%;border-collapse:collapse;border-spacing:0;margin:0 0 15px;}body h1,body h2,body h3,body label,body strong,table.prodItmLst th,table.prodItmLst td{font-family:'Roboto Condensed',sans-serif; font-size:8pt; color:#302d2e ;}table h3{font-size:16px;color:#272425;}table.invoiceHeader table{margin:0;}
                        .invoiceWrap{width:1100px;margin:auto;border:solid 1px #7f7d7e;padding:18px 22px;}.invoiceHeader td{vertical-align:top;}.invCapt{width:170px;text-align:center;}.captBTxt{font-size:36px;color:#919396;text-transform:uppercase;line-height:1;}.subTxt{font-size:18px;color:#919396;margin:0 0 6px;}.invCapt p{color:#434041;font-size:13px;margin:0 0 12px;}.invCapt p:last-of-type{margin:0;}.invLogo{margin:0 0 20px;line-height:0;}.addressHldr .addCell{width:50%;padding:0;}.addCellDscHldr td.cellDescLbl{width:96px;text-align:right;padding:0 8px 0 0;vertical-align:middle;color:#a9abad;font-size:11px;text-transform:uppercase;}.addCellDscHldr td.cellDescTxt{padding:0 8px;border-left:solid 2px #918f90;vertical-align:top;}.addCellDscHldr td.cellDescTxt h3,.addCellDscHldr td.cellDescTxt p{margin:0;padding:0;}.addCellDscHldr td.cellDescTxt p{font-size:14px;}.custOdrDtls{padding:10px 0 8px;margin-bottom:18px;border-bottom:solid 1px #a0a0a0;color:#000000;font-size:13px;}.custOdrDtls p{margin:0;padding:0;}.custOdrDtls table{width:auto;margin:0;}.custOdrDtls table td{padding:0 14px;border-left:solid 1px #a0a0a0;}.custOdrDtls table td:first-child{padding-left:0;border-left:0;}table.prodItmLst th,table.prodItmLst td{font-family:'Roboto Condensed',sans-serif; font-size:14px; color:#302d2e; text-align:center;padding:3px 6px;vertical-align:top;font-weight:400;}table.prodItmLst th.t-left,table.prodItmLst td.t-left{text-align:left;}table.prodItmLst th{color:#ababab;}table.prodItmLst td{color:#302d2e;}table.prodItmLst td a{color:#302d2e !important;text-decoration:none;}.invoiceCtnr table.prodItmLst td a:hover{color:#000000;}table.prodItmLst td:first-child{text-align:left;}table.prodItmLst td:last-child{text-align:right;}table.prodItmLst tr th,table.prodItmLst tr td{border-bottom:solid 1px #a0a0a0;}table.invoiceFootBtm td{vertical-align:top;color:#171717;font-size:13px;padding:3px 8px;}table.invoiceFootBtm td.sideBox{width:220px;}td.midDesc{text-align:center;}.sideBox .name{margin:0 0 12px;font-weight:700;text-align:left;padding-left:10px;}.invoiceFooter .custOdrDtls{margin-bottom:12px;}.invoiceFooter table{margin:0;}.sideBox .totalTxt td{color:#222222;font-size:14px;padding:0;text-align:right;}.sideBox table.totalTxt tr td:last-child{padding-right:0;}.midDescTxt{width:535px;margin:auto;font-size:11px;padding:4px 0 0;}.midDescTxt p{margin:0 0 8px;}.warnTxt{background-color:#e5e5e6;padding:4px 12px;width:250px;text-align:center;}.warnTxt p{margin:0;padding:0;font-size:11px;font-weight:700;}.warnTxt h3{font-size:18px;text-transform:uppercase;margin:0;padding:0;color:#7a7b7e;}table.totalPrice td{font-size:12px;color:#000000;padding:3px 6px;vertical-align:top;text-align:right;font-weight:400;}table.totalPrice td.price{width:110px;}.invoiceFooter{border-top:solid 1px #a0a0a0;padding-top:8px;}table.invoiceFootBtm td.sideBox.note{text-align:center;width:320px;}.sideBox.note p{font-size:10px;margin:0 0 4px;font-weight:400;}td .approved{border:solid 1px #a0a0a0;padding:10px;font-size:12px;width:200px;text-align:center;}.t-center{text-align:center;}td .approved p{margin:0;}
                        table.prodItmLst th.qtyShipped{color:#302d2e; text-align: right; font-weight:300; font-size:16px;}
                    </style>
                </head>
                <body>
                    <div class=''>
                        <div class='invoiceCtnr'>
                            <table class='invoiceHeader'>
                                <tr>
                                    <td>
                                        <div class='invLogo'><img src='".$images_destination."/logo-invoice.png' alt='Talbert: Architectural Panels and Doors'></div>
                                        <table class='addressHldr'>
                                            <tr>
                                                <td class='addCell'>
                                                    <table class='addCellDscHldr'>
                                                        <tr>
                                                            <td class='cellDescLbl'>Sold To</td>
                                                            <td class='cellDescTxt'>
                                                                <h3>".$arrApi['data']['company']."</h3>
                                                                <p>".$arrApi['data']['billAdd']['street']."</p>
                                                                <p>".$arrApi['data']['billAdd']['city'].",".$arrApi['data']['billAdd']['state']." ".$arrApi['data']['billAdd']['zip']."</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td class='addCell'>
                                                    <table class='addCellDscHldr'>
                                                        <tr>
                                                            <td class='cellDescLbl'>Ship To</td>
                                                            <td class='cellDescTxt'>
                                                            <h3>".$arrApi['data']['shipAdd']['nickname']."</h3>
                                                            <p>".$arrApi['data']['shipAdd']['street']."</p>
                                                            <p>".$arrApi['data']['shipAdd']['city'].",".$arrApi['data']['shipAdd']['state']." ".$arrApi['data']['shipAdd']['zip']."</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td class='invCapt'>
                                        <div class='captBTxt'>".$printType."</div>
                                        <div class='subTxt'>O-".$arrApi['data']['controlNumber']."-".$arrApi['data']['version']."</div>
                                        <p>".$arrApi['data']['date']." &nbsp;|&nbsp; Net 30 days</p>
                                        <p><em>At Talbert Architectural <br>Your Total Satisfaction <br>is Our Goal!</em></p>
                                    </td>
                                </tr>
                            </table>
                            
            
                            <div class='custOdrDtls'>
                                <table>
                                    <tr>
                                        <td>Customer Reference: ".$arrApi['data']['referenceNumber']."</td>
                                        <td>Job Name: ".$arrApi['data']['job']."</td>
                                        <td>Delivery Date: ".$arrApi['data']['deliveryDate']."</td>
                                        <td>Ship Via: ".$arrApi['data']['shipMethod']."</td>
                                        <td>SqFt: ".number_format($calSqrft,2)."</td>
                                    </tr>
                                </table>
                            </div>
                            
            
                            <div class='itemListTablHldr'>
                                <table class='prodItmLst'>
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Qty</th>
                                            <th>Grain</th>
                                            <th>Species</th>
                                            <th>Grd</th>
                                            <th>PTRN</th>
                                            <th>Back</th>
                                            <th>Dimensions</th>
                                            <th>Core</th>
                                            <th class='t-left'>Details</th>";
                                    if ($printType == 'ORDER' || $printType == 'INVOICE') {
                                        $html .= "<th>Unit Price</th>
                                                  <th>Total</th>";
                                    } else {
                                        $html .= "<th class='t-left qtyShipped'>Qty Shipped</th>";
                                    }
                                    
                                    $html .= "</tr></thead><tbody>";

        foreach($arrApi['data']['lineitems'] as $key=>$qData){

            if($qData['type'] == 'plywood'){
                $index = ($key+1)." P";
            }
            else if($qData['type'] == 'veneer'){
                $index = ($key+1)." V";
            }
            else{
                $index = ($key+1)." D";
            }

            $html .= "<tr>
                        <th>".$index."</th>
                        <td>".$qData['quantity']."</td>
                        <td>".$qData['grain']."</td>
                        <td>".$qData['species']."</td>
                        <td>".$qData['grade']."</td>
                        <td>".$qData['pattern']."</td>
                        <td>".$qData['back']."</td>
                        <td>".$qData['width']."-".$qData['widthFraction']." x ".$qData['length']."-".$qData['lengthFraction']." x ".$qData['thickness']."</td>
                        <td>".$qData['core']."</td>
                        ";
            $html .= ($qData['edgeDetail'] == 1) ? "<td class='t-left'>Edge Detail: TE-".$this->getEdgeNameById($qData['topEdge'])."|BE-".$this->getEdgeNameById($qData['bottomEdge'])."|RE-".$this->getEdgeNameById($qData['rightEdge'])."|LE-".$this->getEdgeNameById($qData['leftEdge'])."<br>" : "<td class='t-left'>";
            $html .= ($qData['milling'] == 1) ? "Miling: ".$qData['millingDescription'].' '.$this->getUnitNameById($qData['unitMesureCostId'])."<br>" : "";
            if ($qData['finish'] == 'UV') {
                $html .= "Finish: UV-".$qData['uvCuredId']."-".$qData['sheenId']." %-".$qData['shameOnId'].$qData['coreSameOnbe'].$qData['coreSameOnte'].$qData['coreSameOnre'].$qData['coreSameOnle']."<br>";
            } elseif ($qData['finish'] == 'Paint') {
                $html .= "Finish: Paint-".$qData['facPaint']."-".$qData['uvCuredId']."-".$qData['sheenId']." %".$qData['shameOnId'].$qData['coreSameOnbe'].$qData['coreSameOnte'].$qData['coreSameOnre'].$qData['coreSameOnle']."<br>";
            }
            $html .= ($qData['comment']) ? "Comment: ".$qData['comment']."<br>" : "";
            $html .= ($qData['isLabels']) ? "Label:".$qData['autoNumber']."</td>" : "";

            if ($printType == 'ORDER') {
                if ($qData['isGreyedOut'] != 'LineItemBackOrder') {
                    $html .= "<td>$".$qData['unitPrice']."</td>
                              <td>$".$qData['totalPrice']."</td>
                              </tr>";
                } else {
                    $html .= "<td></td>
                              <td>BACK ORDERED</td>
                              </tr>";
                }
            } elseif ($printType == 'INVOICE') {
                if ($qData['isGreyedOut'] != 'LineItemBackOrder') {
                    $html .= "<td>$".$qData['unitPrice']."</td>
                              <td>$".$qData['totalPrice']."</td>
                              </tr>";
                } else {
                    $html .= "<td></td>
                              <td>BACK ORDERED</td>
                              </tr>";
                }
            } else {
                if ($qData['isGreyedOut'] != 'LineItemBackOrder') {
                    $html .= " <td>".$qData['quantity']."</td>
                           </tr>";
                } else {
                    $html .= " <td>BACK ORDERED</td>
                           </tr>";
                }
            }
        }

        $html .= "</tbody>
                                </table>
                                <table class='totalPrice'>
                                    <tr>";
        if ($printType == 'ORDER' || $printType == 'INVOICE') {
            $html .= "<td>Special Tooling - Description</td><td class='price'>$350.00</td>";
        } else {
            $html .= "<td></td><td class='price'></td>";
        }
        $html .= "</tr></table>
                            </div>  
                            <div class='invoiceFooter'>
                                <!--<div class='custOdrDtls'>
                                    <table>
                                        <tr>
                                            <td><strong>Special Instructions:</strong> **".$arrApi['data']['comment']."**</td>
                                        </tr>
                                    </table>
                                </div> -->
                                <table class='invoiceFootBtm'>
                                    <tr>
                                        <td class='sideBox note'>
                                            <div class='name'>Sold By: ".$arrApi['data']['salesman']."</div> 
                                            <p>Talbert Architectural Panels &amp; Doors, Inc. assumes no liability beyond the replacement of individual items, but not to exceed the purchase price of said item.</p>
                                            <p>For FSC Edgebanding. The claim for edgebanding is the same as the claim for the panels to which it is affixed.</p>
                                        </td>                            
                                        <td>
                                            <div class='warnTxt'>
                                                <h3>WARNING</h3>
                                                <p>Drilling, Sawing, Sanding or machining wood products generate wood dust, a substance known to the State of California to cause cancer.</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class='approved'>
                                                <p>Approved by: ".$orderData->getApprovedBy()." via ".$orderData->getVia()."</p>
                                                <p>".date("d.m.y",strtotime($orderData->getOrderDate()))." | ".date("h:i:s a",strtotime($orderData->getOrderDate()))."</p>
                                            </div>
                                        </td>
                                        <td>
                                            <img src='".$images_destination."/fsc-order.png' alt=''>
                                        </td>";
                            if ($printType == 'ORDER' || $printType == 'INVOICE') {
                                $html .="<td class='sideBox'>
                                            <table class='totalTxt'>
                                                <tr>
                                                    <td>Sub Total</td>
                                                    <td>$".$arrApi['data']['quoteSubTot']."</td>
                                                </tr>
                                                <tr>
                                                    <td>Sale Tax</td>
                                                    <td>$".$arrApi['data']['salesTax']."</td>
                                                </tr>
                                                <tr>
                                                    <td>Shipping</td>
                                                    <td>$".$arrApi['data']['shipCharge']."</td>
                                                </tr>
                                                <tr>
                                                    <td>Lumber Fee</td>
                                                    <td>$".$arrApi['data']['lumFee']."</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total</strong></td>
                                                    <td><strong>$".$arrApi['data']['projectTot']."</strong></td>
                                                </tr>
                                            </table>
                                        </td>";
                            } else {
                                $html .="<td class='sideBox'><table class='totalTxt'>
                                                <tr><td></td><td></td></tr>
                                                <tr><td></td><td></td></tr>
                                                <tr><td></td><td></td></tr>
                                                <tr><td></td><td></td></tr>
                                                <tr><td><strong></strong></td><td><strong></strong></td></tr>
                                            </table>
                                        </td>";
                            }
                                        $html .= "</tr></table></div>
                                                            </div>   
                                                        </div>
                                                    </body>
                                                </html>";

        return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('orientation'=>'Landscape', 'default-header'=>false, 'page-size' => 'Letter')), 200, array('Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="Work-Order-Print.pdf"'));
    }

    private function convertToDecimal($fraction)
    {
        $numbers=explode("/",$fraction);
        return round($numbers[0]/$numbers[1],6);
    }

    private function getOrderDetailsById($orderId) {
        return $this->getDoctrine()->getRepository('AppBundle:Orders')->findOneBy(array('quoteId'=> $orderId));
    }

    private function getUnitNameById($id) {
        return ($id == 1) ? 'Running foot' : ($id == 2) ? 'Side' : ($id == 3) ? 'Square Foot' : 'Piece';
    }

    private function getQuoteDataById($qId,$editFlag='cloneQuote',$estnumber='') {
        if($editFlag=="cloneQuote"){
            $result = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneById($qId);
        } else {
            $estnumber1= explode('-', $estnumber);
            $controlVersion = $estnumber1[1];
            $version = $estnumber1[2];
            $result = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneBy(['controlNumber'=>$controlVersion,
                'version'=>$version],
                ['version'=>'DESC'],1,0);
        }
        return $result;
    }

    private function getTermName($id){
        $lastQuote = $this->getDoctrine()->getRepository('AppBundle:Terms')->findOneById($id);
        if (!empty($lastQuote)) {
            return $lastQuote->getName();
        } else {
            return '';
        }
    }
    
    private function clonePlywoodData($quoteId, $clonedQuoteId, $datime) {
        $ply = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $quoteId,'isAtive'=>1));
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
        $veneeerData = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $quoteId,'isActive'=>1));
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
        $doorData = $this->getDoctrine()->getRepository('AppBundle:Doors')->findBy(array('quoteId' => $quoteId,'status'=>1));
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
            return $patternRecord->getAbbr();
        }
    }

    private function getGradeNameById($gId) {
        $gradeRecord = $this->getDoctrine()->getRepository('AppBundle:FaceGrade')->findOneById($gId);
        if (!empty($gradeRecord)) {
            return $gradeRecord->getName();
        }
    }

    private function getBackNameById($bId,$type) {
        
        if($type == 'veneer'){

            $data = $this->getDoctrine()->getRepository('AppBundle:Backer')->findOneById($bId);
            if(!empty($data)){
                return $data->getName();
            } else {
                return '';
            }

        }
        else if($type == 'plywood'){

            $data = $this->getDoctrine()->getRepository('AppBundle:BackerGrade')->findOneById($bId);
            if(!empty($data)){
                return $data->getAbbr();
            } else {
                return '';
            }

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
            return $cTypeRecord->getAbbr();
        }
    }

    private function getEdgeNameById($eId) {
        $eFinishRecord = $this->getDoctrine()->getRepository('AppBundle:EdgeFinish')->findOneById($eId);
        if (!empty($eFinishRecord)) {
            return $eFinishRecord->getAbbr();
        }
    }

    private function getVeneerslistbyQuoteId($qId,$printType=null) {
        
        $query = $this->getDoctrine()->getManager();

        if($printType == 'SHIPPER'){
           
            $plywoodRecords = $query->createQueryBuilder()
            ->select(['p.id, p.quantity, p.speciesId, p.patternId,p.patternMatch, p.gradeId,p.backerId,p.finishThickId,p.finishThickType, p.finThickFraction, p.plywoodWidth, p.plywoodLength, p.coreType,p.sellingPrice,p.totalCost,p.widthFraction, p.lengthFraction,p.grainPatternId,p.backOrderEstNo,p.edgeDetail,p.topEdge,p.bottomEdge,p.rightEdge,p.leftEdge,p.milling,p.unitMesureCostId,p.finish,p.comments,p.uvCuredId,p.sheenId,p.shameOnId,p.coreSameOnbe,p.coreSameOnte,p.coreSameOnre,p.coreSameOnle,p.facPaint,p.isLabels, p.autoNumber, p.coreType,s.statusName','p.millingDescription'])
            ->from('AppBundle:Plywood', 'p')
            ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = p.id")
            ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
            ->leftJoin('AppBundle:GrainPattern', 'gp', 'WITH', "p.patternId = gp.id")
            ->addSelect(['gp.name as grain'])
            ->leftJoin('AppBundle:Thickness', 't', 'WITH', "p.thicknessId = t.id")
            ->addSelect(['t.name as thicknessName'])
            ->leftJoin('AppBundle:Pattern', 'pat', 'WITH', "pat.id = p.patternMatch")
            ->addSelect(['pat.name as pattern'])
            ->leftJoin('AppBundle:BackerGrade', 'bgrd', 'WITH', "p.backerId = bgrd.id")
            ->addSelect(['bgrd.name as backerName'])
            ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND p.quoteId = :quoteId and p.isActive=1 AND s.statusName !='LineItemBackOrder'")
            ->orderBy('p.id','ASC')
            ->setParameter('quoteId', $qId)
            ->setParameter('lineItemType', 'Plywood')
            ->getQuery()
            ->getResult();

            $veneerRecords = $query->createQueryBuilder()
            ->select(['v.id, v.quantity, v.speciesId, v.patternId,v.patternId, v.gradeId, v.backer, v.thicknessId,v.width, v.length,v.coreTypeId,v.sellingPrice,v.totalCost,v.widthFraction, v.lengthFraction,v.grainPatternId,v.backOrderEstNo, v.comments,s.statusName',"'' as millingDescription"])
            ->from('AppBundle:Veneer', 'v')
            ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = v.id")
            ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
            ->leftJoin('AppBundle:GrainPattern', 'gp', 'WITH', "v.patternId = gp.id")
            ->addSelect(['gp.name as grain'])
            ->leftJoin('AppBundle:Thickness', 't', 'WITH', "v.thicknessId = t.id")
            ->addSelect(['t.name as thicknessName'])
            ->leftJoin('AppBundle:FaceGrade', 'fg', 'WITH', "v.gradeId = fg.id")
            ->addSelect(['fg.name as faceGradeName'])
            ->leftJoin('AppBundle:Backer', 'b', 'WITH', "v.backer = b.id")
            ->addSelect(['b.name as backerName'])
            ->leftJoin('AppBundle:CoreType', 'ct', 'WITH', "ct.id = v.coreTypeId")
            ->addSelect(['ct.name as coreType'])
            ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND v.quoteId = :quoteId and v.isActive=1 AND s.statusName !='LineItemBackOrder'")
            ->orderBy('v.id','ASC')
            ->setParameter('quoteId', $qId)
            ->setParameter('lineItemType', 'Veneer')
            ->getQuery()
            ->getResult();
            $doorRecords = $query->createQueryBuilder()
                ->select(['d.id, d.qty, d.width, d.length,d.widthFraction, d.lengthFraction,d.finishThickType,d.finishThickId,d.finThickFraction,d.backOrderEstNo, d.edgeFinish,d.topEdge,d.bottomEdge,d.rightEdge,d.leftEdge,d.milling,d.unitMesureCostId,d.finish,d.uvCured,d.sheen,d.sameOnBack,d.sameOnBottom,d.sameOnTop,d.sameOnRight,d.sameOnLeft,d.facPaint,d.comment,d.isLabel,d.autoNumber,s.statusName,d.coreType,d.millingDescription'])
                ->from('AppBundle:Doors', 'd')
                ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = d.id")
                ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
                ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND d.quoteId = :quoteId and d.status=1 AND s.statusName !='LineItemBackOrder'")
                ->orderBy('d.id','ASC')
                ->setParameter('quoteId', $qId)
                ->setParameter('lineItemType', 'Door')
                ->getQuery()
                ->getResult();
        }
        else{


            $plywoodRecords = $query->createQueryBuilder()
            ->select(['p.id, p.quantity, p.speciesId, p.patternId,p.patternMatch, p.gradeId,p.backerId,p.finishThickId,p.finishThickType, p.finThickFraction, p.plywoodWidth, p.plywoodLength, p.coreType,p.sellingPrice,p.totalCost,p.widthFraction, p.lengthFraction,p.grainPatternId,p.backOrderEstNo,p.edgeDetail,p.topEdge,p.bottomEdge,p.rightEdge,p.leftEdge,p.milling,p.unitMesureCostId,p.finish,p.comments,p.uvCuredId,p.sheenId,p.shameOnId,p.coreSameOnbe,p.coreSameOnte,p.coreSameOnre,p.coreSameOnle,p.facPaint,p.isLabels, p.autoNumber, p.coreType,s.statusName,p.millingDescription'])
            ->from('AppBundle:Plywood', 'p')
            ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = p.id")
            ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
            ->leftJoin('AppBundle:GrainPattern', 'gp', 'WITH', "p.patternId = gp.id")
            ->addSelect(['gp.name as grain'])
            ->leftJoin('AppBundle:Thickness', 't', 'WITH', "p.thicknessId = t.id")
            ->addSelect(['t.name as thicknessName'])
            ->leftJoin('AppBundle:Pattern', 'pat', 'WITH', "pat.id = p.patternMatch")
            ->addSelect(['pat.name as pattern'])
            ->leftJoin('AppBundle:BackerGrade', 'bgrd', 'WITH', "p.backerId = bgrd.id")
            ->addSelect(['bgrd.name as backerName'])
            ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND p.quoteId = :quoteId and p.isActive=1")
            ->orderBy('p.id','ASC')
            ->setParameter('quoteId', $qId)
            ->setParameter('lineItemType', 'Plywood')
            ->getQuery()
            ->getResult();
        $veneerRecords = $query->createQueryBuilder()
            ->select(['v.id, v.quantity, v.speciesId, v.patternId,v.patternId, v.gradeId, v.backer, v.thicknessId,v.width, v.length,v.coreTypeId,v.sellingPrice,v.totalCost,v.widthFraction, v.lengthFraction,v.grainPatternId,v.backOrderEstNo, v.comments,s.statusName',"'' as millingDescription"])
            ->from('AppBundle:Veneer', 'v')
            ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = v.id")
            ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
            ->leftJoin('AppBundle:GrainPattern', 'gp', 'WITH', "v.patternId = gp.id")
            ->addSelect(['gp.name as grain'])
            ->leftJoin('AppBundle:Thickness', 't', 'WITH', "v.thicknessId = t.id")
            ->addSelect(['t.name as thicknessName'])
            ->leftJoin('AppBundle:FaceGrade', 'fg', 'WITH', "v.gradeId = fg.id")
            ->addSelect(['fg.name as faceGradeName'])
            ->leftJoin('AppBundle:Backer', 'b', 'WITH', "v.backer = b.id")
            ->addSelect(['b.name as backerName'])
            ->leftJoin('AppBundle:CoreType', 'ct', 'WITH', "ct.id = v.coreTypeId")
            ->addSelect(['ct.name as coreType'])
            ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND v.quoteId = :quoteId and v.isActive=1")
            ->orderBy('v.id','ASC')
            ->setParameter('quoteId', $qId)
            ->setParameter('lineItemType', 'Veneer')
            ->getQuery()
            ->getResult();
        $doorRecords = $query->createQueryBuilder()
            ->select(['d.id, d.qty, d.width, d.length,d.widthFraction, d.lengthFraction,d.finishThickType,d.finishThickId,d.finThickFraction,d.backOrderEstNo, d.edgeFinish,d.topEdge,d.bottomEdge,d.rightEdge,d.leftEdge,d.milling,d.unitMesureCostId,d.finish,d.uvCured,d.sheen,d.sameOnBack,d.sameOnBottom,d.sameOnTop,d.sameOnRight,d.sameOnLeft,d.facPaint,d.comment,d.isLabel,d.autoNumber,s.statusName,d.coreType,d.millingDescription'])
            ->from('AppBundle:Doors', 'd')
            ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = d.id")
            ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
            ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND d.quoteId = :quoteId and d.status=1")
            ->orderBy('d.id','ASC')
            ->setParameter('quoteId', $qId)
            ->setParameter('lineItemType', 'Door')
            ->getQuery()
            ->getResult();

        }
        
        $lineItem = array();
        
        $i=0;
        $calSqrft=0;
        $calSqrftP =$calSqrftV =$calSqrftD = 0;
        if (!empty($plywoodRecords) || !empty($veneerRecords) || !empty($doorRecords)) {
            if (!empty($plywoodRecords)) {
                foreach ($plywoodRecords as $p) {
                    $calSqrftP += ((float)($p['plywoodWidth'] + $p['widthFraction'])*
                            (float)($p['plywoodLength'] + $p['lengthFraction']))/144;
                    if($p['finishThickType'] == 'inch'){
                        if($p['finishThickId']>0){
                            $thickness=$p['finishThickId'].($p['finThickFraction']!=0?' '.$this->float2rat($p['finThickFraction']):'').'"';
                        } else {
                            $thickness=$this->float2rat($p['finThickFraction']).'"';
                        }
                    } else {
                        $thickness=$p['finishThickId'].' '.$p['finishThickType'];
                    }
                    $lineItem[$i]['id'] = $p['id'];
                    $lineItem[$i]['type'] = 'plywood';
                    $lineItem[$i]['url'] = 'line-item/edit-plywood';
                    $lineItem[$i]['quantity'] = $p['quantity'];
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($p['speciesId']);
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($p['patternMatch']);
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($p['gradeId']))[0];
                    $lineItem[$i]['back'] = $this->getBackNameById($p['backerId'],'plywood');
                    $lineItem[$i]['thickness'] = $thickness;
                    $lineItem[$i]['width'] = $p['plywoodWidth'];
                    $lineItem[$i]['length'] = $p['plywoodLength'];
                    $lineItem[$i]['core'] = $this->getCoreNameById($p['coreType']);
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $p['sellingPrice'];
                    $lineItem[$i]['totalPrice'] = $p['totalCost'];
                    $lineItem[$i]['widthFraction'] = $this->float2rat($p['widthFraction']);
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($p['lengthFraction']);
                    $lineItem[$i]['grain'] = $this->getGrainNameById($p['patternId']);
                    $lineItem[$i]['edgeDetail'] = ($p['edgeDetail']) ? 1 : 0;
                    $lineItem[$i]['topEdge'] = $p['topEdge'];
                    $lineItem[$i]['bottomEdge'] = $p['bottomEdge'];
                    $lineItem[$i]['rightEdge'] = $p['rightEdge'];
                    $lineItem[$i]['leftEdge'] = $p['leftEdge'];
                    $lineItem[$i]['milling'] = ($p['milling']) ? '1' : 0 ;
                    $lineItem[$i]['unitMesureCostId'] = $p['unitMesureCostId'];
                    $lineItem[$i]['finish'] = $p['finish'];
                    $lineItem[$i]['uvCuredId'] = $this->getUVCuredNameById($p['uvCuredId']);
                    $lineItem[$i]['sheenId'] = $this->getSheenById($p['sheenId']);
                    $lineItem[$i]['shameOnId'] = ($p['shameOnId']) ? 'BS' : '';
                    $lineItem[$i]['coreSameOnbe'] = ($p['coreSameOnbe']) ? ',BE' : '';
                    $lineItem[$i]['coreSameOnte'] = ($p['coreSameOnte']) ? ',TE' : '';
                    $lineItem[$i]['coreSameOnre'] = ($p['coreSameOnre']) ? ',RE' : '';
                    $lineItem[$i]['coreSameOnle'] = ($p['coreSameOnle']) ? ',LE' : '';
                    $lineItem[$i]['facPaint'] = $this->getFacPaintById($p['facPaint']);
                    $lineItem[$i]['isLabels'] = $p['isLabels'];
                    $lineItem[$i]['autoNumber'] = $this->getFirstLabel($p['autoNumber']);
                    $lineItem[$i]['comment'] = $p['comments'];
                    $lineItem[$i]['isGreyedOut'] = $p['statusName'];
                    $lineItem[$i]['greyedOutClass'] = ($p['statusName'] == 'LineItemBackOrder') ? 'greyedOut' : '';
                    $lineItem[$i]['greyedOutEstNo'] = $p['backOrderEstNo'];
                    $lineItem[$i]['topEdgeName'] = $this->getEdgeNameById($p['topEdge']);
                    $lineItem[$i]['bottomEdgeName'] = $this->getEdgeNameById($p['bottomEdge']);
                    $lineItem[$i]['rightEdgeName'] = $this->getEdgeNameById($p['rightEdge']);
                    $lineItem[$i]['leftEdgeName'] = $this->getEdgeNameById($p['leftEdge']);
                    $lineItem[$i]['millingName'] = $this->getUnitNameById($p['milling']);
                    $lineItem[$i]['millingDescription'] = $p['millingDescription'];
                    $i++;
                }
            }
            if (!empty($veneerRecords)) {
                foreach ($veneerRecords as $v) {
                    $calSqrftV += ((float)($v['width'] + $v['widthFraction'])*(float)($v['length'] + $v['lengthFraction']))/144;
                    $lineItem[$i]['id'] = $v['id'];
                    $lineItem[$i]['type'] = 'veneer';
                    $lineItem[$i]['url'] = 'line-item/edit-veneer';
                    $lineItem[$i]['quantity'] = $v['quantity'];
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($v['speciesId']);
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($v['patternId']);
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($v['gradeId']))[0];
                    $lineItem[$i]['back'] = $this->getBackNameById($v['backer'],'veneer');
                    $lineItem[$i]['thickness'] = $this->getThicknessNameById($v['thicknessId']);
                    $lineItem[$i]['width'] = $v['width'];
                    $lineItem[$i]['length'] = $v['length'];
                    $lineItem[$i]['core'] = $this->getCoreNameById($v['coreTypeId']);
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $v['sellingPrice'];
                    $lineItem[$i]['totalPrice'] = $v['totalCost'];
                    $lineItem[$i]['widthFraction'] = $this->float2rat($v['widthFraction']);
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($v['lengthFraction']);
                    $lineItem[$i]['grain'] = $this->getGrainNameById($v['patternId']);
                    $lineItem[$i]['edgeDetail'] = 0;
                    $lineItem[$i]['topEdge'] = 1;
                    $lineItem[$i]['bottomEdge'] = 1;
                    $lineItem[$i]['rightEdge'] = 1;
                    $lineItem[$i]['leftEdge'] = 1;
                    $lineItem[$i]['milling'] = 0;
                    $lineItem[$i]['unitMesureCostId'] = 0;
                    $lineItem[$i]['finish'] = 'None';
                    $lineItem[$i]['uvCuredId'] = 0;
                    $lineItem[$i]['sheenId'] = 0;
                    $lineItem[$i]['shameOnId'] = 0;
                    $lineItem[$i]['coreSameOnbe'] = '';
                    $lineItem[$i]['coreSameOnte'] = '';
                    $lineItem[$i]['coreSameOnre'] = '';
                    $lineItem[$i]['coreSameOnle'] = '';
                    $lineItem[$i]['facPaint'] = 0;
                    $lineItem[$i]['isLabels'] = 0;
                    $lineItem[$i]['autoNumber'] = 0;
                    $lineItem[$i]['comment'] = $v['comments'];
                    $lineItem[$i]['isGreyedOut'] = $v['statusName'];
                    $lineItem[$i]['greyedOutClass'] = ($v['statusName'] == 'LineItemBackOrder') ? 'greyedOut' : '';
                    $lineItem[$i]['greyedOutEstNo'] = $v['backOrderEstNo'];
                    $lineItem[$i]['topEdgeName'] = '';
                    $lineItem[$i]['bottomEdgeName'] = '';
                    $lineItem[$i]['rightEdgeName'] = '';
                    $lineItem[$i]['leftEdgeName'] = '';
                    $lineItem[$i]['millingName'] = '';
                    $lineItem[$i]['millingDescription'] = $v['millingDescription'];
                    $i++;
                }
            }
            if (!empty($doorRecords)) {
                foreach ($doorRecords as $d) {
                    $calSqrftD += ((float)($d['width'] + $d['widthFraction'])*(float)($d['length'] + $d['lengthFraction']))/144;
                    $doorCosts = $this->getDoctrine()->getRepository('AppBundle:DoorCalculator')->
                            findOneBy(['doorId' => $d['id']]);
                    if(!empty($doorCosts)){
                        $totalcostPerPiece = !empty($doorCosts->getSellingPrice())?$doorCosts->getSellingPrice():0;
                        $totalCost = !empty($doorCosts->getTotalCost())?$doorCosts->getTotalCost():0;
                    } else {
                        $totalcostPerPiece=0;
                        $totalCost=0;
                    }
                    if($d['finishThickType'] == 'inch'){
                        if($d['finishThickId']>0){
                            $thickness=$d['finishThickId'].($d['finThickFraction']!=0?' '.$this->float2rat($d['finThickFraction']):'').'"';
                        } else {
                            $thickness=$this->float2rat($d['finThickFraction']).'"';
                        }
                    } else {
                        $thickness=$d['finishThickId'].' '.$d['finishThickType'];
                    }
                    $lineItem[$i]['id'] = $d['id'];
                    $lineItem[$i]['type'] = 'door';
                    $lineItem[$i]['url'] = 'door/edit-door';
                    $lineItem[$i]['quantity'] = $d['qty'];
                    if ($this->getSpeciesNameById($this->getSpeciesIdByDoorId($d['id'])) == null) {
                        $lineItem[$i]['grain'] = 'Other';
                        $lineItem[$i]['species'] = 'Other';
                    } else {
                        $lineItem[$i]['species'] = $this->getSpeciesNameById($this->getSpeciesIdByDoorId($d['id']));
                        $lineItem[$i]['grain'] = $this->getGrainPatternOfDoor($d['id']);
                    }
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($this->getPatternIdByDoorId($d['id']));
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($this->getGradeIdByDoorId($d['id'])))[0];
                    $lineItem[$i]['back'] = 'NA';//$this->getBackNameById($this->getBackerIdByDoorId($d->getId()));
                    $lineItem[$i]['thickness']=$thickness;
                    $lineItem[$i]['width'] = $d['width'];
                    $lineItem[$i]['length'] = $d['length'];
                    $lineItem[$i]['core'] = $this->getCoreNameById($d['coreType']);
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $totalcostPerPiece;
                    $lineItem[$i]['totalPrice'] = $totalCost;
                    $lineItem[$i]['widthFraction'] = $this->float2rat($d['widthFraction']);
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($d['lengthFraction']);
                    $lineItem[$i]['edgeDetail'] = ($d['edgeFinish']) ? '1' : 0;
                    $lineItem[$i]['topEdge'] = $d['topEdge'];
                    $lineItem[$i]['bottomEdge'] = $d['bottomEdge'];
                    $lineItem[$i]['rightEdge'] = $d['rightEdge'];
                    $lineItem[$i]['leftEdge'] = $d['leftEdge'];
                    $lineItem[$i]['milling'] = ($d['milling']) ? '1' : 0 ;
                    $lineItem[$i]['unitMesureCostId'] = $d['unitMesureCostId'];
                    $lineItem[$i]['finish'] = $d['finish'];
                    $lineItem[$i]['uvCuredId'] = $this->getUVCuredNameById($d['uvCured']);
                    $lineItem[$i]['sheenId'] = $this->getSheenById($d['sheen']);
                    $lineItem[$i]['shameOnId'] = ($d['sameOnBack']) ? 'BS' : '';
                    $lineItem[$i]['coreSameOnbe'] = ($d['sameOnBottom']) ? ',BE' : '';
                    $lineItem[$i]['coreSameOnte'] = ($d['sameOnTop']) ? ',TE' : '';
                    $lineItem[$i]['coreSameOnre'] = ($d['sameOnRight']) ? ',RE' : '';
                    $lineItem[$i]['coreSameOnle'] = ($d['sameOnLeft']) ? ',LE' : '';
                    $lineItem[$i]['facPaint'] = $this->getFacPaintById($d['facPaint']);
                    $lineItem[$i]['isLabels'] = $d['isLabel'];
                    $lineItem[$i]['autoNumber'] = $this->getFirstLabel($d['autoNumber']);
                    $lineItem[$i]['comment'] = $d['comment'];
                    $lineItem[$i]['isGreyedOut'] = $d['statusName'];
                    $lineItem[$i]['greyedOutClass'] = ($d['statusName'] == 'LineItemBackOrder') ? 'greyedOut' : '';
                    $lineItem[$i]['greyedOutEstNo'] = $d['backOrderEstNo'];
                    $lineItem[$i]['topEdgeName'] = $this->getEdgeNameById($d['topEdge']);
                    $lineItem[$i]['bottomEdgeName'] = $this->getEdgeNameById($d['bottomEdge']);
                    $lineItem[$i]['rightEdgeName'] = $this->getEdgeNameById($d['rightEdge']);
                    $lineItem[$i]['leftEdgeName'] = $this->getEdgeNameById($d['leftEdge']);
                    $lineItem[$i]['millingName'] = $this->getUnitNameById($d['milling']);
                    $lineItem[$i]['millingDescription'] = $d['millingDescription'];
                    $i++;
                }
            }
            
            $calSqrft = number_format($calSqrftP + $calSqrftV + $calSqrftD,2);
            
            return ['lineItem'=>$lineItem,'calSqrft'=>$calSqrft];
        }
    }
    
    private function getGrainPattern($id,$type=''){
        if($type=='door'){
            $grainId= $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(['id'=>$id]);
            if(!empty($grainId)){
                $id=$grainId->getGrain();
            } else {
                $id = 0;
            }
        }
        else if($type == 'plywood'){
            $data= $this->getDoctrine()->getRepository('AppBundle:GrainPattern')->findOneBy(['id'=>$id]);
            if(!empty($data)){
                return $data->getAbbr();
            } else {
                return '';
            }
        }
        $data = $this->getDoctrine()->getRepository('AppBundle:GrainDirection')->findOneBy(['id' => $id]);
        if (!empty($data)) {
            return $data->getName();
        } else {
            return '';
        }
    }

    private function getGrainNameById($id) {
        $grain = $this->getDoctrine()->getRepository('AppBundle:GrainPattern')
                            ->findOneById($id);
        return $grain->getAbbr();
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
                            's.abbr as status',
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
                    $query1->setParameter('from', date('Y-m-d', strtotime($startDate)).'T00:00:00')
                            ->setParameter('to', date('Y-m-d', strtotime($endDate)).'T23:59:59');
                } else if (!empty($startDate) && empty($endDate) || ($startDate == $endDate && !empty($endDate) && !empty($startDate))) {
                    $query1->setParameter('from', date('Y-m-d', strtotime($startDate)).'T00:00:00');
                } else if (empty($startDate) && !empty($endDate)) {
                    $query1->setParameter('to', date('Y-m-d', strtotime($endDate)).'T23:59:59');
                }
                $quotes = $query1->orderBy('q.estimatedate', 'DESC')->getQuery()->getResult();
                //$quotes=$query1->getQuery()->getSQL();print_r($quotes);die;
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
        $doorData= $this->getDoorDataByQuoteId($quoteId);
        $arr = array_merge($veneerData, $plywoodData, $doorData);
        $final = $this->arraySortByColumn($arr, 'lineItemNum');
//        print_r($final);die;
        $images_destination = $this->container->getParameter('images_destination');
        $lineItemHTML = '';
        $plyHtmlV ='';
        $plyHtmlC ='';
        $plyHtmlS ='';
        $venHTMLV ='';
        $venHTMLS ='';
        $htmlBlocks = [];

        if (!empty($final)) {
            foreach ($final as $v) {
                if (!empty($v['type']) && $v['type'] == 'Plywood') {
                    //for ($i=0;$i<$v['quantity'];$i++) {
                        $htmlBlocks[] = $this->getOrderTicketPlywoodHTMLV($v, $images_destination);
                        $htmlBlocks[] = $this->getOrderTicketPlywoodHTMLC($v, $images_destination);
                        $htmlBlocks[] = $this->getOrderTicketPlywoodHTMLS($v, $images_destination);
                    //}
                }
                if (!empty($v['type']) && $v['type'] == 'Veneer') {
                    //for ($i=0;$i<$v['quantity'];$i++) {
                        $htmlBlocks[] = $this->getOrderTicketVeneerHTMLV($v, $images_destination);
                        $htmlBlocks[] = $this->getOrderTicketVeneerHTMLS($v, $images_destination);
                    //}
                }
//                if (!empty($v['type']) && $v['type'] == 'Door') {
//                    $lineItemHTML .= $this->getOrderTicketOrderHTML($v, $images_destination);
//                }
            }
        }

        $html = $this->getOrderTicketHTML($lineItemHTML);

        return new Response(
            $this->get('knp_snappy.pdf')
                ->getOutputFromHtml(
                    $htmlBlocks, [
                        'orientation' => 'portrait',
                        'default-header' => false,
                        'page-height' => 203,
                        'page-width'  => 140,
                    ]
                ),
            200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Test.pdf"'
            ]);
    }

    private function getPlywoodDataByQuoteId($quoteId) {
        if (!empty($quoteId)) {
            $query = $this->getDoctrine()->getManager();
            $result = $query->createQueryBuilder()
                    ->select(['v.id','v.quantity', 'v.plywoodWidth as width','v.widthFraction', 'v.plywoodLength as length','v.lengthFraction', 'v.comments', 'v.speciesId', 'v.topEdge', 'v.bottomEdge',
                        'v.rightEdge', 'v.leftEdge', "v.finishThickId as pThicknessName", 'v.finishThickType', "'Plywood' as type",
                        "v.finThickFraction", "v.thickness as panelThicknessName", 'v.lineItemNum', 'v.isSequenced','v.isLabels','st.statusName'])
                    ->from('AppBundle:Plywood', 'v')
                    ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = v.id")
                    ->leftJoin('AppBundle:Status', 'st', 'WITH', "st.id = lis.statusId")
                    ->leftJoin('AppBundle:Quotes', 'q', 'WITH', 'v.quoteId = q.id')
                    ->addSelect(['q.refNum', 'q.deliveryDate'])
                    ->leftJoin('AppBundle:Profile', 'u', 'WITH', "q.customerId = u.userId")
                    ->addSelect(['u.company as username'])
                    ->leftJoin('AppBundle:Species', 's', 'WITH', "v.speciesId = s.id")
                    ->addSelect(['s.name as SpecieName'])
                    ->leftJoin('AppBundle:GrainPattern', 'gp', 'WITH', "v.patternId = gp.id")
                    ->addSelect(['gp.name as patternName'])
                    ->leftJoin('AppBundle:Thickness', 't', 'WITH', "v.thicknessId = t.id")
                    ->addSelect(['t.name as thicknessName'])
                    ->leftJoin('AppBundle:FaceGrade', 'fg', 'WITH', "v.gradeId = fg.id")
                    ->addSelect(['fg.name as faceGradeName'])
                    ->leftJoin('AppBundle:Pattern', 'p', 'WITH', "p.id = v.patternMatch")
                    ->addSelect(['p.name as patternMatch'])
                    ->leftJoin('AppBundle:CoreType', 'ct', 'WITH', "ct.id = v.coreType")
                    ->addSelect(['ct.name as coreType'])
                    ->leftJoin('AppBundle:BackerGrade', 'bgrd', 'WITH', "v.backerId = bgrd.id")
                    ->addSelect(['bgrd.name as backerName'])
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
                    ->where("st.statusName != 'LineItemBackOrder' AND v.quoteId = " . $quoteId)
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

            /* $plywoodRecords = $query->createQueryBuilder()
            ->select(['p.id, p.quantity, p.speciesId, p.patternId,p.patternMatch, p.gradeId,p.backerId,p.finishThickId,p.finishThickType, p.finThickFraction, p.plywoodWidth, p.plywoodLength, p.coreType,p.sellingPrice,p.totalCost,p.widthFraction, p.lengthFraction,p.grainPatternId,p.backOrderEstNo,p.edgeDetail,p.topEdge,p.bottomEdge,p.rightEdge,p.leftEdge,p.milling,p.unitMesureCostId,p.finish,p.comments,p.uvCuredId,p.sheenId,p.shameOnId,p.coreSameOnbe,p.coreSameOnte,p.coreSameOnre,p.coreSameOnle,p.facPaint,p.isLabels, p.autoNumber, p.coreType,s.statusName'])
            ->from('AppBundle:Plywood', 'p')
            ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = p.id")
            ->leftJoin('AppBundle:Status', 's', 'WITH', "s.id = lis.statusId")
            ->leftJoin('AppBundle:GrainPattern', 'gp', 'WITH', "p.patternId = gp.id")
            ->addSelect(['gp.name as grain'])
            ->leftJoin('AppBundle:Thickness', 't', 'WITH', "p.thicknessId = t.id")
            ->addSelect(['t.name as thicknessName'])
            ->leftJoin('AppBundle:Pattern', 'pat', 'WITH', "pat.id = p.patternMatch")
            ->addSelect(['pat.name as pattern'])
            ->leftJoin('AppBundle:BackerGrade', 'bgrd', 'WITH', "p.backerId = bgrd.id")
            ->addSelect(['bgrd.name as backerName'])
            ->where("s.isActive = 1 and lis.isActive=1 AND lis.lineItemType= :lineItemType AND p.quoteId = :quoteId and p.isActive=1 AND s.statusName !='LineItemBackOrder'")
            ->orderBy('p.id','ASC')
            ->setParameter('quoteId', $qId)
            ->setParameter('lineItemType', 'Plywood')
            ->getQuery()
            ->getResult(); */

            $result = $query->createQueryBuilder()
                    ->select(['v.quantity', 'v.width','v.widthFraction', 'v.length','v.lengthFraction', 'v.comments', 'v.sequenced', 'v.lineItemNum', "'Veneer' as type,st.statusName"])
                    ->from('AppBundle:Veneer', 'v')
                    ->leftJoin('AppBundle:LineItemStatus', 'lis', 'WITH', "lis.lineItemId = v.id")
                    ->leftJoin('AppBundle:Status', 'st', 'WITH', "st.id = lis.statusId")
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
                    ->leftJoin('AppBundle:CoreType', 'ct', 'WITH', "ct.id = v.coreTypeId")
                    ->addSelect(['ct.name as coreType'])
                    ->addSelect(['o.orderDate as orderDate', 'o.estNumber as estNumber'])
                    ->where("st.statusName != 'LineItemBackOrder' AND v.quoteId = " . $quoteId)
                    ->getQuery()
                    ->getResult();
        } else {
            $result = [];
        }
        return $result;
    }

    private function getDoorDataByQuoteId($quoteId) {
        if (!empty($quoteId)) {
            $query = $this->getDoctrine()->getManager();
            $result = $query->createQueryBuilder()
                ->select(['d.id','d.qty as quantity','d.width','d.length','d.comment as comments','d.lineItemNum','d.sequence as sequenced',
                    'd.finishThickId as pThicknessName','d.panelThickness as panelThicknessName', "'Door' as type"])
                ->from('AppBundle:Doors', 'd')
                ->leftJoin('AppBundle:Skins', 's', 'WITH', 'd.id = s.doorId AND d.quoteId = s.quoteId')
                ->addSelect(['s.species as SpecieName','s.grade','s.pattern'])
                ->leftJoin('AppBundle:Quotes', 'q', 'WITH', 'd.quoteId = q.id')
                ->addSelect(['q.refNum','q.deliveryDate','q.controlNumber','q.version'])
                ->leftJoin('AppBundle:Profile', 'u', 'WITH', 'q.customerId = u.userId')
                ->addSelect(['u.company as username'])
                ->leftJoin('AppBundle:GrainDirection', 'gd', 'WITH', "s.grainDir = gd.id")
                ->addSelect(['gd.name as grainDirectionName'])
                ->leftJoin('AppBundle:Pattern', 'p', 'WITH', "s.pattern = p.id")
                ->addSelect(['p.name as patternName'])
                ->leftJoin('AppBundle:Thickness', 't', 'WITH', "s.thickness = t.id")
                ->addSelect(['t.name as thicknessName'])
                ->leftJoin('AppBundle:FaceGrade', 'fg', 'WITH', "s.grade = fg.id")
                ->addSelect(['fg.name as faceGradeName'])
                ->where('d.quoteId = '.$quoteId)
                ->getQuery()
                ->getResult();
            return $result;
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

    private function getOrderTicketVeneerHTMLV($v, $images_destination) {
        $veneerHtml = '';
        $veneerHtml .= '<div class="ticketWrap">
        <div class="ticketScreen veneer">
            <table class="ticketHead">
                <tr>
                    <td class="lftLogo"><img src="' . $images_destination . '/ticket-images/logo-ico.png" alt="Department Logo"></td>
                    <td class="dep">Veneer Department</td>
                </tr> 
            </table>
            <table class="ticketDesc">
                <tr>
                    <td class="cellLabel"></td>
                    <td class="cellDesc"><label class="custName">' . $v['username'] . '</label></td>
                </tr>
                <tr>
                    <td class="cellLabel"></td>
                    <td class="cellDesc">PO: ' . $v['refNum'] . '<br>Order: ' . $v['estNumber'] . '<br>Item: '.$v['lineItemNum'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"></td>
                    <td class="cellDesc"><hr></td>
                </tr>
            </table>
            <table class="ticketDesc" style="min-height:300px;">
                <tr>
                    <td class="cellLabel"><label>Quantity</label></td>
                    <td class="cellDesc">' . $v['quantity'] . ' Pieces</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Species</label></td>
                    <td class="cellDesc">' . $v['SpecieName'] . ' - ' . $v['patternName'] . '</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Veneer Size</label></td>
                    <td class="cellDesc">'.($v['width'] + 1).' '.$this->float2rat($v['widthFraction']).'" x '.($v['length']+1).' '.$this->float2rat($v['lengthFraction']).'"</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Thickness</label></td>
                    <td class="cellDesc">'.$v['thicknessName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Species</label></td>
                    <td class="cellDesc">'.$v['SpecieName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Face Grade</label></td>
                    <td class="cellDesc">'.$v['faceGradeName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Grain Direction</label></td>
                    <td class="cellDesc">'.$v['grainDirectionName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Back</label></td>
                    <td class="cellDesc">'.$v['backerName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Sequenced</label></td>
                    <td class="cellDesc">'.($v['sequenced'] ? 'Yes' : 'No').'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Pattern</label></td>
                    <td class="cellDesc">'.$v['patternName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Label</label></td>
                    <td class="cellDesc">No</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Core</label></td>
                    <td class="cellDesc">'.$v['coreType'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Notes:</label></td>
                    <td class="cellDesc"><strong>'.$v['comments'].'</strong></td>
                </tr>
            </table>

            <table class="ticketFoot">
                <tr>
                    <td class="lftFoot"></td>
                    <td class="dep footIN">
                        <table>
                            <tr>
                                <td><span class="itTxt t-left">' . date('l | M d', strtotime($v['deliveryDate'])) . '</span></td>
                            </tr>
                        </table>
                    </td>
                </tr> 
            </table>
        </div>
    </div>';
        $html='';
        if(!empty($veneerHtml)){
            $html .= '<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700" rel="stylesheet">
                    <style>
                        body{font-family:"Roboto Condensed",sans-serif;font-weight:300;}table{width:100%;border-collapse:collapse;border-spacing:0;}h1,h2,h3,label,strong{font-weight:400;font-family:inherit;}table.ticketFoot{margin-top:40px;}
                        .ticketWrap{width:600px;margin:auto;}.ticketWrap.broad{width:1132px;}.ticketScreen{padding:20px 0;}.ticketHead{margin-bottom:30px;}.lftLogo,.cellLabel{width:121px;line-height:0;}.lftFoot{width:124px;line-height:0;}.ticketHead td,.ticketDesc td{padding:0 6px;vertical-align:middle;text-align:left;}td.dep{padding:0 30px;color:#ffffff;font-size:38px;font-weight:400;height:90px;}.lftLogo img{margin:0 0 0 6px;display:block;padding:0;}.ticketHead td.lftLogo,.ticketDesc td.cellLabel{padding-left:0;}td.cellDesc{font-size:24px;color:#212121;}td.cellLabel{font-size:14px;color:#999999;text-align:right;vertical-align:top;letter-spacing:0.3px;}.ticketDesc td.cellDesc{padding-left:0;}.cellLabel label{padding-right:10px;display:inline-block;padding-top:13px;font-weight:300;}label.custName,td.cellDesc label.custName{font-size:36px;font-family:\'Roboto Condensed\',sans-serif;font-weight:700;color:#212121;padding:0 0 20px;line-height:1;}table.ticketDesc td{padding:0 6px 3px;}table.ticketDesc td.cellLabel{padding-top:3px;}td.cellDesc label{padding:0 8px 3px;display:inline-block;vertical-align:middle;color:#f02232;font-size:14px;font-weight:400;}table td hr{height:1px;display:block;width:90%;background-color:#212121;border:0;margin:0 0 10px;}td.cellDesc .noteTxt{font-size:15px;display:inline-block;padding-bottom:3px;}td.cellDesc label.grnTxt{font-size:10px;color:#33b598;padding:0 0 3px;}td.dep.footIN{height:auto;padding:10px 15px;}.footIN table td{padding:0;vertical-align:bottom;color:#f02232;font-size:13px;text-align:center;}td span.itTxt{font-size:32px;color:#ffffff;}td.itBx{width:110px;}.footIN table td.footImg{padding:10px 0 10px 20px;text-align:left;height:90px;}.footImg img{margin:0 16px;display:inline;}.ticketFoot td.cellDesc label.custName{font-size:32px;}.ticketWrap table.hlfGrdTable{width:100%;}.ticketWrap table.hlfGrdTable td{width:50%;vertical-align:top;padding-left:20px;padding-right:20px;border-left:solid 1px #212121;}.ticketWrap table.hlfGrdTable td.cellLabel{width:121px;}.ticketWrap table.hlfGrdTable td.cellDesc{width:auto;}.ticketWrap table.hlfGrdTable td.cellLabel,.ticketWrap table.hlfGrdTable td.cellDesc{padding-left:6px;padding-right:6px;border:0;}.ticketWrap table.hlfGrdTable td.fstGrd{padding-left:0;padding-right:0;border:0;}.ticketWrap.broad table.ticketHead .lftLogo,.ticketWrap.broad table.ticketFoot .cellLabel,.ticketWrap.broad table.ticketFoot .lftFoot{width:131px;}.ticketWrap.broad .lftLogo img{margin-left:16px;}.ticketWrap table.ticketDesc td.cellDesc td,.ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td{padding:0;border:0;}.ticketWrap table.ticketDesc td.cellDesc td, .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td td.w20p,td.w20p{width:20%;}td.dep.footIN span.t-left{display:block;padding:10px 4px 6px;text-align:left;}
                        .veneer td.dep{background-color:#9c946b;}.core td.dep,.laminating td.dep{background-color:#9c946b;}.sanding td.dep{background-color:#9c946b;}.door td.dep,.shipping td.dep{background-color:#9c946b;}
                    </style>
                </head>
                <body>
                ' . $veneerHtml . '
                </body>
            </html>';
        }
        return $html;
    }

    private function getOrderTicketVeneerHTMLS($v, $images_destination) {
        $veneerHtml = '';
        $veneerHtml .= '<div class="ticketWrap">
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
                    <td class="cellDesc">PO: ' . $v['refNum'] . '<br>Order: ' . $v['estNumber'] . '<br>Item: '.$v['lineItemNum'].'</td>
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
                    <td class="cellLabel"><label>Veneer Size</label></td>
                    <td class="cellDesc">'.($v['width'] + 1).' '.$this->float2rat($v['widthFraction']).'" x '.($v['length']+1).' '.$this->float2rat($v['lengthFraction']).'"</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Thickness</label></td>
                    <td class="cellDesc">'.$v['thicknessName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Species</label></td>
                    <td class="cellDesc">'.$v['SpecieName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Face Grade</label></td>
                    <td class="cellDesc">'.$v['faceGradeName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Grain Direction</label></td>
                    <td class="cellDesc">'.$v['grainDirectionName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Back</label></td>
                    <td class="cellDesc">'.$v['backerName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Sequenced</label></td>
                    <td class="cellDesc">'.($v['sequenced'] ? 'Yes' : 'No').'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Pattern</label></td>
                    <td class="cellDesc">'.$v['patternName'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Label</label></td>
                    <td class="cellDesc">No</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Core</label></td>
                    <td class="cellDesc">'.$v['coreType'].'</td>
                </tr>
                <tr>
                    <td class="cellLabel"><label>Notes:</label></td>
                    <td class="cellDesc"><strong>'.$v['comments'].'</strong></td>
                </tr>
            </table>
            <table class="ticketFoot">
                <tr>
                    <td class="lftFoot"></td>
                    <td class="dep footIN">
                        <table>
                            <tr>
                                <td><span class="itTxt t-left">' . date('l | M d', strtotime($v['deliveryDate'])) . '</span></td>
                            </tr>
                        </table>
                    </td>
                </tr> 
            </table>
        </div>
    </div>';
        $html='';
        if(!empty($veneerHtml)){
            $html .= '<!DOCTYPE html>
            <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700" rel="stylesheet">
                    <style>
                        body{font-family:"Roboto Condensed",sans-serif;font-weight:300;}table{width:100%;border-collapse:collapse;border-spacing:0;}h1,h2,h3,label,strong{font-weight:400;font-family:inherit;}table.ticketFoot{margin-top:50px;}
                        .ticketWrap{width:600px;margin:auto;}.ticketWrap.broad{width:1132px;}.ticketScreen{padding:20px 0;}.ticketHead{margin-bottom:30px;}.lftLogo,.cellLabel{width:121px;line-height:0;}.lftFoot{width:124px;line-height:0;}.ticketHead td,.ticketDesc td{padding:0 6px;vertical-align:middle;text-align:left;}td.dep{padding:0 30px;color:#ffffff;font-size:38px;font-weight:400;height:95px;}.lftLogo img{margin:0 0 0 6px;display:block;padding:0;}.ticketHead td.lftLogo,.ticketDesc td.cellLabel{padding-left:0;}td.cellDesc{font-size:24px;color:#212121;}td.cellLabel{font-size:14px;color:#999999;text-align:right;vertical-align:top;letter-spacing:0.3px;}.ticketDesc td.cellDesc{padding-left:0;}.cellLabel label{padding-right:10px;display:inline-block;padding-top:13px;font-weight:300;}label.custName,td.cellDesc label.custName{font-size:42px;font-family:\'Roboto Condensed\',sans-serif;font-weight:700;color:#212121;padding:0 0 30px;line-height:1;}table.ticketDesc td{padding:0 6px 3px;}table.ticketDesc td.cellLabel{padding-top:3px;}td.cellDesc label{padding:0 8px 3px;display:inline-block;vertical-align:middle;color:#f02232;font-size:14px;font-weight:400;}table td hr{height:1px;display:block;width:90%;background-color:#212121;border:0;margin:0 0 10px;}td.cellDesc .noteTxt{font-size:15px;display:inline-block;padding-bottom:3px;}td.cellDesc label.grnTxt{font-size:10px;color:#33b598;padding:0 0 3px;}td.dep.footIN{height:auto;padding:10px 15px;}.footIN table td{padding:0;vertical-align:bottom;color:#f02232;font-size:13px;text-align:center;}td span.itTxt{font-size:32px;color:#ffffff;}td.itBx{width:110px;}.footIN table td.footImg{padding:10px 0 10px 20px;text-align:left;height:90px;}.footImg img{margin:0 16px;display:inline;}.ticketFoot td.cellDesc label.custName{font-size:32px;}.ticketWrap table.hlfGrdTable{width:100%;}.ticketWrap table.hlfGrdTable td{width:50%;vertical-align:top;padding-left:20px;padding-right:20px;border-left:solid 1px #212121;}.ticketWrap table.hlfGrdTable td.cellLabel{width:121px;}.ticketWrap table.hlfGrdTable td.cellDesc{width:auto;}.ticketWrap table.hlfGrdTable td.cellLabel,.ticketWrap table.hlfGrdTable td.cellDesc{padding-left:6px;padding-right:6px;border:0;}.ticketWrap table.hlfGrdTable td.fstGrd{padding-left:0;padding-right:0;border:0;}.ticketWrap.broad table.ticketHead .lftLogo,.ticketWrap.broad table.ticketFoot .cellLabel,.ticketWrap.broad table.ticketFoot .lftFoot{width:131px;}.ticketWrap.broad .lftLogo img{margin-left:16px;}.ticketWrap table.ticketDesc td.cellDesc td,.ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td{padding:0;border:0;}.ticketWrap table.ticketDesc td.cellDesc td, .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td td.w20p,td.w20p{width:20%;}td.dep.footIN span.t-left{display:block;padding:10px 4px 6px;text-align:left;}
                        .veneer td.dep{background-color:#9c946b;}.core td.dep,.laminating td.dep{background-color:#9c946b;}.sanding td.dep{background-color:#9c946b;}.door td.dep,.shipping td.dep{background-color:#9c946b;}
                    </style>
                </head>
                <body>
                ' . $veneerHtml . '
                </body>
            </html>';
        }
        return $html;
    }

    private function getOrderTicketOrderHTML($v, $images_destination) {
        $doorHtml = '';
        $doorHtml .= '';
        return $doorHtml;
    }

    private function getOrderTicketPlywoodHTMLV($v, $images_destination) {
//        $topEdgeName = !empty($v['topEdgeName']) ? $v['topEdgeName'] : 'N/A';
//        $bottomEdgeName = !empty($v['bottomEdgeName']) ? $v['bottomEdgeName'] : 'N/A';
//        $rightEdgeName = !empty($v['rightEdgeName']) ? $v['rightEdgeName'] : 'N/A';
//        $topEdgeMaterialName = !empty($v['topEdgeMaterialName']) ? $v['topEdgeMaterialName'] : 'N/A';
//        $rightEdgeMaterialName = !empty($v['rightEdgeMaterialName']) ? $v['rightEdgeMaterialName'] : 'N/A';
//        $leftEdgeMaterialName = !empty($v['leftEdgeMaterialName']) ? $v['leftEdgeMaterialName'] : 'N/A';
//        $topSpeciesName = !empty($v['topEdgeName']) ? $v['topEdgeName'] : 'N/A';
//        $bottomSpeciesName = !empty($v['bottomSpeciesName']) ? $v['bottomSpeciesName'] : 'N/A';
//        $rightSpeciesName = !empty($v['rightSpeciesName']) ? $v['rightSpeciesName'] : 'N/A';
//        $leftSpeciesName = !empty($v['leftSpeciesName']) ? $v['leftSpeciesName'] : 'N/A';
//        $leftEdgeName = !empty($v['leftEdgeName']) ? $v['leftEdgeName'] : 'N/A';
//        $bottomEdgeMaterialName = !empty($v['bottomEdgeMaterialName']) ? $v['bottomEdgeMaterialName'] : 'N/A';
//
//
//        $edge = '';
//        if (($topEdgeName !== 'N/A' && $topEdgeName !== 'None') ||
//                ($bottomEdgeName !== 'N/A' && $bottomEdgeName !== 'None') ||
//                ($rightEdgeName !== 'N/A' && $rightEdgeName !== 'None') ||
//                ($leftEdgeName !== ' N/A' && $leftEdgeName !== 'None')) {
//            if ($topEdgeName !== 'N/A' && $topEdgeName !== 'None') {
//                $edge .= 'TE: ' . $topEdgeName . ' &nbsp; |  &nbsp;' . $topEdgeMaterialName . ' &nbsp; |  &nbsp;' . $topSpeciesName . ' <br>';
//            }
//            if ($bottomEdgeName !== 'N/A' && $bottomEdgeName !== 'None') {
//                $edge .= 'TE: ' . $bottomEdgeName . ' &nbsp; |  &nbsp;' . $bottomEdgeMaterialName . ' &nbsp; |  &nbsp;' . $bottomSpeciesName . ' <br>';
//            }
//            if ($rightEdgeName !== 'N/A' && $rightEdgeName !== 'None') {
//                $edge .= 'TE: ' . $rightEdgeName . ' &nbsp; |  &nbsp;' . $rightEdgeMaterialName . ' &nbsp; |  &nbsp;' . $rightSpeciesName . ' <br>';
//            }
//            if ($leftEdgeName !== 'N/A' && $leftEdgeName !== 'None') {
//                $edge .= 'TE: ' . $leftEdgeName . ' &nbsp; |  &nbsp;' . $leftEdgeMaterialName . ' &nbsp; |  &nbsp;' . $leftSpeciesName . ' <br>';
//            }
//        } else {
//            $edge = 'No';
//        }

        //print_r($v['quantity']);die;
                $plywoodHtml = '';
                $plywoodHtml .= '<div class="ticketWrap">
                <div class="ticketScreen veneer">
                    <table class="ticketHead">
                        <tr>
                            <td class="lftLogo"><img src="' . $images_destination . '/ticket-images/logo-ico.png" alt="Department Logo"></td>
                            <td class="dep">Veneer Department</td>
                        </tr> 
                    </table>
                    <table class="ticketDesc">
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><label class="custName">' . $v["username"] . '</label></td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc">PO: ' . $v["refNum"] . '<br>Order: ' . $v["estNumber"] . '<br>Item: '.$v["lineItemNum"].'</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"></td>
                            <td class="cellDesc"><hr></td>
                        </tr>
                    </table>
                    <table class="ticketDesc" style="min-height:300px;">
                        <tr>
                            <td class="cellLabel"><label>Quantity</label></td>
                            <td class="cellDesc">' . $v["quantity"] . ' Pieces</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Veneer Size</label></td>
                            <td class="cellDesc">'.($v["width"] + 1).' '.$this->float2rat($v["widthFraction"]).'" x '.($v["length"]+1).' '.$this->float2rat($v["lengthFraction"]).'"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Thickness</label></td>
                            <td class="cellDesc">' . $v["thicknessName"] . '"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Species</label></td>
                            <td class="cellDesc">' . $v["SpecieName"] . ' - ' . $v["patternName"] . '</td>
                        </tr>
                        <!--<tr>
                            <td class="cellLabel"><label>Species</label></td>
                            <td class="cellDesc">' . $v["patternName"] . '</td>
                        </tr>-->
                        <tr>
                            <td class="cellLabel"><label>Face Grade</label></td>
                            <td class="cellDesc">' . $v["faceGradeName"] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Grain Direction</label></td>
                            <td class="cellDesc">' . $v["grainDirectionName"] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Back</label></td>
                            <td class="cellDesc">' . $v["backerName"] . '</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Sequenced</label></td>
                            <td class="cellDesc">'.($v["isSequenced"] == 1 ? "Yes" : "No").'</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Pattern</label></td>
                            <td class="cellDesc">'.$v['patternMatch'].'</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Label</label></td>
                            <td class="cellDesc">'.($v["isLabels"] ? "Yes" : "No").'</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Core</label></td>
                            <td class="cellDesc">'.$v["coreType"].' - '.$this->float2rat($v["panelThicknessName"]).'"</td>
                        </tr>
                        <tr>
                            <td class="cellLabel"><label>Notes:</label></td>
                            <td class="cellDesc"><strong>'.$v["comments"].'</strong></td>
                        </tr>
                    </table>
                    <table class="ticketFoot">
                        <tr>
                            <td class="lftFoot"></td>
                            <td class="dep footIN">
                                <table>
                                    <tr>
                                        <td><span class="itTxt t-left">'.date("D | M d",strtotime($v["deliveryDate"])).'</span></td>
                                    </tr>
                                </table>
                            </td>
                        </tr> 
                    </table>
                </div>
            </div>';
        $html='';
        if(!empty($plywoodHtml)){
            $html .= '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700" rel="stylesheet">
                <style>
                    body{font-family:"Roboto Condensed",sans-serif;font-weight:300;}table{width:100%;border-collapse:collapse;border-spacing:0;}h1,h2,h3,label,strong{font-weight:400;font-family:inherit;}table.ticketFoot{margin-top:50px;}
.ticketWrap{width:600px;margin:auto;}.ticketWrap.broad{width:1132px;}.ticketScreen{padding:20px 0;}.ticketHead{margin-bottom:30px;}.lftLogo,.cellLabel{width:121px;line-height:0;}.lftFoot{width:124px;line-height:0;}.ticketHead td,.ticketDesc td{padding:0 6px;vertical-align:middle;text-align:left;}td.dep{padding:0 30px;color:#ffffff;font-size:38px;font-weight:400;height:95px;}.lftLogo img{margin:0 0 0 6px;display:block;padding:0;}.ticketHead td.lftLogo,.ticketDesc td.cellLabel{padding-left:0;}td.cellDesc{font-size:26px;color:#212121;}td.cellLabel{font-size:14px;color:#999999;text-align:right;vertical-align:top;letter-spacing:0.3px;}.ticketDesc td.cellDesc{padding-left:0;}.cellLabel label{padding-right:10px;display:inline-block;padding-top:13px;font-weight:300;}label.custName,td.cellDesc label.custName{font-size:42px;font-family:"Roboto Condensed",sans-serif;font-weight:700;color:#212121;padding:0 0 30px;line-height:1;}table.ticketDesc td{padding:0 6px 3px;}table.ticketDesc td.cellLabel{padding-top:3px;}td.cellDesc label{padding:0 8px 3px;display:inline-block;vertical-align:middle;color:#f02232;font-size:14px;font-weight:400;}table td hr{height:1px;display:block;width:90%;background-color:#212121;border:0;margin:0 0 10px;}td.cellDesc .noteTxt{font-size:15px;display:inline-block;padding-bottom:3px;}td.cellDesc label.grnTxt{font-size:10px;color:#33b598;padding:0 0 3px;}td.dep.footIN{height:auto;padding:10px 15px;}.footIN table td{padding:0;vertical-align:bottom;color:#f02232;font-size:13px;text-align:center;}td span.itTxt{font-size:32px;color:#ffffff;}td.itBx{width:110px;}.footIN table td.footImg{padding:10px 0 10px 20px;text-align:left;height:90px;}.footImg img{margin:0 16px;display:inline;}.ticketFoot td.cellDesc label.custName{font-size:32px;}.ticketWrap table.hlfGrdTable{width:100%;}.ticketWrap table.hlfGrdTable td{width:50%;vertical-align:top;padding-left:20px;padding-right:20px;border-left:solid 1px #212121;}.ticketWrap table.hlfGrdTable td.cellLabel{width:121px;}.ticketWrap table.hlfGrdTable td.cellDesc{width:auto;}.ticketWrap table.hlfGrdTable td.cellLabel,.ticketWrap table.hlfGrdTable td.cellDesc{padding-left:6px;padding-right:6px;border:0;}.ticketWrap table.hlfGrdTable td.fstGrd{padding-left:0;padding-right:0;border:0;}.ticketWrap.broad table.ticketHead .lftLogo,.ticketWrap.broad table.ticketFoot .cellLabel,.ticketWrap.broad table.ticketFoot .lftFoot{width:131px;}.ticketWrap.broad .lftLogo img{margin-left:16px;}.ticketWrap table.ticketDesc td.cellDesc td,.ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td{padding:0;border:0;}.ticketWrap table.ticketDesc td.cellDesc td, .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td td.w20p,td.w20p{width:20%;}td.dep.footIN span.t-left{display:block;padding:10px 4px 6px;text-align:left;}
.veneer td.dep{background-color:#9c946b;}.core td.dep,.laminating td.dep{background-color:#9c946b;}.sanding td.dep{background-color:#9c946b;}.door td.dep,.shipping td.dep{background-color:#9c946b;}
                </style>
            </head>
            <body>
            ' . $plywoodHtml . '
            </body>
        </html>';
        }
        return $html;
    }

    private function getOrderTicketPlywoodHTMLC($v, $images_destination) {
        $plywoodHtml = '';
        $plywoodHtml .= '<div class="ticketWrap">
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
                        <td class="cellDesc"><label class="custName">' . $v["username"] . '</label></td>
                    </tr>
                    <tr>
                        <td class="cellLabel"></td>
                        <td class="cellDesc">PO: ' . $v["refNum"] . '<br>Order: ' . $v["estNumber"] . '<br>Item: '.$v["lineItemNum"].'</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"></td>
                        <td class="cellDesc"><hr></td>
                    </tr>
                </table>
                <table class="ticketDesc">
                    <tr>
                        <td class="cellLabel"><label>Quantity</label></td>
                        <td class="cellDesc">' . $v["quantity"] . ' Pieces</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Species</label></td>
                        <td class="cellDesc">' . $v["SpecieName"] . ' - ' . $v["patternName"] . '</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Cut Size</label></td>
                        <td class="cellDesc">'.($v["width"] + 1).' '.$this->float2rat($v["widthFraction"]).'" x '.($v["length"]+1).' '.$this->float2rat($v["lengthFraction"]).'"</td>
                    </tr>
                    <!--<tr>
                        <td class="cellLabel"><label>Finished Size</label></td>
                        <td class="cellDesc">'.($v["width"] + 1).'" x '.($v["length"]+1).'"</td>
                    </tr>-->
                    <tr>
                        <td class="cellLabel"><label>Core Thickness</label></td>
                        <td class="cellDesc">'.$this->float2rat($v["panelThicknessName"]).'"</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Core</label></td>
                        <td class="cellDesc">'.$v["coreType"].'"</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Back</label></td>
                        <td class="cellDesc">'.$v["backerName"].'</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label></label></td>
                        <td class="cellDesc"></td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label></label></td>
                        <td class="cellDesc"></td>
                    </tr>
                    <!--<tr>
                        <td class="cellLabel"><label>Sequenced</label></td>
                        <td class="cellDesc">'.($v["isSequenced"] == 1 ? "Yes" : "No").'</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Label</label></td>
                        <td class="cellDesc">'.($v["isLabels"] ? "Yes" : "No").'</td>
                    </tr>-->
                    <tr>
                        <td class="cellLabel"><label>Notes:</label></td>
                        <td class="cellDesc"><strong>'.$v["comments"].'</strong></td>
                    </tr>
                </table>
                <table class="ticketFoot">
                    <tr>
                        <td class="lftFoot"></td>
                        <td class="dep footIN">
                            <table>
                                <tr>
                                    <td><span class="itTxt t-left">'.date("D | M d",strtotime($v["deliveryDate"])).'</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr> 
                </table>
            </div>
        </div>';
        $html='';
        if(!empty($plywoodHtml)){
            $html .= '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700" rel="stylesheet">
                <style>
                    body{font-family:"Roboto Condensed",sans-serif;font-weight:300;}table{width:100%;border-collapse:collapse;border-spacing:0;}h1,h2,h3,label,strong{font-weight:400;font-family:inherit;}table.ticketFoot{margin-top:50px;}
.ticketWrap{width:600px;margin:auto;}.ticketWrap.broad{width:1132px;}.ticketScreen{padding:20px 0;}.ticketHead{margin-bottom:30px;}.lftLogo,.cellLabel{width:121px;line-height:0;}.lftFoot{width:124px;line-height:0;}.ticketHead td,.ticketDesc td{padding:0 6px;vertical-align:middle;text-align:left;}td.dep{padding:0 30px;color:#ffffff;font-size:38px;font-weight:400;height:95px;}.lftLogo img{margin:0 0 0 6px;display:block;padding:0;}.ticketHead td.lftLogo,.ticketDesc td.cellLabel{padding-left:0;}td.cellDesc{font-size:26px;color:#212121;}td.cellLabel{font-size:14px;color:#999999;text-align:right;vertical-align:top;letter-spacing:0.3px;}.ticketDesc td.cellDesc{padding-left:0;}.cellLabel label{padding-right:10px;display:inline-block;padding-top:13px;font-weight:300;}label.custName,td.cellDesc label.custName{font-size:42px;font-family:"Roboto Condensed",sans-serif;font-weight:700;color:#212121;padding:0 0 30px;line-height:1;}table.ticketDesc td{padding:0 6px 3px;}table.ticketDesc td.cellLabel{padding-top:3px;}td.cellDesc label{padding:0 8px 3px;display:inline-block;vertical-align:middle;color:#f02232;font-size:14px;font-weight:400;}table td hr{height:1px;display:block;width:90%;background-color:#212121;border:0;margin:0 0 10px;}td.cellDesc .noteTxt{font-size:15px;display:inline-block;padding-bottom:3px;}td.cellDesc label.grnTxt{font-size:10px;color:#33b598;padding:0 0 3px;}td.dep.footIN{height:auto;padding:10px 15px;}.footIN table td{padding:0;vertical-align:bottom;color:#f02232;font-size:13px;text-align:center;}td span.itTxt{font-size:32px;color:#ffffff;}td.itBx{width:110px;}.footIN table td.footImg{padding:10px 0 10px 20px;text-align:left;height:90px;}.footImg img{margin:0 16px;display:inline;}.ticketFoot td.cellDesc label.custName{font-size:32px;}.ticketWrap table.hlfGrdTable{width:100%;}.ticketWrap table.hlfGrdTable td{width:50%;vertical-align:top;padding-left:20px;padding-right:20px;border-left:solid 1px #212121;}.ticketWrap table.hlfGrdTable td.cellLabel{width:121px;}.ticketWrap table.hlfGrdTable td.cellDesc{width:auto;}.ticketWrap table.hlfGrdTable td.cellLabel,.ticketWrap table.hlfGrdTable td.cellDesc{padding-left:6px;padding-right:6px;border:0;}.ticketWrap table.hlfGrdTable td.fstGrd{padding-left:0;padding-right:0;border:0;}.ticketWrap.broad table.ticketHead .lftLogo,.ticketWrap.broad table.ticketFoot .cellLabel,.ticketWrap.broad table.ticketFoot .lftFoot{width:131px;}.ticketWrap.broad .lftLogo img{margin-left:16px;}.ticketWrap table.ticketDesc td.cellDesc td,.ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td{padding:0;border:0;}.ticketWrap table.ticketDesc td.cellDesc td, .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td td.w20p,td.w20p{width:20%;}td.dep.footIN span.t-left{display:block;padding:10px 4px 6px;text-align:left;}
.veneer td.dep{background-color:#9c946b;}.core td.dep,.laminating td.dep{background-color:#9c946b;}.sanding td.dep{background-color:#9c946b;}.door td.dep,.shipping td.dep{background-color:#9c946b;}
                </style>
            </head>
            <body>
            ' . $plywoodHtml . '
            </body>
        </html>';
        }
        return $html;
    }

    private function getOrderTicketPlywoodHTMLS($v, $images_destination) {
        $plywoodHtml = '';
        $plywoodHtml .= '<div class="ticketWrap">
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
                        <td class="cellDesc"><label class="custName">'.$v["username"].'</label></td>
                    </tr>
                    <tr>
                        <td class="cellLabel"></td>
                        <td class="cellDesc">PO: ' . $v["refNum"] . '<br>Order: ' . $v["estNumber"] . '<br>Item: '.$v["lineItemNum"].'</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"></td>
                        <td class="cellDesc"><hr></td>
                    </tr>
                </table>
                <table class="ticketDesc">
                    <tr>
                        <td class="cellLabel"><label>Quantity</label></td>
                        <td class="cellDesc">' . $v["quantity"] . ' Pieces</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Species</label></td>
                        <td class="cellDesc">' . $v["SpecieName"] . ' - ' . $v["patternName"] . '</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Cut Size</label></td>
                        <td class="cellDesc">'.($v["width"] + 1).' '.$this->float2rat($v["widthFraction"]).'" x '.($v["length"]+1).' '.$this->float2rat($v["lengthFraction"]).'"</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Finished Size</label></td>
                        <td class="cellDesc">'.($v["width"]).'" x '.($v["length"]).'"</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Fin. Thickness</label></td>
                        <td class="cellDesc">' . $v["pThicknessName"].' '.$this->float2rat($v["finThickFraction"]) . '"</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Back</label></td>
                        <td class="cellDesc">' . $v["backerName"] . '</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Sequenced</label></td>
                        <td class="cellDesc">'.($v["isSequenced"] == 1 ? "Yes" : "No").'</td>
                    </tr>
                    <!--<tr>
                        <td class="cellLabel"><label>Species</label></td>
                        <td class="cellDesc">' . $v["patternName"] . '</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Face Grade</label></td>
                        <td class="cellDesc">' . $v["faceGradeName"] . '</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Grain Direction</label></td>
                        <td class="cellDesc">' . $v["grainDirectionName"] . '</td>
                    </tr>
                    <tr>
                        <td class="cellLabel"><label>Pattern</label></td>
                        <td class="cellDesc">'.$v["patternMatch"].'</td>
                    </tr>-->
                    <tr>
                        <td class="cellLabel"><label>Label</label></td>
                        <td class="cellDesc">'.($v["isLabels"] ? "Yes" : "No").'</td>
                    </tr>
                    <!--<tr>
                        <td class="cellLabel"><label>Core</label></td>
                        <td class="cellDesc">'.$v["coreType"].' - '.$this->float2rat($v["panelThicknessName"]).'"</td>
                    </tr>-->
                    <tr>
                        <td class="cellLabel"><label>Notes:</label></td>
                        <td class="cellDesc"><strong>'.$v["comments"].'</strong></td>
                    </tr>
                </table>
                <table class="ticketFoot">
                    <tr>
                        <td class="lftFoot"></td>
                        <td class="dep footIN">
                            <table>
                                <tr>
                                    <td><span class="itTxt t-left">'.date("D | M d",strtotime($v["deliveryDate"])).'</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr> 
                </table>
            </div>
        </div>';
        $html='';
        if(!empty($plywoodHtml)){
            $html .= '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700" rel="stylesheet">
                <style>
                    body{font-family:"Roboto Condensed",sans-serif;font-weight:300;}table{width:100%;border-collapse:collapse;border-spacing:0;}h1,h2,h3,label,strong{font-weight:400;font-family:inherit;}table.ticketFoot{margin-top:50px;}
.ticketWrap{width:600px;margin:auto;}.ticketWrap.broad{width:1132px;}.ticketScreen{padding:20px 0;}.ticketHead{margin-bottom:30px;}.lftLogo,.cellLabel{width:121px;line-height:0;}.lftFoot{width:124px;line-height:0;}.ticketHead td,.ticketDesc td{padding:0 6px;vertical-align:middle;text-align:left;}td.dep{padding:0 30px;color:#ffffff;font-size:38px;font-weight:400;height:95px;}.lftLogo img{margin:0 0 0 6px;display:block;padding:0;}.ticketHead td.lftLogo,.ticketDesc td.cellLabel{padding-left:0;}td.cellDesc{font-size:26px;color:#212121;}td.cellLabel{font-size:14px;color:#999999;text-align:right;vertical-align:top;letter-spacing:0.3px;}.ticketDesc td.cellDesc{padding-left:0;}.cellLabel label{padding-right:10px;display:inline-block;padding-top:13px;font-weight:300;}label.custName,td.cellDesc label.custName{font-size:42px;font-family:"Roboto Condensed",sans-serif;font-weight:700;color:#212121;padding:0 0 30px;line-height:1;}table.ticketDesc td{padding:0 6px 3px;}table.ticketDesc td.cellLabel{padding-top:3px;}td.cellDesc label{padding:0 8px 3px;display:inline-block;vertical-align:middle;color:#f02232;font-size:14px;font-weight:400;}table td hr{height:1px;display:block;width:90%;background-color:#212121;border:0;margin:0 0 10px;}td.cellDesc .noteTxt{font-size:15px;display:inline-block;padding-bottom:3px;}td.cellDesc label.grnTxt{font-size:10px;color:#33b598;padding:0 0 3px;}td.dep.footIN{height:auto;padding:10px 15px;}.footIN table td{padding:0;vertical-align:bottom;color:#f02232;font-size:13px;text-align:center;}td span.itTxt{font-size:32px;color:#ffffff;}td.itBx{width:110px;}.footIN table td.footImg{padding:10px 0 10px 20px;text-align:left;height:90px;}.footImg img{margin:0 16px;display:inline;}.ticketFoot td.cellDesc label.custName{font-size:32px;}.ticketWrap table.hlfGrdTable{width:100%;}.ticketWrap table.hlfGrdTable td{width:50%;vertical-align:top;padding-left:20px;padding-right:20px;border-left:solid 1px #212121;}.ticketWrap table.hlfGrdTable td.cellLabel{width:121px;}.ticketWrap table.hlfGrdTable td.cellDesc{width:auto;}.ticketWrap table.hlfGrdTable td.cellLabel,.ticketWrap table.hlfGrdTable td.cellDesc{padding-left:6px;padding-right:6px;border:0;}.ticketWrap table.hlfGrdTable td.fstGrd{padding-left:0;padding-right:0;border:0;}.ticketWrap.broad table.ticketHead .lftLogo,.ticketWrap.broad table.ticketFoot .cellLabel,.ticketWrap.broad table.ticketFoot .lftFoot{width:131px;}.ticketWrap.broad .lftLogo img{margin-left:16px;}.ticketWrap table.ticketDesc td.cellDesc td,.ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td{padding:0;border:0;}.ticketWrap table.ticketDesc td.cellDesc td, .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td td.w20p,td.w20p{width:20%;}td.dep.footIN span.t-left{display:block;padding:10px 4px 6px;text-align:left;}
.veneer td.dep{background-color:#9c946b;}.core td.dep,.laminating td.dep{background-color:#9c946b;}.sanding td.dep{background-color:#9c946b;}.door td.dep,.shipping td.dep{background-color:#9c946b;}
                </style>
            </head>
            <body>
            ' . $plywoodHtml . '
            </body>
        </html>';
        }
        return $html;
    }
    
    private function getOrderTicketHTML($lineItemHTML){
        $html='';
        if(!empty($lineItemHTML)){
            $html .= '<!DOCTYPE html>
        <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700" rel="stylesheet">
                <style>
                    body{font-family:"Roboto Condensed",sans-serif;font-weight:300;}table{width:100%;border-collapse:collapse;border-spacing:0;}h1,h2,h3,label,strong{font-weight:400;font-family:inherit;}table.ticketFoot{margin-top:50px;}
                    .ticketWrap{width:600px;margin:auto;}.ticketWrap.broad{width:1132px;}.ticketScreen{padding:20px 0;}.ticketHead{margin-bottom:30px;}.lftLogo,.cellLabel{width:121px;line-height:0;}.lftFoot{width:124px;line-height:0;}.ticketHead td,.ticketDesc td{padding:0 6px;vertical-align:middle;text-align:left;}td.dep{padding:0 30px;color:#ffffff;font-size:38px;font-weight:400;height:95px;}.lftLogo img{margin:0 0 0 6px;display:block;padding:0;}.ticketHead td.lftLogo,.ticketDesc td.cellLabel{padding-left:0;}td.cellDesc{font-size:24px;color:#212121;}td.cellLabel{font-size:14px;color:#999999;text-align:right;vertical-align:top;letter-spacing:0.3px;}.ticketDesc td.cellDesc{padding-left:0;}.cellLabel label{padding-right:10px;display:inline-block;padding-top:13px;font-weight:300;}label.custName,td.cellDesc label.custName{font-size:42px;font-family:\'Roboto Condensed\',sans-serif;font-weight:700;color:#212121;padding:0 0 30px;line-height:1;}table.ticketDesc td{padding:0 6px 3px;}table.ticketDesc td.cellLabel{padding-top:3px;}td.cellDesc label{padding:0 8px 3px;display:inline-block;vertical-align:middle;color:#f02232;font-size:14px;font-weight:400;}table td hr{height:1px;display:block;width:90%;background-color:#212121;border:0;margin:0 0 10px;}td.cellDesc .noteTxt{font-size:15px;display:inline-block;padding-bottom:3px;}td.cellDesc label.grnTxt{font-size:10px;color:#33b598;padding:0 0 3px;}td.dep.footIN{height:auto;padding:10px 15px;}.footIN table td{padding:0;vertical-align:bottom;color:#f02232;font-size:13px;text-align:center;}td span.itTxt{font-size:32px;color:#ffffff;}td.itBx{width:110px;}.footIN table td.footImg{padding:10px 0 10px 20px;text-align:left;height:90px;}.footImg img{margin:0 16px;display:inline;}.ticketFoot td.cellDesc label.custName{font-size:32px;}.ticketWrap table.hlfGrdTable{width:100%;}.ticketWrap table.hlfGrdTable td{width:50%;vertical-align:top;padding-left:20px;padding-right:20px;border-left:solid 1px #212121;}.ticketWrap table.hlfGrdTable td.cellLabel{width:121px;}.ticketWrap table.hlfGrdTable td.cellDesc{width:auto;}.ticketWrap table.hlfGrdTable td.cellLabel,.ticketWrap table.hlfGrdTable td.cellDesc{padding-left:6px;padding-right:6px;border:0;}.ticketWrap table.hlfGrdTable td.fstGrd{padding-left:0;padding-right:0;border:0;}.ticketWrap.broad table.ticketHead .lftLogo,.ticketWrap.broad table.ticketFoot .cellLabel,.ticketWrap.broad table.ticketFoot .lftFoot{width:131px;}.ticketWrap.broad .lftLogo img{margin-left:16px;}.ticketWrap table.ticketDesc td.cellDesc td,.ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td{padding:0;border:0;}.ticketWrap table.ticketDesc td.cellDesc td, .ticketWrap table.hlfGrdTable table.ticketDesc td.cellDesc td td.w20p,td.w20p{width:20%;}td.dep.footIN span.t-left{display:block;padding:10px 4px 6px;text-align:left;}
                    .veneer td.dep{background-color:#9c946b;}.core td.dep,.laminating td.dep{background-color:#9c946b;}.sanding td.dep{background-color:#9c946b;}.door td.dep,.shipping td.dep{background-color:#9c946b;}
                </style>
            </head>
            <body>
            ' . $lineItemHTML . '
            </body>
        </html>';
        }
        return $html;
    }



    private function getFirstLabel($labels) {
        return explode(',', $labels)[0];
    }

    private function getUVCuredNameById($id) {
        //return ($id == 2) ? 'Pacific Collection' : ($id == 3) ? 'Custom Stain Match' : '';
        $data= $this->getDoctrine()->getRepository('AppBundle:UvCured')->findOneBy(['id'=>$id]);
        if(!empty($data)){
            return $data->getAbbr();
        } else {
            return '';
        }
    }

    private function getSheenById($id) {
        return ($id == 1) ? '3' : ($id == 2) ? '5' : ($id == 3) ? '10' : ($id == 4) ? '20' : ($id == 5) ? '30' : ($id == 6) ? '40' : ($id == 7) ? '50' : ($id == 8) ? '60' : ($id == 9) ? '70' : ($id == 10) ? '80' : ($id == 11) ? '90' : ($id == 12) ? '100' : '0';
    }

    private function getFacPaintById($id) {
        return ($id == 1) ? 'Prime Only' : ($id == 2) ? 'White / Light Color' : 'Dark Color';
    }

    private function getGrainPatternOfDoor($id) {
        $skin= $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(['doorId'=>$id]);
        return $this->getGrainNameById($skin->getGrain());
    }
}
