<?php

namespace AppBundle\Controller;

use AppBundle\Entity\MessageTemplates;
use AppBundle\Entity\Plywood;
use AppBundle\Entity\SentQuotes;
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
use AppBundle\Entity\Orders;
use AppBundle\Entity\Veneer;
use AppBundle\Entity\Doors;
use AppBundle\Entity\Skins;
use AppBundle\Entity\Files;
use AppBundle\Entity\Profile;
use AppBundle\Entity\DoorCalculator;
use AppBundle\Entity\QuoteStatus;
use AppBundle\Entity\OrderStatus;
use AppBundle\Entity\Status;
use \AppBundle\Entity\LineItemStatus;
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\Filesystem\Filesystem;

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
            $qDateForSearch = explode('T', trim($data->get('date')))[0];
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
            if (empty($qDate) || empty($quoteAddedby) || empty($custId) || empty($salsManId) || empty($termId) || empty($shipMethod) || empty($shipAddId) || empty($leadTime) || empty($status) || empty($datime)) {
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
            //                $refNocount = $this->checkIfRefNoExists($refNo);
                $refNocount=[];
                if (!empty($refNocount)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'This reference number already exists';
                    $statusCode = 422;
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully saved quote';
                    $statusCode = 200;
                    $arrApi['data']['lastInsertId'] = $this->saveQuoteData($qDate, $qDateForSearch, $quoteAddedby, $ctrlNo, $ver, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $datime);
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
     * @Method("POST")
     * params: None
     */
    public function getQuotesAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $columnName = trim($data->get('columnName'));
        $orderBy = trim($data->get('orderBy'));
        $requestColumnName='';
        if($columnName=='' || $columnName=='customer' || $columnName == 'id'){
            $requestColumnName=$columnName;
            $requestOrderBy=$orderBy;
            $columnName='q.id';
            $orderBy='DESC';
        }
        if($columnName=='estimatorId'){
            $columnName = 'q.controlNumber';
            $sortArray=['q.controlNumber'=>$orderBy];
        }
        else if($columnName == 'estimatedate')   {
            $columnName = 'q.estimatedate';
        }
        else {
            $sortArray=[$columnName=>$orderBy];
        }

        //$quotes = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findBy(array('status'=> array('Current','Hold','Dead')),$sortArray);
        $condition="q.status != :status and qs.statusId!='' and qs.isActive=1 ";
        $query = $this->getDoctrine()->getManager();
        $quotes=$query->createQueryBuilder()
        ->select(['q.controlNumber','q.version','q.customerId','q.estimatedate','q.id',
                'qs.statusId',
                's.statusName as status',
                's.abbr as stsAbbr',
                'u.company as companyname','u.fname','u.lname'
                ])
            ->from('AppBundle:Quotes', 'q')
            // ->leftJoin('AppBundle:Quotes', 'q', 'WITH', "q.id = o.quoteId")
            ->innerJoin('AppBundle:QuoteStatus', 'qs', 'WITH', "q.id = qs.quoteId")
            ->leftJoin('AppBundle:Status', 's', 'WITH', "qs.statusId=s.id ")
            ->leftJoin('AppBundle:Profile', 'u', 'WITH', "q.customerId = u.userId")
            ->where($condition)
            ->orderBy($columnName, $orderBy)
            ->setParameter('status', 'Approved')
            ->getQuery()->getResult();

        
        if (empty($quotes) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no quote.';
            $statusCode = 422;
        } else {
            /* $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the quote list.';
            $quoteList=[];
            for($i=0;$i<count($quotes);$i++) {
                $quoteList[$i]['id']                = $quotes[$i]->getId();
                $quoteList[$i]['estimateNumber']    = 'E-'.$quotes[$i]->getControlNumber().'-'.$quotes[$i]->getVersion();
                $quoteList[$i]['customername']      = strtoupper($this->getCustomerNameById($quotes[$i]->getCustomerId()));
                $quoteList[$i]['companyname']       = strtoupper($this->getCompanyById($quotes[$i]->getCustomerId()));
                $quoteList[$i]['status']            = $quotes[$i]->getStatus();
                $quoteList[$i]['stsAbbr']           = $quotes[$i]->getAbbr();

                $quoteList[$i]['estDate'] = $this->getEstimateDateFormate($quotes[$i]->getEstimateDate());
            } */

            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the order list.';
            $quoteList=[];
            for ($i=0;$i<count($quotes);$i++) {
                $quoteList[$i]=[
                    'id'=>$quotes[$i]['id'],
                    'estimateNumber'=>$quotes[$i]['controlNumber'].'-'.$quotes[$i]['version'],
                    'customername'=>$quotes[$i]['fname'],
                    'companyname'=>$quotes[$i]['companyname'],
                    // 'orderId'=>$quotes[$i]['orderId'],
                    'status'=>$quotes[$i]['status'],
                    'stsabbr' => $quotes[$i]['stsAbbr'],
                    'estDate'=>$this->getEstimateDateFormate($quotes[$i]['estimatedate']),
                ];
            }
            $arrApi['data']['quotes'] = $quoteList;

            
            if($requestColumnName=='customer'){
                $arrApi['data']['quotes'] = $this->__arraySortByColumn($quoteList, 'customername',$requestOrderBy);
            } else {
                $arrApi['data']['quotes'] = $quoteList;
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
                $quotes = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findBy(array('status'=> array('Current','Hold','Dead')),array('id'=>'desc'));
                //print_R($quotes);
                if (!empty($quotes)) {
                    $quoteId = $quotes[0]->getId();
                }
            }
            $this->updateQuoteData($quoteId);
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
                $arrApi['data']['term'] = (string) $quoteData->getTermId();
                $arrApi['data']['shipMethod'] = $this->getShipMethodNamebyId($quoteData->getShipMethdId());
                $arrApi['data']['shipMethodId'] = $quoteData->getShipMethdId();
                $arrApi['data']['billAdd'] = $this->getBillAddById($quoteData->getCustomerId());
                $arrApi['data']['shipAdd'] = $this->getShippingAddById($quoteData->getShipAddId());
                $arrApi['data']['shipAddId'] = $quoteData->getShipAddId();
                $arrApi['data']['leadTime'] = $quoteData->getLeadTime();
                $arrApi['data']['status'] = $quoteData->getStatus();
                $arrApi['data']['comment'] = $quoteData->getComment();
                $arrApi['data']['deliveryDate'] = $quoteData->getDeliveryDate();
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
     * @Method("POST")
     * params: None
     */
    public function cloneQuoteWithLineItemsAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $quoteId = trim($data->get('quoteId'));
            $lineItemIdArr= trim($data->get('lineItemIdArr'));
            $backOrder= trim($data->get('backOrder'));
            $estnumber=trim($data->get('estnumber'));
            $lineItemArr=[];
            if(!empty($lineItemIdArr)){
                $lineItemIdArr= trim($lineItemIdArr,',');
                $lineItemIdArr= explode(',',$lineItemIdArr);
                foreach ($lineItemIdArr as $v) {
                    $a= explode('-', $v);
                    $lineItemArr[$a[1]][]=$a[0];
                }
            }
            if ($backOrder == 'backOrder') {
                $editFlag = 'backOrder';
            } else if (!empty($lineItemArr)) {
                $editFlag = 'editOrder';
            } else {
                $editFlag = 'cloneQuote';
            }
            $lineItemArrD=!empty($lineItemArr['D'])?$lineItemArr['D']:[];
            $lineItemArrP=!empty($lineItemArr['P'])?$lineItemArr['P']:[];
            $lineItemArrV=!empty($lineItemArr['V'])?$lineItemArr['V']:[];
            $datime = new \DateTime('now');
            $quoteData = $this->getQuoteDataById($quoteId,$editFlag,$estnumber);
            if (empty($quoteData)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'This quote does not exists';
                $statusCode = 422;
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully cloned quote';
                $clonedQuoteId = $this->cloneQuoteData($quoteData, $datime,$quoteId,$editFlag);
                $arrApi['newCloneQuoteId']=$clonedQuoteId;

                $veneerNew=$this->cloneVeneerData($quoteId, $clonedQuoteId, $datime,$lineItemArrV,$editFlag);
                $arrApi['newCloneVeneerId']=$veneerNew['id'];

                $plyNew=$this->clonePlywoodData($quoteId, $clonedQuoteId, $datime,$lineItemArrP,$editFlag);
                $arrApi['newClonePlywoodId']=$plyNew['id'];
                
                $doorNew=$this->cloneDoorData($quoteId, $clonedQuoteId, $datime,$lineItemArrD,$editFlag);
                $arrApi['newCloneDoorId']=$doorNew['id'];
                
                $this->updateEditOrderStatusWithQuoteCost([
                    'clonedQuoteId'=>$clonedQuoteId,
                    'quoteId'=>$quoteId,
                    'plyCost'=>$plyNew['plyCost'],
                    'veneerCost'=>$veneerNew['veneerCost'],
                    'doorCost'=>$doorNew['doorCost']
                ]);
                if ($backOrder != 'backOrder') {
                    $this->updateQuoteStatus($clonedQuoteId, 'Current', null, $datime);
                }
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/quote/getLineItemCount")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
    */
    public function getLineItemsCountAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
                $quoteId = $request->query->get('id');
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully cloned quote';
                $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $quoteId,'isActive'=>1));
                $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $quoteId,'isActive'=>1));
                $doorRecords = $this->getDoctrine()->getRepository('AppBundle:Doors')->findBy(array('quoteId' => $quoteId, 'status'=> 1));
                $arrApi['data']['lineItemCount'] = count($plywoodRecords) + count($veneerRecords) + count($doorRecords);

            }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function convertToDecimal($fraction)
    {
        $numbers=explode("/",$fraction);
        return round($numbers[0]/$numbers[1],6);
    }

    private function getUnitNameById($id) {
        return ($id == 1) ? 'Running foot' : ($id == 2) ? 'Side' : ($id == 3) ? 'Square Foot' : 'Piece';
    }


    /**
     * @Route("/api/quote/printQuotePdf/{id}")
     * @Method("GET")
    */
    public function printQuotePdfAction($id,Request $request) {
        $html = $this->getQuoteHtmlByQuoteId($id);
        return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($html, array('orientation'=>'Landscape', 'default-header'=>false,  'page-size' => 'Letter')), 200, array('Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="Test.pdf"'));
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

    /**
     * @Route("/api/quote/updateQuote")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     */
    public function updateQuoteAction(Request $request)
    {
        $arrApi = array();
        $statusCode = 200;
        try {
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $qId = $data->get('id');
            $qDate = trim($data->get('date'));
            $qDateForSearch = explode('T', trim($data->get('date')))[0];
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
            $comment = trim($data->get('comment'));
            $deliveryDate = trim($data->get('deliveryDate'));
            $expFee = trim($data->get('expFee'));
            $discount = trim($data->get('discount'));
            $shipCost = trim($this->formateDeliveryCharge($data->get('shipCost')));
            $datime = new \DateTime('now');
            if (empty($qDate) || empty($quoteAddedby) || empty($custId) || empty($salsManId) || empty($termId) || empty($shipMethod) || empty($shipAddId) || empty($leadTime) || empty($status) || empty($datime)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Parameter missing';
                $statusCode = 422;
            } else {
                $updateQuote = $this->updateData($qId, $qDate, $qDateForSearch, $quoteAddedby, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $comment, $deliveryDate, $expFee, $discount, $shipCost, $datime);
                if (!$updateQuote) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Unable to update quote.';
                    $statusCode = 422;
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Updated quote.';
                }
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);

    }

    /**
     * @Route("/api/quote/getEmailQuoteData")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function getEmailQuoteDataAction(Request $request) {
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
                $projName = 'Talbert Quote Project';
                $messageTemplate = $this->replaceShortcodeFromMessage($custName,$userName,$projName,$this->getMessageTemplateforEmailQuote());
                if (!empty($messageTemplate)) {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully reterived email quote data.';
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
     * @Route("/api/quote/emailQuote")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function EmailQuoteAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $_DATA = file_get_contents('php://input');
        $_DATA = json_decode($_DATA, true);
        $qId = $_DATA['qId'];
        $currUserId = $_DATA['currentuserId'];
        $currUserEmail = $_DATA['currUserEmail'];
        $custEmail = explode(',', $_DATA['custEmail']);
        $msg = $_DATA['msg'];
        $cmt = $_DATA['cmt'];
        $chkVal = $_DATA['chkVal'];
        //$html = $_DATA['html'];
        $html = $this->getQuoteHtmlByQuoteId($qId);

        $quoteData = $this->getQuoteDataById($qId);
        
        $controlNumber = $quoteData->getControlNumber();
        $version = $quoteData->getVersion();

        $datime = new \DateTime('now');
        if (empty($qId) || empty($currUserId) || empty($currUserEmail) || empty($custEmail) || empty($msg) || empty($html)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing.';
            $statusCode = 422;
        } else {
            $pdfName = 'uploads/quote_screenshots/quotePDF-'.$qId.'-'.time().'.pdf';
            $quotePdfUrl = $this->createQuoteLineitemPDF($html, $pdfName, $request);
            $newMessage = $this->createMessageToBeSent($msg, $cmt);
            $urls = $this->getAttachmentUrls($chkVal, $request);
            $urls[] = $quotePdfUrl;
            $message = \Swift_Message::newInstance()
                ->setFrom($currUserEmail)
                ->setTo($custEmail)
                ->setSubject('Here\'s your quote from Talbert #'.$controlNumber.'-'.$version)
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

    /**
     * @Route("/api/quote/searchQuotes")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    
    public function searchQuoteAction(Request $request) {
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
                $condition="q.status != :status and qs.statusId!='' and qs.isActive=1 ";
                $concat = ' and ';
                if ($searchType == 'estNo') {
                    $estimate = explode('-',$searchVal);
                    $condition.=$concat."q.controlNumber= :searchVal ";
                    $keyword = $estimate[1];
                //                    $concat = " AND ";
                } else if ($searchType == 'company'){
                    if($type=='status'){
                        if($searchVal!='all'){
                            $keyword=$searchVal;                  
                            $condition.=$concat."qs.statusId = :searchVal ";
                //                            $concat = " AND ";
                        }                        
                    } else {
                        $keyword='%'.$searchVal.'%';                  
                        $condition.=$concat."u.company Like :searchVal ";
                //                        $concat = " AND ";
                    }                    
                }
                if(!empty($startDate) && !empty($endDate)){
                    $condition = $condition.$concat." q.estimatedate >= :from AND q.estimatedate <= :to ";
                } else if(!empty($startDate) && empty($endDate) || ($startDate == $endDate && !empty($endDate) && !empty($startDate))){
                    $condition = $condition.$concat." q.estimatedate >= :from ";
                } else if(empty($startDate) && !empty($endDate)){
                    $condition = $condition.$concat." q.estimatedate <= :to ";
                }
                $query = $this->getDoctrine()->getManager();
                $query1=$query->createQueryBuilder()
                ->select(['q.controlNumber','q.version','q.customerId','q.estimatedate','q.id',
                        'qs.statusId',
                        's.statusName as status',
                        's.abbr as stsAbbr',
                        'u.company as companyname','u.fname','u.lname'
                        ])
                    ->from('AppBundle:Quotes', 'q')
                //                    ->leftJoin('AppBundle:Quotes', 'q', 'WITH', "q.id = o.quoteId")
                    ->innerJoin('AppBundle:QuoteStatus', 'qs', 'WITH', "q.id = qs.quoteId")
                    ->leftJoin('AppBundle:Status', 's', 'WITH', "qs.statusId=s.id ")
                    ->leftJoin('AppBundle:Profile', 'u', 'WITH', "q.customerId = u.userId")
                    ->where($condition)
                    ->setParameter('status', 'Approved');
                if($searchVal!='all'){
                    $query1->setParameter('searchVal', $keyword);
                }
                if(!empty($startDate) && !empty($endDate)){
                    $query1->setParameter('from', date('Y-m-d',strtotime($startDate)).'T00:00:00')
                    ->setParameter('to', date('Y-m-d',strtotime($endDate)).'T23:59:59');
                } else if(!empty($startDate) && empty($endDate) || ($startDate == $endDate && !empty($endDate) && !empty($startDate))){
                    $query1->setParameter('from', date('Y-m-d',strtotime($startDate)).'T00:00:00');
                } else if(empty($startDate) && !empty($endDate)){
                    $query1->setParameter('to', date('Y-m-d',strtotime($endDate)).'T23:59:59');
                }
                $quotes=$query1->orderBy('q.estimatedate','DESC')->getQuery()->getResult();
                //                $quotes=$query1->getQuery()->getSQL();print_r($quotes);die;
                if (empty($quotes) ) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'There is no order.';
                    $statusCode = 422;
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully retreived the order list.';
                    $quoteList=[];
                    for($i=0;$i<count($quotes);$i++) {
                        $quoteList[$i]=[
                            'id'=>$quotes[$i]['id'],
                            'estimateNumber'=>$quotes[$i]['controlNumber'].'-'.$quotes[$i]['version'],
                            'customername'=>$quotes[$i]['fname'],
                            'companyname'=>$quotes[$i]['companyname'],
                            //                            'orderId'=>$quotes[$i]['orderId'],
                            'status'=>$quotes[$i]['status'],
                            'stsabbr' => $quotes[$i]['stsAbbr'],
                            'estDate'=>$this->getEstimateDateFormate($quotes[$i]['estimatedate']),
                        ];
                    }
                    $arrApi['data']['quotes'] = $quoteList;
                }
                return new JsonResponse($arrApi, $statusCode);                
            }
            catch(Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }
    
    public function searchQuoteActionOld(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $pageNo = $data->get('current_page');
        $limit = $data->get('limit');
        $sortBy = $data->get('sort_by');
        $order = $data->get('order');
        $searchVal = trim($data->get('searchVal'));
        $startDate = $data->get('startDate');
        $endDate = $data->get('endDate');
        $offset = ($pageNo - 1)  * $limit;
        if (false) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing.';
            $statusCode = 422;
        } else {
            try {
                $searchType = $this->checkIfSearchValIsEstOrCompany($searchVal);
                if ($searchType == 'estNo') {
                    $data = $this->getQuoteDataByEstNo($searchVal);
                } else {
                    $data = $this->getQuoteDataByCompany($searchVal, $startDate, $endDate);
                }
                if (empty($data) ) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'No quote found';
                    $arrApi['data']['quotes'] = [];
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully retreived the quote list.';
                    $quoteList=[];
                    for($i=0;$i<count($data);$i++) {
                        $quoteList[$i]['id'] = $data[$i]['id'];
                        $quoteList[$i]['estimateNumber'] = 'E-'.$data[$i]['controlNumber'].'-'.$data[$i]['version'];
                        $quoteList[$i]['customername'] = strtoupper($this->getCustomerNameById($data[$i]['customerId']));
                        $quoteList[$i]['companyname'] = strtoupper($this->getCompanyById($data[$i]['customerId']));
                        $quoteList[$i]['status'] = $data[$i]['status'];
                        $quoteList[$i]['estDate'] = $this->getEstimateDateFormate($data[$i]['estimatedate']);
                    }
                    $arrApi['data']['quotes'] = $quoteList;
                }
            }
            catch(Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    private function getQuoteDataByEstNo($searchVal) {
        $estArr = explode('-', $searchVal);
        $query = $this->getDoctrine()->getManager();
        $qb = $query->createQueryBuilder("qe");
        $qb->select(["q.id","q.controlNumber","q.version","q.customerId, q.status","q.estimatedate"])
           ->from('AppBundle:Quotes', 'q')
           ->where('q.controlNumber=:controlNumber AND (q.status=:status OR q.status=:hstatus)')
           ->setParameter('controlNumber', $estArr[1] )
           ->setParameter('status', 'Current' )
           ->setParameter('hstatus', 'Hold' );
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    private function getQuoteDataByCompany($searchVal, $startDate, $endDate) {
        $query = $this->getDoctrine()->getManager();
        $qb = $query->createQueryBuilder("qe");
        $qb->select(["q.id","q.controlNumber","q.version","q.customerId, q.status","q.estimatedate"])
            ->from('AppBundle:Quotes', 'q');
        if ( $startDate && $endDate ) {
            if ( $startDate == $endDate ) {
                $qb->where('q.estDateForSearch = :from AND p.company LIKE :company AND (q.status=:status OR q.status=:hstatus)');
            } else {
                $qb->where('q.estDateForSearch >= :from AND q.estDateForSearch <= :to AND p.company LIKE :company AND (q.status=:status OR q.status=:hstatus) order by q.estimatedate');
            }
        } else {
            $qb->where('p.company LIKE :company AND (q.status=:status OR q.status=:hstatus)');
        }
        $qb->leftJoin('AppBundle:Profile', 'p', 'WITH', "q.customerId = p.userId")
        ->setParameter('company', $searchVal."%" );
        if ( $startDate && $endDate ) {
            if ( $startDate == $endDate ) {
                $qb->setParameter('from', explode('T', $startDate)[0]);
            } else {
                $qb->setParameter('from', explode('T', $startDate)[0])
                    ->setParameter('to', explode('T', $endDate)[0]);
            }
        }
        $qb->setParameter('status', 'Current' )
        ->setParameter('hstatus', 'Hold' );
        return $qb->getQuery()->getResult();

    }

    /**
     * @Route("/api/quote/approveQuote")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     */
    public function approveQuoteAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $qId = $data->get('qId');
        $estNo = $data->get('estNo');
        $orderNum = $this->getOrderNumber($estNo);
        $approveBy = trim($data->get('approvedBy'));
        $via = trim($data->get('via'));
        $other = trim($data->get('other'));
        $custPO = trim($data->get('custPO'));
        $datime = new \DateTime('now');
        if (empty($qId) || empty($estNo) || empty($approveBy) || empty($via)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing';
            $statusCode = 422;
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Order generated successfully';
            $deliveryDate = trim($data->get('deliveryDate'));
            $orderDate = $this->getOrderDate($qId);
            $orderExists = $this->checkIfOrderAlreadyExists($qId);
            if ($orderExists) {
                $this->updateOrderData($qId, $estNo, $orderNum, $approveBy, $via, $other, $orderDate, $custPO, $deliveryDate);
            } else {
                $this->saveOrderData($qId, $estNo, $orderNum, $approveBy, $via, $other, $orderDate, $custPO, $deliveryDate);
            }
            $this->updateQuoteStatus($qId, 'Approved', $deliveryDate, $datime);
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    /**
     * @Route("/api/quote/excludeLineItemPriceFromOrder")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     */
    public function excludeLineItemPriceFromOrderAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $quoteId = $data->get('quoteId');
        $lineItemArr = explode(',', $data->get('lineItemIdArr'));
        $estimateNo = $this->getEstimatenoByQId($data->get('newQuoteId'));
        $datime = new \DateTime('now');
        for ($i=0; $i < count($lineItemArr)-1; $i++) {
            $lineItemIdArr = explode('-', $lineItemArr[$i]);
            if ($lineItemIdArr[1] == 'V') {
                $this->excludePriceOfVeneer($lineItemIdArr[0], $estimateNo, $quoteId, 'Quote', 'Veneer', $datime);
            } elseif ($lineItemIdArr[1] == 'P') {
                $this->excludePriceOfPlywood($lineItemIdArr[0], $estimateNo, $quoteId, 'Quote', 'Plywood', $datime);
            } else {
                $this->excludePriceOfDoor($lineItemIdArr[0], $estimateNo, $quoteId, 'Quote', 'Door', $datime);
            }
        }
        $arrApi['status'] = 1;
        $arrApi['message'] = 'Success';
        return new JsonResponse($arrApi, $statusCode);
    }

    //Reusable codes

    private function excludePriceOfVeneer($id, $estimateNo, $orderId, $quoteOrOrder, $lineitemType, $datime) {
        $this->changeLineitemStatusRowForVeneer($orderId, $quoteOrOrder, $id, $lineitemType, $datime);
        $em = $this->getDoctrine()->getManager();
        $veneer = $em->getRepository(Veneer::class)->findOneById($id);
        if (!empty($veneer)) {
            $veneer->setCustMarkupPer(25);
            $veneer->setVenCost(0);
            $veneer->setVenWaste(1);
            $veneer->setSubTotalVen(0);
            $veneer->setCoreCost(0);
            $veneer->setSubTotalCore(0);
            $veneer->setCoreWaste(1);
            $veneer->setSubTotalBackr(0);
            $veneer->setBackrCost(0);
            $veneer->setBackrWaste(1);
            $veneer->setRunningCost(0);
            $veneer->setRunningWaste(1);
            $veneer->getSubTotalrunning(0);
            $veneer->setTotCostPerPiece(0.00);
            $veneer->setMarkup(0.00);
            $veneer->setSellingPrice(0.00);
            $veneer->setLineitemTotal(0.00);
            $veneer->setMachineSetup(0);
            $veneer->setMachineTooling(0);
            $veneer->setPreFinishSetup(0);
            $veneer->setColorMatch(0);
            $veneer->setTotalCost(0.00);
            //$veneer->setLinitemStatusId($lineitemStatusId);
            $veneer->setBackOrderEstNo($estimateNo);
            $em->persist($veneer);
            $em->flush();
        }
    }

    private function changeLineitemStatusRowForVeneer($orderId, $quoteOrOrder, $lineitemId, $lineitemType, $datime) {
        $em = $this->getDoctrine()->getManager();
        $lineItemStatus = $em->getRepository(LineItemStatus::class)->findOneBy(array('quoteOrOrderId'=> $orderId, 'type'=> $quoteOrOrder, 'lineItemId'=> $lineitemId, 'lineItemType'=> $lineitemType, 'isActive'=> 1));
        if (!empty($lineItemStatus)) {
            $lineItemStatus->setStatusId(13);
            $lineItemStatus->setType('Order');
            $lineItemStatus->setUpdatedAt($datime);
            $em->persist($lineItemStatus);
            $em->flush();
        }
    }

    private function excludePriceOfPlywood($id, $estimateNo, $orderId, $quoteOrOrder, $lineitemType, $datime) {
        $this->changeLineitemStatusRowForPlywood($orderId, $quoteOrOrder, $id, $lineitemType, $datime);
        $em = $this->getDoctrine()->getManager();
        $plywood = $em->getRepository(Plywood::class)->findOneById($id);
        if (!empty($plywood)) {
            $plywood->setCustMarkupPer(25);
            $plywood->setCalcTW(0);
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
//            $plywood->setLinitemStatusId($lineitemStatusId);
            $plywood->setBackOrderEstNo($estimateNo);
            $em->persist($plywood);
            $em->flush();
        }
    }

    private function changeLineitemStatusRowForPlywood($orderId, $quoteOrOrder, $lineitemId, $lineitemType, $datime) {
        $em = $this->getDoctrine()->getManager();
        $lineItemStatus = $em->getRepository(LineItemStatus::class)->findOneBy(array('quoteOrOrderId'=> $orderId, 'type'=> $quoteOrOrder, 'lineItemId'=> $lineitemId, 'lineItemType'=> $lineitemType, 'isActive'=> 1));
        if (!empty($lineItemStatus)) {
            $lineItemStatus->setStatusId(13);
            $lineItemStatus->setType('Order');
            $lineItemStatus->setUpdatedAt($datime);
            $em->persist($lineItemStatus);
            $em->flush();
        }
    }

    private function excludePriceOfDoor($id, $estimateNo, $orderId, $quoteOrOrder, $lineitemType, $datime) {
        $this->changeLineitemStatusRowForDoor($orderId, $quoteOrOrder, $id, $lineitemType, $datime);
        $em = $this->getDoctrine()->getManager();
        $door = $em->getRepository(DoorCalculator::class)->findOneBy(array('doorId'=> $id));
        if (!empty($door)) {
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
            $door->setMillingCost(0.00);
            $door->setMillingWaste(1);
            $door->setSubTotalMilling(0.00);
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
            $door->setCalcTW(0);
            $em->persist($door);
            $em->flush();
        }
        $doors = $em->getRepository(Doors::class)->findOneById($id);
        if (!empty($doors)) {
//            $doors->setLinitemStatusId($lineitemStatusId);
            $doors->setBackOrderEstNo($estimateNo);
            $em->persist($doors);
            $em->flush();
        }
    }

    private function changeLineitemStatusRowForDoor($orderId, $quoteOrOrder, $lineitemId, $lineitemType, $datime) {
        $em = $this->getDoctrine()->getManager();
        $lineItemStatus = $em->getRepository(LineItemStatus::class)->findOneBy(array('quoteOrOrderId'=> $orderId, 'type'=> $quoteOrOrder, 'lineItemId'=> $lineitemId, 'lineItemType'=> $lineitemType, 'isActive'=> 1));
        if (!empty($lineItemStatus)) {
            $lineItemStatus->setStatusId(13);
            $lineItemStatus->setType('Order');
            $lineItemStatus->setUpdatedAt($datime);
            $em->persist($lineItemStatus);
            $em->flush();
        }
    }

    private function getOrderNumber($estNo) {
        return str_replace('E', 'O' , $estNo);
    }

    private function updateOrderData($qId, $estNo, $orderNum, $approveBy, $via, $other, $datime, $custPO, $deliveryDate) {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository(Orders::class)->findOneBy(array('quoteId'=> $qId));
        $em->getConnection()->beginTransaction();
        if (!empty($order)) {
            try {
                $order->setEstNumber($estNo);
                $order->setOrderNumber($orderNum);
                $order->setApprovedBy($approveBy);
                $order->setVia($via);
                $order->setOther($other);
                $order->setOrderDate($datime);
                $order->setPoNumber($custPO);
                $order->setShipDate($deliveryDate);
                $em->persist($order);
                $em->flush();
                $currentdatime = new \DateTime('now');
                $orderStatus = $em->getRepository('AppBundle:OrderStatus')->findOneBy(['orderId'=>$order->getId(),'isActive'=>1]);
                if(!empty($orderStatus)){
                    $orderStatus->setUpdatedAt($currentdatime);
                    $orderStatus->setIsActive(0);
                    $em->persist($orderStatus);
                    $em->flush();
                }                
                
                $newOrderStatus = new OrderStatus();
                $newOrderStatus->setOrderId($order->getId());
                $newOrderStatus->setStatusId(1);
                $newOrderStatus->setCreatedAt($currentdatime);
                $newOrderStatus->setUpdatedAt($currentdatime);
                $newOrderStatus->setIsActive(1);
                $em->persist($newOrderStatus);
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
            }            
        }
    }

    private function getDeliveryDate($qId) {
        $em = $this->getDoctrine()->getManager();
        $quote = $em->getRepository(Quotes::class)->findOneById($qId);
        if (!empty($quote)) {
            return $quote->getDeliveryDate();
        }
    }

    private function getOrderDate($qId) {
        $em = $this->getDoctrine()->getManager();
        $quote = $em->getRepository(Quotes::class)->findOneById($qId);
        if (!empty($quote)) {
            return $quote->getEstimatedate();
        }
    }

    private function checkIfOrderAlreadyExists($qId) {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository(Orders::class)->findOneBy(array('quoteId'=> $qId));
        if (!empty($order)) {
            return true;
        } else {
            return false;
        }
    }

    private function saveOrderData($qId, $estNo, $orderNum, $approveBy, $via, $other, $datime, $custPO, $deliveryDate) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $currentdatime = new \DateTime('now');
            $orders = new Orders();
            $orders->setQuoteId($qId);
            $orders->setEstNumber($estNo);
            $orders->setOrderNumber($orderNum);
            $orders->setApprovedBy($approveBy);
            $orders->setVia($via);
            $orders->setOther($other);
            $orders->setOrderDate($datime);
            $orders->setPoNumber($custPO);
            $orders->setShipDate($deliveryDate);
            $orders->setIsActive(1);
            $em->persist($orders);
            $em->flush();
            $newOrderStatus = new OrderStatus();
            $newOrderStatus->setOrderId($orders->getId());
            $newOrderStatus->setStatusId(5);
            $newOrderStatus->setCreatedAt($currentdatime);
            $newOrderStatus->setUpdatedAt($currentdatime);
            $newOrderStatus->setIsActive(1);
            $em->persist($newOrderStatus);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        
    }

    private function updateQuoteStatus($qId, $status, $deliveryDate, $datime) {
        $em = $this->getDoctrine()->getManager();
        $quote = $em->getRepository(Quotes::class)->findOneById($qId);        
        $em->getConnection()->beginTransaction();
        if (!empty($quote)) {
            try {
                $quote->setStatus($status);
                $quote->setUpdatedAt($datime);
                $quote->setDeliveryDate($deliveryDate);
                $em->persist($quote);
                $em->flush();
                $currentdatime = new \DateTime('now');
                $quoteStatus = $em->getRepository('AppBundle:QuoteStatus')->findOneBy(['quoteId'=>$qId,'isActive'=>1]);
                // $quoteStatus->setStatusId($this->getQuoteStatusId($status));
                if(!empty($quoteStatus)){
                    $quoteStatus->setUpdatedAt($currentdatime);
                    $quoteStatus->setIsActive(0);
                    $em->persist($quoteStatus);
                    $em->flush();
                }              
                
                $newQuoteStatus = new QuoteStatus();
                $newQuoteStatus->setQuoteId($quote->getId());
                $newQuoteStatus->setStatusId($this->getQuoteStatusId($status));
                $newQuoteStatus->setCreatedAt($currentdatime);
                $newQuoteStatus->setUpdatedAt($currentdatime);
                $newQuoteStatus->setIsActive(1);
                $em->persist($newQuoteStatus);
                $em->flush();
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
            }
            
        }
    }

    private function createQuoteLineitemPDF($html,$pdfName, $request) {
        $fs = new Filesystem();
        $snappy = new Pdf(  '../vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=$pdfName');
        $snappy->generateFromHtml($html, $pdfName, array('orientation'=>'Landscape', 'default-header'=>false, 'page-size' => 'Letter'));
        $fs->chmod($pdfName, 0777);
        return 'http://'.$request->getHost().'/'.$request->getBasePath().'/'.$pdfName;
    }

    private function createMessageToBeSent($msg, $cmt) {
        $nStr = "Please call me with any questions.\r\n\r\n".$cmt."\r\n\r\n";
        return str_replace("Please call me with any questions.",$nStr, $msg);
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

    private function strignfyCHKArr($chkVal) {
        return implode(',', $chkVal);
    }

    private function getCustomerIdByQuoteId($qId) {
        $quoteRecord = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findById($qId);
        if (!empty($quoteRecord)) {
            return $quoteRecord[0]->getCustomerId();
        }
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

    private function getVeneerIds($ven) {
        $ids = array();
        for ($i=0;$i<count($ven);$i++) {
            $ids[] = $ven[$i]->getId();
        }
        return $ids;
    }

    private function getPlywoodIds($ply) {
        $ids = array();
        for ($i=0;$i<count($ply);$i++) {
            $ids[] = $ply[$i]->getId();
        }
        return $ids;
    }

    private function getMessageTemplateforEmailQuote() {
        $em = $this->getDoctrine()->getManager();
        $messTemplate = $em->getRepository(MessageTemplates::class)->findOneById(1);
        if (!empty($messTemplate)) {
            return $messTemplate->getMessage();
        }
    }

    private function updateData($qId, $qDate, $qDateForSearch, $quoteAddedby, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status,  $comment, $deliveryDate, $expFee, $discount, $shipCost, $datime) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $quote = $em->getRepository(Quotes::class)->findOneById($qId);
        if (!empty($quote)) {
            try {
                if($status!=$quote->getStatus()){
                    $updateStatusFlag = true;
                } else {
                    $updateStatusFlag = false;
                }
                $quote->setEstimatedate($qDate);
                $quote->setEstDateForSearch($qDateForSearch);
                $quote->setEstimatorId($quoteAddedby);
                $quote->setCustomerId($custId);
                $quote->setRefNum($refNo);
                $quote->setSalesmanId($salsManId);
                $quote->setJobName($job);
                $quote->setTermId($termId);
                $quote->setShipMethdId($shipMethod);
                $quote->setShipAddId($shipAddId);
                $quote->setLeadTime($leadTime);
                $quote->setStatus($status);
                $quote->setComment($comment);
                $quote->setDeliveryDate($deliveryDate);
                $quote->setExpFee($expFee);
                $quote->setDiscount($discount);
                $quote->setShipCharge($shipCost);
                $quote->setUpdatedAt($datime);
                $em->persist($quote);
                $em->flush();
                $this->updateQuoteData($qId);
                if($updateStatusFlag==true){
                    $quoteStatus = $em->getRepository('AppBundle:QuoteStatus')->findOneBy(['quoteId'=>$qId,'isActive'=>1]);
    //                $quoteStatus->setStatusId($this->getQuoteStatusId($status));
                    if(!empty($quoteStatus)){
                        $quoteStatus->setUpdatedAt($datime);
                        $quoteStatus->setIsActive(0);
                        $em->persist($quoteStatus);
                        $em->flush();
                    }                

                    $newQuoteStatus = new QuoteStatus();
                    $newQuoteStatus->setQuoteId($quote->getId());
                    $newQuoteStatus->setStatusId($this->getQuoteStatusId($status));
                    $newQuoteStatus->setCreatedAt($datime);
                    $newQuoteStatus->setUpdatedAt($datime);
                    $newQuoteStatus->setIsActive(1);
                    $em->persist($newQuoteStatus);
                    $em->flush();
                }
                
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
            }            
            return 1;
        }
    }

    private function cloneQuoteData($qData, $datime,$quoteId,$editFlag='cloneQuote') {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $quote = new Quotes();
            if($editFlag=='editOrder'){
                $quote->setVersion($qData->getVersion()+1);
                $quote->setControlNumber($qData->getControlNumber());
                $quote->setRefid($qData->getControlNumber());
            } else {
                $quote->setVersion(1);
                // $quote->setControlNumber($this->getLastControlNumber()+1);
                $sstNo = $this->getLastControlNumberById()+1;
                $quote->setControlNumber($sstNo);
                $quote->setRefid($qData->getId());
            }
            $quote->setEstimatedate($qData->getEstimatedate());
            $quote->setEstimatorId($qData->getEstimatorId());
            // $quote->setControlNumber($this->getLastControlNumber()+1);
            $quote->setCustomerId($qData->getCustomerId());
            $quote->setRefNum($qData->getRefNum());
            $quote->setSalesmanId($qData->getSalesmanId());
            $quote->setJobName($qData->getJobName());
            $quote->setTermId($qData->getTermId());
            $quote->setShipMethdId($qData->getShipMethdId());
            $quote->setShipAddId($qData->getShipAddId());
            $quote->setLeadTime($qData->getLeadTime());
            $quote->setStatus('Current');
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
            if($editFlag=='editOrder'){
                $order = $em->getRepository(Orders::class)->findOneBy(['quoteId'=>$quoteId]);
                $order->setIsActive(0);
                $em->persist($order);
                $em->flush();
            } elseif ($editFlag == 'backOrder') {
                $this->approveQuoteForBackOrder($quote->getId(), 'E-'.$sstNo.'-1', '', '', '', '');
            }
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        return $quote->getId();
    }

    private function clonePlywoodData($quoteId, $clonedQuoteId, $datime,$lineItemArr=[],$editFlag="cloneQuote") {
        $em = $this->getDoctrine()->getEntityManager('default');
        $condition=($editFlag=="editOrder" || $editFlag=='backOrder')?['quoteId'=>$quoteId,'id'=>$lineItemArr]:['quoteId'=>$quoteId];
        $ply = $em->getRepository('AppBundle:Plywood')->findBy($condition);
        $lineItemCost=0;
        
        if (!empty($ply)) {
            $em->getConnection()->beginTransaction();
            try {
                $datime = new \DateTime('now');
                foreach ($ply as $entity) {
                    $lineItemCost=$lineItemCost+$entity->getTotalCost();
                    $newEntity = clone $entity;
                    $newEntity
                            ->setId(null)
                            ->setQuoteId($clonedQuoteId)
                            ->setCreatedAt($datime)
                            ->setUpdatedAt($datime)
                    ;
                    $em->persist($newEntity);
                    $em->flush();
                    if($editFlag=="editOrder"  || $editFlag=='backOrder'){
                        if($editFlag=="editOrder"){
                            $type = 'Order';
                        } else if($editFlag=='backOrder'){
                            $type = 'Quote';                            
                        }
                        
                    }
                    else {
                        $type = 'Quote';
                    }
                    $lineItemStatus=new LineItemStatus();
                    $lineItemStatus->setQuoteOrOrderId($clonedQuoteId);
                    $lineItemStatus->setType($type);
                    $lineItemStatus->setLineItemId($newEntity->getId());
                    $lineItemStatus->setStatusId(1);
                    $lineItemStatus->setLineItemType('Plywood');
                    $lineItemStatus->setIsActive(1);
                    $lineItemStatus->setCreatedAt($datime);
                    $lineItemStatus->setUpdatedAt($datime);
                    $em->persist($lineItemStatus);
                    $em->flush();
                }
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
            }            
            $newPly = $em->getRepository('AppBundle:Plywood')->findOneBy(['quoteId'=>$clonedQuoteId],['id'=>'ASC']);
            return [
                'id'=>!empty($newPly->getId())?$newPly->getId():'',
                'plyCost'=>$lineItemCost
            ];
//            for ($i=0; $i< 1; $i++) {
//                $ply[$i]->setQuoteId($clonedQuoteId);
                /*$plywd = new Plywood();
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
                $plywd->setTotalCost($ply[$i]->getTotalCost());*/
                /*$em->persist($ply[$i]);
                $em->flush();
                echo $ply[$i]->getId();die;*/
//                $this->cloneAttachments($ply[$i]->getId(), $plywd->getId(), 'Plywood', $datime);
//            }
            
        } else {
            return [
                'id'=>'',
                'plyCost'=>$lineItemCost
            ];
        }
    }

    private function cloneVeneerData($quoteId, $clonedQuoteId, $datime,$lineItemArr=[],$editFlag=false) {
        $em = $this->getDoctrine()->getEntityManager('default');
        $condition=($editFlag=="editOrder" || $editFlag=='backOrder')?['quoteId'=>$quoteId,'id'=>$lineItemArr]:['quoteId'=>$quoteId];
        $veneeerData = $em->getRepository('AppBundle:Veneer')->findBy($condition);
        //print_r($veneeerData);die;
        $veneerCost=0;
        
        if (!empty($veneeerData)) {
            $em->getConnection()->beginTransaction();
            try {
                $datime = new \DateTime('now');
                foreach ($veneeerData as $entity) {
                    $veneerCost=$veneerCost+$entity->getTotalCost();
                    $newEntity = clone $entity;
                    $newEntity
                            ->setId(null)
                            ->setQuoteId($clonedQuoteId)
                            ->setCreatedAt($datime)
                            ->setUpdatedAt($datime)
                    ;
                    $em->persist($newEntity);
                    $em->flush();
                    if($editFlag=="editOrder" || $editFlag=='backOrder'){                        
                        if($editFlag=="editOrder"){
                            $type = 'Order';
                        } else if($editFlag=='backOrder'){
                            $type = 'Quote';                            
                        }
                        
                    }
                    else {
                        $type = 'Quote';
                    }
                    $lineItemStatus=new LineItemStatus();
                    $lineItemStatus->setQuoteOrOrderId($clonedQuoteId);
                    $lineItemStatus->setType($type);
                    $lineItemStatus->setLineItemId($newEntity->getId());
                    $lineItemStatus->setStatusId(1);
                    $lineItemStatus->setLineItemType('Veneer');
                    $lineItemStatus->setIsActive(1);
                    $lineItemStatus->setCreatedAt($datime);
                    $lineItemStatus->setUpdatedAt($datime);
                    $em->persist($lineItemStatus);
                    $em->flush();


                }
                $em->getConnection()->commit();
            } catch (Exception $ex) {
                $em->getConnection()->rollback();
            } 
            /*foreach ($veneeerData as $entity) {
                $veneerCost=$veneerCost+$entity->getTotalCost();
                $newEntity = clone $entity;
                $newEntity
                        ->setId(null)
                        ->setQuoteId($clonedQuoteId)
                ;
                $em->persist($newEntity);
            }
            $em->flush();*/
            $newV= $em->getRepository('AppBundle:Veneer')->findOneBy(['quoteId'=>$clonedQuoteId],['id'=>'ASC']);
            return [
                'id'=>!empty($newV->getId())?$newV->getId():'',
                'veneerCost'=>$veneerCost
            ];
            /*$em = $this->getDoctrine()->getManager();
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
            }*/
        } else {
            return [
                'id'=>'',
                'veneerCost'=>$veneerCost
            ];
        }
    }
    
    private function cloneDoorData($quoteId, $clonedQuoteId, $datime,$lineItemArr=[],$editFlag=false) {
        
        $em = $this->getDoctrine()->getEntityManager('default');
        
        $doorCost=0;
        try {
            $em->getConnection()->beginTransaction();
            $condition=($editFlag=="editOrder" || $editFlag=='backOrder')?['quoteId'=>$quoteId,'id'=>$lineItemArr]:['quoteId'=>$quoteId];
            $doorData = $em->getRepository('AppBundle:Doors')->findBy($condition);
            if (!empty($doorData)) {
                foreach ($doorData as $entity) {
                    $newEntity = clone $entity;
                    $newEntity->setId(null)->setQuoteId($clonedQuoteId);
                    $em->persist($newEntity);
                    $em->flush();
                    $doorCalData = $em->getRepository('AppBundle:DoorCalculator')->findBy(['doorId' => $entity->getId()]);
                    if(!empty($doorCalData)){
                        foreach ($doorCalData as $value) {
                            $doorCost=$doorCost+$value->getTotalCost();
                            $newDoorCalEntity = clone $value;
                            $newDoorCalEntity->setId(NULL)->setDoorId($newEntity->getId());
                            $em->persist($newDoorCalEntity);
                            $em->flush();
                        }
                    }                    
                    $doorSkinData = $em->getRepository('AppBundle:Skins')->findBy(['doorId' => $entity->getId(),
                        'quoteId'=>$quoteId]);
                    if(!empty($doorSkinData)){
                        foreach ($doorSkinData as $v) {
                            $newDoorSkinEntity = clone $v;
                            $newDoorSkinEntity->setId(NULL)->setQuoteId($clonedQuoteId)->setDoorId($newEntity->getId());
                            $em->persist($newDoorSkinEntity);
                            $em->flush();
                        }
                    }
                    if($editFlag=="editOrder" || $editFlag=='backOrder'){                                               
                        if($editFlag=="editOrder"){
                            $type = 'Order';
                        } else if($editFlag=='backOrder'){
                            $type = 'Quote';                            
                        }
                    } else {
                        $type = 'Quote';
                    }
                    $lineItemStatus=new LineItemStatus();
                    $lineItemStatus->setQuoteOrOrderId($clonedQuoteId);
                    $lineItemStatus->setType($type);
                    $lineItemStatus->setLineItemId($newEntity->getId());
                    $lineItemStatus->setStatusId(1);
                    $lineItemStatus->setLineItemType('Door');
                    $lineItemStatus->setIsActive(1);
                    $lineItemStatus->setCreatedAt($datime);
                    $lineItemStatus->setUpdatedAt($datime);
                    $em->persist($lineItemStatus);
                    $em->flush();
                }
            }
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        if (!empty($doorData)) {
            $newD= $em->getRepository('AppBundle:Doors')->findOneBy(['quoteId'=>$clonedQuoteId],['id'=>'ASC']);
            return [
                'id'=>!empty($newD->getId())?$newD->getId():'',
                'doorCost'=>$doorCost
            ];
        } else {
            return [
                'id'=>'',
                'doorCost'=>$doorCost
            ];
        }        
    }

    private function cloneDoorDataOld($quoteId, $clonedQuoteId, $datime) {
        $em = $this->getDoctrine()->getEntityManager('default');
        $doorData = $em->getRepository('AppBundle:Doors')->findBy(array('quoteId' => $quoteId));
        
        if (!empty($doorData)) {
            foreach ($doorData as $entity) {
                $doorCalData = $em->getRepository('AppBundle:DoorCalculator')->findBy(['doorId' => $entity->getId()]);
                $newEntity = clone $entity;
                $newEntity
                        ->setId(null)
                        ->setQuoteId($clonedQuoteId);
                $em->persist($newEntity);
                $em->flush();
                foreach ($doorCalData as $value) {
                    $newDoorCalEntity = clone $value;
                    $newDoorCalEntity
                        ->setId(NULL)
                        ->setDoorId($newEntity->getId());
                    $em->persist($newDoorCalEntity);
                    $em->flush();
                }
//                print_r($newDoorCalEntity);die;
            }
            
            
            /*$em = $this->getDoctrine()->getManager();
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
            } */
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

    private function getLastControlNumber() {
        $lastQuote = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneBy(array(),array('id'=>'desc'));
        if (!empty($lastQuote)) {
            return $lastQuote->getControlNumber();
        }
    }

    private  function checkIfRefNoExists($refNo) {
        return $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneBy(array('refNum' => $refNo));
    }

    private function saveQuoteData($qDate, $qDateForSearch, $quoteAddedby, $ctrlNo, $ver, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $datime) {
        $em = $this->getDoctrine()->getManager();
        $id='';
        $em->getConnection()->beginTransaction();
        try {
            $quote = new Quotes();
            $quote->setEstimatedate($qDate);
            $quote->setEstDateForSearch($qDateForSearch);
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
            $quote->setShipCharge($this->getShipChargeByAddId($shipAddId));
            $quote->setLeadTime($leadTime);
            $quote->setExpFee(0.00);
            $quote->setDiscount(0.00);
            $quote->setStatus($status);
            $quote->setCreatedAt($datime);
            $quote->setUpdatedAt($datime);
            $em->persist($quote);
            $em->flush();
            $quote->setControlNumber($quote->getId());
            $em->persist($quote);
            $em->flush();
            $quoteStatus = new QuoteStatus();
            $quoteStatus->setQuoteId($quote->getId());
            $quoteStatus->setStatusId($this->getQuoteStatusId($status));
            $quoteStatus->setCreatedAt($datime);
            $quoteStatus->setUpdatedAt($datime);
            $quoteStatus->setIsActive(1);
            $em->persist($quoteStatus);
            $em->flush();
            $em->getConnection()->commit();
            $id=$quote->getId();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        return $id;
    }

    private function getShipChargeByAddId($shipAddId) {
        $shipCharge = 0;
        $addData = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findOneById($shipAddId);
        if (!empty($addData)) {
            $shipCharge = $addData->getDeliveryCharge();
        }
        return $shipCharge;
    }

    private function getCustomerNameById($customer_id) {
        if (!empty($customer_id)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $customer_id));
            if(!empty($profileObj)){
                if(!empty($profileObj->getFname())){
                    $customerName =  $profileObj->getFname();
                    $custArr = explode(' ', $customerName);
                    if (!empty($custArr)) {
                        return $custArr[0];
                    }
                } else {
                    return '';
                }
            } else {
                return '';
            }            
        } else {
            return '';
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

    private function getEstimateDateFormate($date) {
        $dateArr =  explode('-', explode('T',$date)[0]);
        return $d = $dateArr[1].'/'.$dateArr[2].'/'.$dateArr[0];
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

    private function getFirstLabel($labels) {
        return explode(',', $labels)[0];
    }

    private function getUVCuredNameById($id) {
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
        //return ($id == 1) ? 'Prime Only' : ($id == 2) ? 'White / Light Color' : 'Dark Color';
        return ($id == 1) ? 'PO' : ($id == 2) ? 'WLC' : 'DCL';
    }

    private function getVeneerslistbyQuoteId($qId) {
        $lineItem = array();
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $qId,'isActive'=>1));
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $qId,'isActive'=>1));
        $doorRecords = $this->getDoctrine()->getRepository('AppBundle:Doors')->findBy(array('quoteId' => $qId, 'status'=> 1));
        $i=0;
//        print_r($doorRecords);die;
        if (!empty($plywoodRecords) || !empty($doorRecords) || !empty($veneerRecords)) {
            if (!empty($plywoodRecords)) {
                $html='';
                foreach ($plywoodRecords as $p) {
                    if($p->getFinishThickType() == 'inch'){
                        if($p->getFinishThickId()>0){
                            $thickness=$p->getFinishThickId().($p->getFinThickFraction()!=0?' '.$this->float2rat($p->getFinThickFraction()):'').'"';
                        } else {
                            $thickness=$this->float2rat($p->getFinThickFraction()).'"';
                        }
                    } else {
//                        $thickness=$this->float2rat($this->convertMmToInches($p->getFinishThickId()));
                        $thickness=$p->getFinishThickId().' '.$p->getFinishThickType();
                    }
                    $lineItem[$i]['id'] = $p->getId();
                    $lineItem[$i]['type'] = 'plywood';
                    $lineItem[$i]['url'] = 'line-item/edit-plywood';
                    $lineItem[$i]['quantity'] = $p->getQuantity();
                    $lineItem[$i]['species'] = $this->getSpeciesNameById($p->getSpeciesId());
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($p->getPatternMatch());
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($p->getGradeId()))[0];
                    $lineItem[$i]['back'] = $this->getBackNameById($p->getBackerId(),'plywood');
                    $lineItem[$i]['thickness'] = $thickness;
                    $lineItem[$i]['width'] = $p->getPlywoodWidth();
                    $lineItem[$i]['length'] = $p->getPlywoodLength();
                    $lineItem[$i]['core'] = $this->getCoreNameById($p->getCoreType());
                    $lineItem[$i]['edge'] = 'NA';//$this->getEdgeNameById($p->getEdgeDetail());
                    $lineItem[$i]['unitPrice'] = $p->getSellingPrice();
                    $lineItem[$i]['totalPrice'] = $p->getTotalCost();
                    $lineItem[$i]['widthFraction'] = $this->float2rat($p->getWidthFraction());
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($p->getLengthFraction());
                    $lineItem[$i]['grain'] = $this->getGrainPattern( $p->getPatternId(),'plywood' );
                    $lineItem[$i]['edgeDetail'] = ($p->getEdgeDetail()) ? 1 : 0;
                    $lineItem[$i]['topEdge'] = $p->getTopEdge();
                    $lineItem[$i]['bottomEdge'] = $p->getBottomEdge();
                    $lineItem[$i]['rightEdge'] = $p->getRightEdge();
                    $lineItem[$i]['leftEdge'] = $p->getLeftEdge();
                    $lineItem[$i]['milling'] = ($p->getMilling()) ? '1' : 0 ;
                    $lineItem[$i]['unitMesureCostId'] = $p->getUnitMesureCostId();
                    $lineItem[$i]['finish'] = $p->getFinish();
                    $lineItem[$i]['uvCuredId'] = $this->getUVCuredNameById($p->getUvCuredId());
                    $lineItem[$i]['sheenId'] = $this->getSheenById($p->getSheenId());
                    $lineItem[$i]['shameOnId'] = ($p->getShameOnId()) ? 'BS' : '';
                    $lineItem[$i]['coreSameOnbe'] = ($p->getCoreSameOnbe()) ? ',BE' : '';
                    $lineItem[$i]['coreSameOnte'] = ($p->getCoreSameOnte()) ? ',TE' : '';
                    $lineItem[$i]['coreSameOnre'] = ($p->getCoreSameOnre()) ? ',RE' : '';
                    $lineItem[$i]['coreSameOnle'] = ($p->getCoreSameOnle()) ? ',LE' : '';
                    $lineItem[$i]['facPaint'] = $this->getFacPaintById($p->getFacPaint());
                    $lineItem[$i]['isLabels'] = $p->getIsLabels();
                    $lineItem[$i]['autoNumber'] = $this->getFirstLabel($p->getAutoNumber());
                    $lineItem[$i]['comment'] = $p->getComments();
                    $lineItem[$i]['topEdgeName'] = $this->getEdgeNameById($p->getTopEdge());
                    $lineItem[$i]['bottomEdgeName'] = $this->getEdgeNameById($p->getBottomEdge());
                    $lineItem[$i]['rightEdgeName'] = $this->getEdgeNameById($p->getRightEdge());
                    $lineItem[$i]['leftEdgeName'] = $this->getEdgeNameById($p->getLeftEdge());
                    $lineItem[$i]['millingName'] = $this->getUnitNameById($p->getMilling());
                    $lineItem[$i]['millingDescription'] = $p->getMillingDescription();
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
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($v->getGradeId()))[0];
                    $lineItem[$i]['back'] = $this->getBackNameById($v->getBacker(),'veneer');
                    $lineItem[$i]['thickness'] = $this->getThicknessNameById($v->getThicknessId());
                    $lineItem[$i]['width'] = $v->getWidth();
                    $lineItem[$i]['length'] = $v->getLength();
                    $lineItem[$i]['core'] = $this->getCoreNameById($v->getCoreTypeId());
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $v->getSellingPrice();
                    $lineItem[$i]['totalPrice'] = $v->getTotalCost();
                    $lineItem[$i]['widthFraction'] = $this->float2rat($v->getWidthFraction());
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($v->getLengthFraction());
                    $lineItem[$i]['grain'] = $this->getGrainPattern( $v->getPatternId(),'plywood' );
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
                    $lineItem[$i]['comment'] = $v->getComments();
                    $lineItem[$i]['topEdgeName'] = '';
                    $lineItem[$i]['bottomEdgeName'] = '';
                    $lineItem[$i]['rightEdgeName'] = '';
                    $lineItem[$i]['leftEdgeName'] = '';
                    $lineItem[$i]['millingName'] = '';
                    $lineItem[$i]['millingDescription'] = '';
                    $i++;
                }
            }
            if (!empty($doorRecords)) {
                foreach ($doorRecords as $d) {
                    $doorCosts = $this->getDoctrine()->getRepository('AppBundle:DoorCalculator')->
                            findOneBy(['doorId' => $d->getId()]);
                    if(!empty($doorCosts)){
                        $totalcostPerPiece = !empty($doorCosts->getSellingPrice())?$doorCosts->getSellingPrice():0;
                        $totalCost = !empty($doorCosts->getTotalCost())?$doorCosts->getTotalCost():0;
                    } else {
                        $totalcostPerPiece=0;
                        $totalCost=0;
                    }
                    if($d->getFinishThickType() == 'inch'){
                        if($d->getFinishThickId()>0){
                            $thickness=$d->getFinishThickId().($d->getFinThickFraction()!=0?' '.$this->float2rat($d->getFinThickFraction()):'').'"';
                        } else {
                            $thickness=$this->float2rat($d->getFinThickFraction()).'"';
                        }
                    } else {
//                        $thickness=$this->float2rat($this->convertMmToInches($p->getFinishThickId()));
                        $thickness=$d->getFinishThickId().' '.$d->getFinishThickType();
                    }
                    $lineItem[$i]['id'] = $d->getId();
                    $lineItem[$i]['type'] = 'door';
                    $lineItem[$i]['url'] = 'door/edit-door';
                    $lineItem[$i]['quantity'] = $d->getQty();
                    if ($this->getSpeciesNameById($this->getSpeciesIdByDoorId($d->getId())) == null) {
                        $lineItem[$i]['species'] = 'Other';
                    } else {
                        $lineItem[$i]['species'] = $this->getSpeciesNameById($this->getSpeciesIdByDoorId($d->getId()));
                    }
                    $lineItem[$i]['pattern'] = $this->getPatternNameById($this->getPatternIdByDoorId($d->getId()));
                    $lineItem[$i]['grade'] = explode('-', $this->getGradeNameById($this->getGradeIdByDoorId($d->getId())))[0];
                    $lineItem[$i]['back'] = 'NA';//$this->getBackNameById($this->getBackerIdByDoorId($d->getId()));
//                    $lineItem[$i]['thickness'] = ($d->getFinishThickType() == 'inch') ? $this->float2rat($d->getFinishThickId()) : $this->float2rat($this->convertMmToInches($d->getFinishThickId()));
                    $lineItem[$i]['thickness']=$thickness;
                    $lineItem[$i]['width'] = $d->getWidth();
                    $lineItem[$i]['length'] = $d->getLength();
                    $lineItem[$i]['core'] = 'NA';//$this->getCoreNameById($d->getCoreTypeId());
                    $lineItem[$i]['edge'] = 'NA';
                    $lineItem[$i]['unitPrice'] = $totalcostPerPiece;
                    $lineItem[$i]['totalPrice'] = $totalCost;
                    $lineItem[$i]['widthFraction'] = $this->float2rat($d->getWidthFraction());
                    $lineItem[$i]['lengthFraction'] = $this->float2rat($d->getLengthFraction());
                    $lineItem[$i]['grain'] = $this->getGrainPattern($d->getId(),'door');
                    //$lineItem[$i]['grain'] = $this->getGrainPattern($d->getId(),'door');
                    $lineItem[$i]['edgeDetail'] = ($d->getEdgeFinish()) ? '1' : 0;
                    $lineItem[$i]['topEdge'] = $d->getTopEdge();
                    $lineItem[$i]['bottomEdge'] = $d->getBottomEdge();
                    $lineItem[$i]['rightEdge'] = $d->getRightEdge();
                    $lineItem[$i]['leftEdge'] = $d->getLeftEdge();
                    $lineItem[$i]['milling'] = ($d->isMilling()) ? '1' : 0 ;
                    $lineItem[$i]['unitMesureCostId'] = $d->getUnitMesureCostId();
                    $lineItem[$i]['finish'] = $d->getFinish();
                    $lineItem[$i]['uvCuredId'] = $this->getUVCuredNameById($d->getUvCured());
                    $lineItem[$i]['sheenId'] = $this->getSheenById($d->getSheen());
                    $lineItem[$i]['shameOnId'] = ($d->getSameOnBack()) ? 'BS' : '';
                    $lineItem[$i]['coreSameOnbe'] = ($d->getSameOnBottom()) ? ',BE' : '';
                    $lineItem[$i]['coreSameOnte'] = ($d->getSameOnTop()) ? ',TE' : '';
                    $lineItem[$i]['coreSameOnre'] = ($d->getSameOnRight()) ? ',RE' : '';
                    $lineItem[$i]['coreSameOnle'] = ($d->getSameOnLeft()) ? ',LE' : '';
                    $lineItem[$i]['facPaint'] = $this->getFacPaintById($d->getFacPaint());
                    $lineItem[$i]['isLabels'] = $d->getIsLabel();
                    $lineItem[$i]['autoNumber'] = $this->getFirstLabel($d->getAutoNumber());
                    $lineItem[$i]['comment'] = $d->getComment();
                    $lineItem[$i]['topEdgeName'] = $this->getEdgeNameById($d->getTopEdge());
                    $lineItem[$i]['bottomEdgeName'] = $this->getEdgeNameById($d->getBottomEdge());
                    $lineItem[$i]['rightEdgeName'] = $this->getEdgeNameById($d->getRightEdge());
                    $lineItem[$i]['leftEdgeName'] = $this->getEdgeNameById($d->getLeftEdge());
                    $lineItem[$i]['millingName'] = $this->getUnitNameById($d->isMilling());
                    $lineItem[$i]['millingDescription'] = $d->getMillingDescription();
                    $i++;
                }
            }
            return $lineItem;
        }
    }

    private function getGrainPattern($id,$type=''){
        if($type=='door'){
            $grainId= $this->getDoctrine()->getRepository('AppBundle:Skins')->findOneBy(['doorId'=>$id]);
            if(!empty($grainId)){
                $id=$grainId->getGrain();
            } else {
                $id=0;
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
        $data= $this->getDoctrine()->getRepository('AppBundle:GrainPattern')->findOneBy(['id'=>$id]);
        if(!empty($data)){
            return $data->getAbbr();
        } else {
            return '';
        }
    }

    private function convertMmToInches($mm) {
        return $mm * 0.0393701;
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

    private function getCustomerNameByQuote($quoteId) {
        $quoteRecord = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneById($quoteId);
        $custId = $quoteRecord->getCustomerId();
        return $this->getCustomerNameById($custId);

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

    private function replaceShortcodeFromMessage($custName,$userName,$projName,$message) {
        $shortCodes = array('{first_name_customer}','{project_name}','{user_first_name}');
        $values  = array($custName, $projName, $userName);
        return str_replace($shortCodes, $values, $message);
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

    private function updateQuoteData($quoteId) {
        //echo $quoteId;
        $salesTaxRate = 0;
        $salesTaxAmount = 0;
        $quoteSubTotal = $this->getPlywoodSubTotalByQuoteId($quoteId) + $this->getVeneerSubTotalByQuoteId($quoteId) + $this->getDoorSubTotalByQuoteId($quoteId);
        $quoteData = $this->getQuoteDataById($quoteId);
        $shipAddId = $quoteData->getShipAddId();
        $shipCharge = $quoteData->getShipCharge();
        $expFee = $quoteData->getExpFee();
        $discount = $quoteData->getDiscount();
        if (!empty($shipAddId)) {
            $salesTaxRate = $this->getSalesTaxRateByAddId($shipAddId);
        }
        $salesTaxAmount = (($quoteSubTotal ) * ($salesTaxRate)) / 100;
        //$shipCharge = $this->getShippingChargeByAddId($shipAddId);
        $lumFee = $this->getPlywoodLumberFeeByQuoteId($quoteId) + $this->getVeneerLumberFeeByQuoteId($quoteId);
        $projectTotal = ($quoteSubTotal + $expFee - $discount + $salesTaxAmount + $shipCharge + $lumFee);
        $this->saveQuoteCalculatedData($quoteId, $quoteSubTotal, $salesTaxAmount, $shipCharge, $lumFee, $projectTotal);
    }

    private function saveQuoteCalculatedData($quoteId, $quoteSubTotal, $salesTaxAmount, $shipCharge, $lumFee, $projectTotal) {
        $em = $this->getDoctrine()->getManager();
        $quote = $em->getRepository(Quotes::class)->findOneById($quoteId);
        $datime = new \DateTime('now');
        if (!empty($quote)) {
            if(!empty($quoteSubTotal)){
                $quote->setSalesTax($salesTaxAmount);
                $quote->setShipCharge($shipCharge);
                $quote->setLumFee($lumFee);
            } else {
                $quote->setSalesTax(0);
                $quote->setShipCharge(0);
                $quote->setLumFee(0);
                $quote->setExpFee(0);
                $quote->setDiscount(0);
            }
            $quote->setQuoteTot($quoteSubTotal);            
            $quote->setProjectTot($projectTotal);
            $quote->setUpdatedAt($datime);
            $em->persist($quote);
            $em->flush();
        }
    }

    private function getShippingChargeByAddId($shipAddId) {
        $shipCharge = 0;
        $add = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findById($shipAddId);
        if (!empty($add)) {
            $shipCharge = $add[0]->getDeliveryCharge();
        }
        return $shipCharge;
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

    private function getDoorSubTotalByQuoteId($quoteId) {
        $subtotal = 0;
        $query = $this->getDoctrine()->getManager();
        $doorRecords = $query->createQueryBuilder()
            ->select(['dc.totalCost as totolCost'])
            ->from('AppBundle:DoorCalculator', 'dc')
            ->leftJoin('AppBundle:Doors', 'd', 'WITH', 'dc.doorId = d.id')
            ->where('d.quoteId = '.$quoteId, 'd.status = 1')
            ->getQuery()
            ->getResult();
        //print_r($doorRecords);die;
//        $doorRecords = $this->getDoctrine()->getRepository('AppBundle:Doors')->findBy(array('quoteId' => $quoteId,'status'=>1));
        if (!empty($doorRecords)) {
            $i=0;
            foreach ($doorRecords as $d) {
                $subtotal += $d['totolCost'];
                $i++;
            }
        } else {
            $subtotal = 0;
        }
        return $subtotal;
    }

    private function getSalesTaxRateByAddId($shipAddId) {
        $salesTaxRate = 0;
        $add = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findById($shipAddId);
        if (!empty($add)) {
            $salesTaxRate = $add[0]->getSalesTaxRate();
        }
        return $salesTaxRate;
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
    
    private function float2rat($num = 0.0, $err = 0.001)
    {
        if ($err <= 0.0 || $err >= 1.0)
        {
            $err = 0.001;
        }

        $sign = ($num > 0) ? 1 : (($num < 0) ? - 1 : 0);

        if ($sign === - 1)
        {
            $num = abs($num);
        }

        if ($sign !== 0)
        {
            // $err is the maximum relative $err; convert to absolute
            $err *= $num;
        }

        $n = (int) floor($num);
        $num -= $n;

        if ($num < $err)
        {
            return (string) ($sign * $n);
        }

        if (1 - $err < $num)
        {
            return (string) ($sign * ($n + 1));
        }

        // The lower fraction is 0/1
        $lower_n = 0;
        $lower_d = 1;

        // The upper fraction is 1/1
        $upper_n = 1;
        $upper_d = 1;

        while (true)
        {
            // The middle fraction is ($lower_n + $upper_n) / (lower_d + $upper_d)
            $middle_n = $lower_n + $upper_n;
            $middle_d = $lower_d + $upper_d;

            if ($middle_d * ($num + $err) < $middle_n)
            {
                // real + $err < middle : middle is our new upper
                $upper_n = $middle_n;
                $upper_d = $middle_d;
            }
            elseif ($middle_n < ($num - $err) * $middle_d)
            {
                // middle < real - $err : middle is our new lower
                $lower_n = $middle_n;
                $lower_d = $middle_d;
            }
            else
            {
                // Middle is our best fraction
                return (string) (($n * $middle_d + $middle_n) * $sign) . '/' . (string) $middle_d;
            }
        }

        return '0'; // should be unreachable.
    }

    private function formateDeliveryCharge($dc) {
        $arrExp = array('$',',');
        $arrRep = array('','');
        return str_replace($arrExp, $arrRep, $dc);
    }

    private function checkIfSearchValIsEstOrCompany($searchVal) {
        $type = '';
        if (preg_match_all ("/(.)(-)(\\d+)/", $searchVal, $matches))
        {
            $type = 'estNo';
        } else {
            $type = 'company';
        }
        return $type;
    }
    
    private function getQuoteStatusId($name){
        $query = $this->getDoctrine()->getManager();
        $result = $query->createQueryBuilder()
            ->select(['s.id'])
            ->from('AppBundle:Status', 's')
            ->where("s.statusName='".$name."' and s.type='Quote'")
            ->getQuery()
            ->getResult();
            return !empty($result[0]['id'])?$result[0]['id']:6;
    }
    
    private function updateEditOrderStatusWithQuoteCost($array){
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            
            $quote = $em->getRepository(Quotes::class)->findOneById($array['clonedQuoteId']);
            $totalLineItemCost= !empty($array['plyCost'])? str_replace(',','',$array['plyCost']):0
                            + !empty($array['doorCost'])?str_replace(',','',$array['doorCost']):0
                            + !empty($array['veneerCost'])?str_replace(',','',$array['veneerCost']):0;
            $projectTot =   $totalLineItemCost 
                            + str_replace(',', '', $quote->getExpFee())
                            - str_replace(',', '', $quote->getDiscount())
                            + str_replace(',', '', $quote->getShipCharge())
                            + str_replace(',', '', $quote->getSalesTax());
            $quote->setQuoteTot($totalLineItemCost);
            $quote->setProjectTot($projectTot);
            $em->persist($quote);
            $em->flush();
            
            
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
        
    }

    private function p($a){
        print_r($a);die;
    }

    private function getEstimatenoByQId($qid) {
        $em = $this->getDoctrine()->getManager();
        $quote = $em->getRepository(Quotes::class)->findOneById($qid);
        return 'E-'.$quote->getControlNumber().'-'.$quote->getVersion();
    }
    
    private function getLastControlNumberById() {
        $lastQuote = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneBy(array(),array('id'=>'desc'));
        if (!empty($lastQuote)) {
            return $lastQuote->getId();
        }
    }
    
    private function getTermName($id){
        $lastQuote = $this->getDoctrine()->getRepository('AppBundle:Terms')->findOneById($id);
        if (!empty($lastQuote)) {
            return $lastQuote->getName();
        } else {
            return '';
        }
    }

    private function getQuoteHtmlByQuoteId($qId) {
        $images_destination = $this->container->getParameter('images_destination');
        $arrApi = array();
        $statusCode = 200;
        try {
            $quoteId = $qId;
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
                $arrApi['data']['referenceNumber'] = $quoteData->getRefNum();
                $arrApi['data']['salesman'] = $this->getSalesmanNameById($quoteData->getSalesmanId());
                $arrApi['data']['salesmanId'] = $quoteData->getSalesmanId();
                $arrApi['data']['job'] = $quoteData->getJobName();
                $arrApi['data']['term'] = (string) $quoteData->getTermId();
                $arrApi['data']['shipMethod'] = $this->getShipMethodNamebyId($quoteData->getShipMethdId());
                $arrApi['data']['shipMethodId'] = $quoteData->getShipMethdId();
                $arrApi['data']['billAdd'] = $this->getBillAddById($quoteData->getCustomerId());
                $arrApi['data']['shipAdd'] = $this->getShippingAddById($quoteData->getShipAddId());
                $arrApi['data']['shipAddId'] = $quoteData->getShipAddId();
                $arrApi['data']['leadTime'] = $quoteData->getLeadTime();
                $arrApi['data']['status'] = $quoteData->getStatus();
                $arrApi['data']['comment'] = $quoteData->getComment();
                $arrApi['data']['deliveryDate'] = $quoteData->getDeliveryDate();
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
                $arrApi['data']['lineitems'] = $this->getVeneerslistbyQuoteId($quoteId);
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        //print_R($arrApi['data']);

        $calSqrft = 0;

        foreach($arrApi['data']['lineitems'] as $key=>$qData){
            $calSqrft += ((float)($qData['width'] + $qData['widthFraction'])*(float)($qData['length'] + $qData['lengthFraction']))/144;
        }

        $html = "<!DOCTYPE html>
                <html>
                <head>
                        <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700' rel='stylesheet'>
                        <style>
                            body{font-family:'Roboto Condensed',sans-serif;font-weight:400;line-height:1.4;font-size:14px;}table{width:100%;border-collapse:collapse;border-spacing:0;margin:0 0 15px;}body h1,body h2,body h3,body label,body strong,table.prodItmLst th,table.prodItmLst td{font-family:'Roboto Condensed',Arial,sans-serif;font-weight:700;}table h3{font-size:16px;color:#272425;}table.invoiceHeader table{margin:0;}
                            .invoiceWrap{width:1100px;margin:auto;border:solid 1px #7f7d7e;padding:18px 22px;}.invoiceHeader td{vertical-align:top;}.invCapt{width:170px;text-align:center;}.captBTxt{font-size:36px;color:#919396;text-transform:uppercase;line-height:1;}.subTxt{font-size:18px;color:#919396;margin:0 0 6px;}.invCapt p{color:#434041;font-size:13px;margin:0 0 12px;}.invCapt p:last-of-type{margin:0;}.invLogo{margin:0 0 20px;line-height:0;}.addressHldr .addCell{width:50%;padding:0;}.addCellDscHldr td.cellDescLbl{width:96px;text-align:right;padding:0 8px 0 0;vertical-align:middle;color:#a9abad;font-size:11px;text-transform:uppercase;}.addCellDscHldr td.cellDescTxt{padding:0 8px;border-left:solid 2px #918f90;vertical-align:top;}.addCellDscHldr td.cellDescTxt h3,.addCellDscHldr td.cellDescTxt p{margin:0;padding:0;}.addCellDscHldr td.cellDescTxt p{font-size:14px;}.custOdrDtls{padding:10px 0 8px;margin-bottom:18px;border-bottom:solid 1px #a0a0a0;color:#000000;font-size:13px;}.custOdrDtls p{margin:0;padding:0;}.custOdrDtls table{width:auto;margin:0;}.custOdrDtls table td{padding:0 14px;border-left:solid 1px #a0a0a0;}.custOdrDtls table td:first-child{padding-left:0;border-left:0;}.itemListTablHldr{min-height:300px;}table.prodItmLst th,table.prodItmLst td{font-size:14px;text-align:center;padding:3px 6px;vertical-align:top;}table.prodItmLst th.t-left,table.prodItmLst td.t-left{text-align:left;}table.prodItmLst th{color:#ababab;}table.prodItmLst td{font-family:'Roboto Condensed',sans-serif; font-size:14px; color:#302d2e;}table.prodItmLst td a{color:#302d2e !important;text-decoration:none;}.invoiceCtnr table.prodItmLst td a:hover{color:#000000;}table.prodItmLst td:first-child{text-align:left;}table.prodItmLst td:last-child{text-align:right;}table.prodItmLst tr th,table.prodItmLst tr td{border-bottom:solid 1px #a0a0a0;font-weight:700;}table.invoiceFootBtm td{vertical-align:top;color:#171717;font-size:13px;padding:3px 8px;}table.invoiceFootBtm td.sideBox{width:220px;}td.midDesc{text-align:center;}.sideBox .name{margin:0 0 12px;font-weight:700;text-align:left;padding-left:10px;}.invoiceFooter .custOdrDtls{margin-bottom:12px;}.invoiceFooter table{margin:0;}.sideBox .totalTxt td{color:#222222;font-size:14px;padding:0;text-align:right;}.sideBox table.totalTxt tr td:last-child{padding-right:0;}.midDescTxt{width:535px;margin:auto;font-size:11px;padding:4px 0 0;}.midDescTxt p{margin:0 0 8px;}.warnTxt{background-color:#e5e5e6;padding:4px 12px;width:250px;text-align:center;}.warnTxt p{margin:0;padding:0;font-size:11px;font-weight:700;}.warnTxt h3{font-size:18px;text-transform:uppercase;margin:0;padding:0;color:#7a7b7e;}table.totalPrice td{font-size:12px;color:#000000;padding:3px 6px;vertical-align:top;text-align:right;font-weight:400;}table.totalPrice td.price{width:110px;}.invoiceFooter{border-top:solid 1px #a0a0a0;padding-top:8px;}table.invoiceFootBtm td.sideBox.note{text-align:center;width:320px;}.sideBox.note p{font-size:10px;margin:0 0 4px;font-weight:400;}td .approved{border:solid 1px #a0a0a0;padding:10px;font-size:12px;width:200px;text-align:center;}.t-center{text-align:center;}td .approved p{margin:0;}
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
                                        <div class='captBTxt'>Quote</div>
                                        <div class='subTxt'>Q-".$arrApi['data']['controlNumber']."-".$arrApi['data']['version']."</div>
                                        <p>".$arrApi['data']['date']." &nbsp;|&nbsp; Net 30 days</p>
                                        <p><em>At Talbert Architectural <br>Your Total Satisfaction <br>is Our Goal!</em></p>
                                    </td>
                                </tr>
                            </table>
                            
            
                            <div class='custOdrDtls'>
                                <table>
                                    <tr>
                                        <td>Customer Reference: PO-".$arrApi['data']['referenceNumber']."</td>
                                        <td>Job Name: ".$arrApi['data']['job']."</td>
                                        <td>Delivery Date: ".$arrApi['data']['leadTime']."</td>
                                        <td>Ship Via: ".$arrApi['data']['shipMethod']."</td>
                                        <td>SqFt: ".number_format($calSqrft,2)."</td>
                                    </tr>
                                </table>
                            </div>
                            
            
                            <div class='itemListTablHldr'>
                                <table class='prodItmLst'>
                                    <thead>
                                        <tr>
                                            <th width='30px'>#</th>
                                            <th>Qty</th>
                                            <th>Grain</th>
                                            <th>Species</th>
                                            <th>Grd</th>
                                            <th>PTRN</th>
                                            <th>Back</th>
                                            <th>Dimensions</th>
                                            <th>Core</th>
                                            <th class='t-left'>Details</th>
                                            <th width='80px'>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

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

            //$calSqrft2 = ((float)($qData['width'] + $this->convertToDecimal($qData['widthFraction']))*(float)($qData['length'] + $this->convertToDecimal($qData['lengthFraction'])))/144;


            $html .= "<tr>
                                            <th>".$index."</th>
                                            <td>".$qData['quantity']."</td>
                                            <td>".$qData['grain']."</td>
                                            <td>".$qData['species']."</td>
                                            <td>".$qData['grade']."</td>
                                            <td>".$qData['pattern']."</td>
                                            <td>".$qData['back']."</td>
                                            <td>".$qData['width']."-".$qData['widthFraction']." x ".$qData['length']."-".$qData['lengthFraction']." x ".$qData['thickness']."</td>
                                            <td>".$qData['core']."</td>";
            $html .= ($qData['edgeDetail'] == 1) ? "<td class='t-left'>Edge Detail: TE-".$this->getEdgeNameById($qData['topEdge'])."|BE-".$this->getEdgeNameById($qData['bottomEdge'])."|RE-".$this->getEdgeNameById($qData['rightEdge'])."|LE-".$this->getEdgeNameById($qData['leftEdge'])."<br>" : "<td class='t-left'>";
            $html .= ($qData['milling'] == 1) ? "Miling: ".$qData['millingDescription'].' '.$this->getUnitNameById($qData['unitMesureCostId'])."<br>" : "";
            if ($qData['finish'] == 'UV') {
                $html .= "Finish: UV-".$qData['uvCuredId']."-".$qData['sheenId']." %-".$qData['shameOnId'].$qData['coreSameOnbe'].$qData['coreSameOnte'].$qData['coreSameOnre'].$qData['coreSameOnle']."<br>";
            } elseif ($qData['finish'] == 'Paint') {
                $html .= "Finish: Paint-".$qData['facPaint']."-".$qData['uvCuredId']."-".$qData['sheenId']." %".$qData['shameOnId'].$qData['coreSameOnbe'].$qData['coreSameOnte'].$qData['coreSameOnre'].$qData['coreSameOnle']."<br>";
            }
            $html .= ($qData['comment']) ? "Comment: ".$qData['comment']."<br>" : "";
            $html .= ($qData['isLabels']) ? "Label:".$qData['autoNumber']."</td>" : "";

            $html .="<td>$".$qData['unitPrice']."</td>
                                            <td>$".$qData['totalPrice']."</td>
                                        </tr>";

        }

        $html .= "</tbody>
                                            </table>
                                            <table class='totalPrice'>
                                                <tr>
                                                    <td>Special Tooling - Description</td>
                                                    <td class='price'>$350.00</td>
                                                </tr>
                                            </table>
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
                                                <p>&nbsp;&nbsp;&nbsp;</p>
                                                <p>&nbsp;&nbsp;&nbsp;</p>
                                            </div>
                                        </td>
                                        <td>
                                            <img src='".$images_destination."/fsc-order.png' alt=''>
                                        </td>
                                        <td class='sideBox'>
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
                                        </td>
                                    </tr>
                                </table>
                            </div>
            
                        </div>
                        
                    </div>
                </body>
            </html>";
        return $html;
    }

    private function approveQuoteForBackOrder($qId, $estNo, $approveBy, $via, $other, $custPO) {
        $datime = new \DateTime('now');
        $deliveryDate = '';
        $orderDate = $this->getOrderDate($qId);
        $orderExists = $this->checkIfOrderAlreadyExists($qId);
        if ($orderExists) {
            $this->updateOrderData($qId, $estNo, $estNo, $approveBy, $via, $other, $orderDate, $custPO, $deliveryDate);
        } else {
            $this->saveOrderData($qId, $estNo, $estNo, $approveBy, $via, $other, $orderDate, $custPO, $deliveryDate);
        }
        $this->updateQuoteStatus($qId, 'Approved', $deliveryDate, $datime);
    }
}