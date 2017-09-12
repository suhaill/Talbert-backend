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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomerController extends Controller
{
    /**
     * @Route("/api/customer/addCustomer")
     * @Method("POST")
     * params: Various
     */
    public function addCustomerAction(Request $request){
        if ($request->getMethod() == 'POST') {
            $arrApi = array();
			$statusCode = 200;
			$jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);

			$currLoggedInUserId = $data->get('current_user_id');	
			$currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);

			$company = trim($data->get('company'));
			$isActive = trim($data->get('is_active'));	
			$fName = trim($data->get('fname'));	
			$phone = trim($data->get('phone'));	
			$email = trim($data->get('email'));	
			$trmId = $data->get('term');
			$comment = trim($data->get('comment'));	
			$bStreet = trim($data->get('billingStreet'));	
			$bCity = trim($data->get('billingCity'));	
			$bState = $data->get('billingState');	
			$bZip = trim($data->get('billingZip'));	
			$prdArr = $data->get('products');	
			$shipArr = $data->get('shipp');	
			if ( empty($currLoggedInUserRoleId) || $currLoggedInUserRoleId != '1') {
				$arrApi['status'] = 0;
				$arrApi['message'] = 'You are not allowed add customers';
				$statusCode = 422;
			} else {
				if ( $isActive > 1 || empty ($fName) || empty ($phone) || empty ($email) || empty ($trmId) || empty ($bStreet) || empty ($bCity) || empty($bState) || empty ($bZip) || empty ($prdArr) || empty ($shipArr) ) {
					$arrApi['status'] = 0;
					$arrApi['message'] = 'Please fill all required fields';
					$statusCode = 422;
				} else {
					if ( is_numeric($phone) && strlen($phone) > 10 ) {
						$arrApi['status'] = 0;
						$arrApi['message'] = 'Please enter correct phone number';
						$statusCode = 422;	
					} else {
						if ( !filter_var($email,FILTER_VALIDATE_EMAIL )) {
							$arrApi['status'] = 0;
							$arrApi['message'] = 'Invalid email address';
							$statusCode = 422;
						} else {
							
						}
					}
				}	
			}
            return new JsonResponse($arrApi,$statusCode);
        }
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
 
}
