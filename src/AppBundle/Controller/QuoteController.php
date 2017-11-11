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
use AppBundle\Entity\Veneer;
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
                $arrApi['message'] = 'Parameter missing';
                $statusCode = 422;
            } else {
                $updateQuote = $this->updateData($qId, $qDate, $quoteAddedby, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $datime);
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
            $pdfName = 'uploads/quote_screenshots/quotePDF-'.$qId.'-'.time().'.pdf';
            $quotePdfUrl = $this->createQuoteLineitemPDF($html, $pdfName, $request);
            $newMessage = $this->createMessageToBeSent($msg, $cmt);
            $urls = $this->getAttachmentUrls($chkVal, $request);
            $urls[] = $quotePdfUrl;
            $message = \Swift_Message::newInstance()
                ->setFrom($currUserEmail)
                ->setTo($custEmail)
                ->setSubject('Quote Email')
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
     * @Route("/api/quote/searchQuote")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function searchQuoteAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $data = $jsontoarraygenerator->getJson($request);
        $quote = $data->get('quote');
        $currUserId = $data->get('currentuserId');
        if (empty($quote) || empty($currUserId)) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'Parameter missing.';
            $statusCode = 422;
        } else {
            try {
                echo 'hi';die;
            }
            catch(Exception $e) {
                throw $e->getMessage();
            }
        }
        return new JsonResponse($arrApi, $statusCode);
    }


    //Reusable codes

    private function createQuoteLineitemPDF($html,$pdfName, $request) {
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

    private function updateData($qId, $qDate, $quoteAddedby, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $datime) {
        $em = $this->getDoctrine()->getManager();
        $quote = $em->getRepository(Quotes::class)->findOneById($qId);
        if (!empty($quote)) {
            $quote->setEstimatedate($qDate);
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
            $quote->setUpdatedAt($datime);
            $em->persist($quote);
            $em->flush();
            return 1;
        }
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
        $quote->setControlNumber($this->getLastControlNumber()+1);
        $quote->setVersion($qData->getVersion());
        $quote->setCustomerId($qData->getCustomerId());
        //$quote->setRefNum($qData->getRefNum());
        $quote->setSalesmanId($qData->getSalesmanId());
        //$quote->setJobName($qData->getJobName());
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
        $plywoodRecords = $this->getDoctrine()->getRepository('AppBundle:Plywood')->findBy(array('quoteId' => $qId,'isActive'=>1));
        $veneerRecords = $this->getDoctrine()->getRepository('AppBundle:Veneer')->findBy(array('quoteId' => $qId,'isActive'=>1));
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
}