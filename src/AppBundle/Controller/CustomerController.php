<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Addresses;
use AppBundle\Entity\CustomerProfiles;
use AppBundle\Entity\Discounts;
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
use AppBundle\Entity\User;
use AppBundle\Entity\Profile;
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
			$jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $currLoggedInUserId = $data->get('current_user_id');
			$currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);

			$company = trim($data->get('company'));
			$fName = trim($data->get('fname'));
			$passwd = password_hash('123456', PASSWORD_DEFAULT);
            $roleId = '11';
            $isActive = $data->get('is_active');
            $usrType = 'customer';
			$phone = trim($data->get('phone'));	
			$email = trim($data->get('email'));
            $usrname = $email;
			$trmId = $data->get('term');
			$comment = trim($data->get('comment'));	
			$bStreet = trim($data->get('billingStreet'));	
			$bCity = trim($data->get('billingCity'));	
			$bState = $data->get('billingState');	
			$bZip = trim($data->get('billingZip'));	
			$prdArr = $data->get('products');	
			$shipArr = $data->get('shipp');
            $datime = new \DateTime('now');
            if ( empty($currLoggedInUserRoleId) || $currLoggedInUserRoleId != '1') {
				$arrApi['status'] = 0;
				$arrApi['message'] = 'You are not allowed add customers';
			} else {
				if ( empty($company) || $isActive > 1 || empty ($fName) || empty ($phone) || empty ($email) || empty ($trmId) || empty ($bStreet) || empty ($bCity) || empty($bState) || empty ($bZip) || empty ($prdArr) || empty ($shipArr) || empty (trim($prdArr[0]['products'])) || empty (trim($shipArr[0]['nickname'])) || empty (trim($shipArr[0]['street'])) || empty (trim($shipArr[0]['city'])) || empty (trim($shipArr[0]['state'])) || empty (trim($shipArr[0]['zip'])) || empty (trim($shipArr[0]['deliveryCharge'])) || empty (trim($shipArr[0]['salesTaxRate'])) ) {
					$arrApi['status'] = 0;
					$arrApi['message'] = 'Please fill all required fields';
				} else {
					if ( is_numeric($phone) && strlen($phone) > 10 ) {
						$arrApi['status'] = 0;
						$arrApi['message'] = 'Please enter correct phone number';
					} else {
						if ( !filter_var($email,FILTER_VALIDATE_EMAIL )) {
							$arrApi['status'] = 0;
							$arrApi['message'] = 'Invalid email address';
						} else {
                            $phoneCount = $this->checkIfPhoneExists($phone);
                            if ($phoneCount) {
                                $arrApi['status'] = 0;
                                $arrApi['message'] = 'This Phone Address already exists in the database.';
                            } else {
                                $emailCount = $this->checkIfEmailExists($email);
                                if ($emailCount) {
                                    $arrApi['status'] = 0;
                                    $arrApi['message'] = 'This Email number already exists in the database.';
                                } else {
                                    $lastUserId = $this->saveCustomerData($usrname, $passwd, $roleId, $isActive, $datime, $usrType);
                                    if (empty($lastUserId)) {
                                        $arrApi['status'] = 0;
                                        $arrApi['message'] = 'Error while adding customer.';
                                    } else {
                                        $arrApi['status'] = 1;
                                        $arrApi['message'] = 'Customer addedd successfully.';
                                        $lstPrfId = $this->saveProfileData($lastUserId, $company, $fName, $email, $phone);
                                        if (empty($lstPrfId)) {
                                            $arrApi['profileMessage'] = 'Profile Data not saved.';
                                        } else {
                                            $this->saveCustomerProfile($trmId, $comment, $lastUserId);
                                            $this->saveBillingAddress($bStreet, $bCity, $bState, $bZip, $lastUserId, $datime);
                                            $this->saveShippingAddress($shipArr, $lastUserId, $datime);
                                            $this->saveDiscountData($prdArr, $lastUserId);
                                        }
                                    }
                                }
                            }
						}
					}
				}	
			}
            return new JsonResponse($arrApi);
        }
    }

    // Reusable code

    private function saveShippingAddress($shipArr, $lastUserId, $datime) {
        $em = $this->getDoctrine()->getManager();
        foreach ($shipArr as $val) {
            $addresses = new Addresses();
            $addresses->setNickname($val['nickname']);
            $addresses->setStreet($val['street']);
            $addresses->setCity($val['city']);
            $addresses->setStateId($val['state']);
            $addresses->setZip($val['zip']);
            $addresses->setDeliveryChargeId($val['deliveryCharge']);
            $addresses->setSalesTaxRate($val['salesTaxRate']);
            $addresses->setAddressType('billing');
            $addresses->setStatus(1);
            $addresses->setUserId($lastUserId);
            $addresses->setUpdatedAt($datime);
            $em->persist($addresses);
            $em->flush();
        }
    }

    private function saveDiscountData($prdArr, $lastUserId) {
        $rate = 10;
        $sts = 1;
        $em = $this->getDoctrine()->getManager();
        foreach ($prdArr as $val) {
            $dis = new Discounts();
            $dis->setProductName($val['products']);
            $dis->setUserId($lastUserId);
            $dis->setRate($rate);
            $dis->setStatus($sts);
            $em->persist($dis);
            $em->flush();
        }
    }

    private function saveCustomerData($usrname, $passwd, $roleId, $isActive, $datime, $usrType) {
        $em = $this->getDoctrine()->getManager();
        $user = new User();
        $user->setUsername($usrname);
        $user->setPassword($passwd);
        $user->setRoleId($roleId);
        $user->setIsActive($isActive);
        $user->setCreatedAt($datime);
        $user->setUpdatedAt($datime);
        $user->setUserType($usrType);
        $em->persist($user);
        $em->flush();
        return $user->getId();
    }

    private function saveProfileData($lastUserId, $company, $fName, $email, $phone) {
        $em = $this->getDoctrine()->getManager();
        $profile = new Profile();
        $profile->setUserId($lastUserId);
        $profile->setCompany($company);
        $profile->setFname($fName);
        $profile->setEmail($email);
        $profile->setPhone($phone);
        $em->persist($profile);
        $em->flush();
        return $profile->getId();
    }

    private function saveCustomerProfile($trmId, $comment, $lastUserId) {
        $em = $this->getDoctrine()->getManager();
        $custProf = new CustomerProfiles();
        $custProf->setTermId($trmId);
        $custProf->setComment($comment);
        $custProf->setUserId($lastUserId);
        $em->persist($custProf);
        $em->flush();
    }

    private function saveBillingAddress($bStreet, $bCity, $bState, $bZip, $lastUserId, $datime) {
        $em = $this->getDoctrine()->getManager();
        $addresses = new Addresses();
        $addresses->setStreet($bStreet);
        $addresses->setCity($bCity);
        $addresses->setStateId($bState);
        $addresses->setZip($bZip);
        $addresses->setAddressType('billing');
        $addresses->setStatus(1);
        $addresses->setUserId($lastUserId);
        $addresses->setUpdatedAt($datime);
        $em->persist($addresses);
        $em->flush();
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

    private function checkIfEmailExists($email) {
        $emailData = $this->getDoctrine()
            ->getRepository('AppBundle:Profile')
            ->findOneBy(array('email' => $email));
        if (count($emailData) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    private function checkIfPhoneExists($phone) {
        $phoneData = $this->getDoctrine()
            ->getRepository('AppBundle:Profile')
            ->findOneBy(array('phone' => $phone));
        if (count($phoneData) > 0 ) {
            return true;
        } else {
            return false;
        }
    }
 
}
