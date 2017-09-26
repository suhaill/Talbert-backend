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
use PDO;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class QuoteController extends Controller
{
    /**
     * @Route("/api/quote/addQuote")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: None
     */
    public function addQuoteAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            //print_r($data);die;
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
                $arrApi['message'] = 'Please fill all the fields';
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
                    $this->saveQuoteData($qDate, $quoteAddedby, $ctrlNo, $ver, $custId, $refNo, $salsManId, $job, $termId, $shipMethod, $shipAddId, $leadTime, $status, $datime);
                }
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }



    //Reusable codes
    private function getLastControlNumber() {
        $lastQuote = $this->getDoctrine()->getRepository('AppBundle:Quotes')->findOneBy(array(),array('id'=>'desc'));
        return $lastQuote->getControlNumber();
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
    }

}
