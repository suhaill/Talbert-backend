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
     * @Security("is_granted('ROLE_USER')")
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
                $arrApi['message'] = 'You are not allowed to add customers';
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
                                $arrApi['message'] = 'This Phone already exists in the database.';
                            } else {
                                $emailCount = $this->checkIfEmailExists($email);
                                if ($emailCount) {
                                    $arrApi['status'] = 0;
                                    $arrApi['message'] = 'This Email address already exists in the database.';
                                } else {
                                    $lastUserId = $this->saveCustomerData($usrname, $passwd, $roleId, $isActive, $datime, $usrType);
                                    if (empty($lastUserId)) {
                                        $arrApi['status'] = 0;
                                        $arrApi['message'] = 'Error while adding customer.';
                                    } else {
                                        $arrApi['status'] = 1;
                                        $arrApi['message'] = 'Customer added successfully.';
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

    /**
     * @Route("/api/customer/getCustomersList")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     * params: None
     */
    public function getCustomersAction(Request $request){
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $users = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'),array('id' => 'DESC'),5);
            if ( empty($users) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no user.';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the customers list.';
                for ($i=0; $i<count($users); $i++) {
                    $userId = $users[$i]->getId();
                    if (!empty($userId)) {
                        $arrApi['data']['customers'][$i]['id'] = $users[$i]->getId();
                        $arrApi['data']['customers'][$i]['fname'] = $this->getFnameById($userId);
                        $arrApi['data']['customers'][$i]['comapny'] = $this->getCompanyById($userId);
                        $arrApi['data']['customers'][$i]['createdDate'] = $this->getCreatedDateById($userId);
                    }
                }
            }
            return new JsonResponse($arrApi);
        }
    }

    /**
     * @Route("/api/customer/getCustomerDetails")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     * params: customer_id (user_id), current_user_id
     */
    public function getCustomerDetailsAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $arrApi = array();
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $userId = $data->get('customer_id');
            $currLoggedInUserId = $data->get('current_user_id');
            $currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);
            if ( empty($userId) || empty($currLoggedInUserId)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please specify the customer and logged in user.';
            } else {
                if ( empty($currLoggedInUserRoleId) || $currLoggedInUserRoleId != '1') {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'You are not allowed to view details';
                } else {
                    $customerExists = $this->checkIfCustomerExists($userId);
                    if (!$customerExists) {
                        $arrApi['status'] = 0;
                        $arrApi['message'] = 'This customer does not exists';
                    } else {
                        $arrApi['status'] = 1;
                        $arrApi['message'] = 'Successfuly retreived customer data';
                        $arrApi['data']['customer'] = $this->getCustomerData($userId);

                    }
                }
            }
            return new JsonResponse($arrApi);
        }
    }

    /**
     * @Route("/api/customer/updateCustomerDetails")
     * @Security("is_granted('ROLE_USER')")
     * @Method("PUT")
     * params: All customer details with customer_id (user_id), current_user_id
     */

    public function updateCustomerAction(Request $request) {
        if ($request->getMethod() == 'PUT') {
            $arrApi = array();
            $statusCode = 200;
            try {
                $jsontoarraygenerator = new JsonToArrayGenerator();
                $data = $jsontoarraygenerator->getJson($request);
                $userId = $data->get('customer_id');
                $currLoggedInUserId = $data->get('current_user_id');
                $currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);
                $company = trim($data->get('company'));
                $fName = trim($data->get('fname'));
                $isActive = $data->get('is_active');
                $phone = trim($data->get('phone'));
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
                    $arrApi['message'] = 'You are not allowed to update customers';
                    $statusCode = 422;
                } else {
                    if ( empty($company) || $isActive > 1 || empty ($fName) || empty ($phone) || empty ($trmId) || empty ($bStreet) || empty ($bCity) || empty($bState) || empty ($bZip) || empty ($prdArr) || empty ($shipArr) || empty (trim($prdArr[0]['products'])) || empty (trim($shipArr[0]['nickname'])) || empty (trim($shipArr[0]['street'])) || empty (trim($shipArr[0]['city'])) || empty (trim($shipArr[0]['state'])) || empty (trim($shipArr[0]['zip'])) || empty (trim($shipArr[0]['deliveryCharge'])) || empty (trim($shipArr[0]['salesTaxRate'])) ) {
                        $arrApi['status'] = 0;
                        $arrApi['message'] = 'Please fill all required fields';
                        $statusCode = 422;
                    } else {
                        if ( is_numeric($phone) && strlen($phone) > 10 ) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Please enter correct phone number';
                            $statusCode = 422;
                        } else {
                            $phoneCount = $this->checkIfOthrUsrHasThisPhone($phone, $userId);
                            if ($phoneCount) {
                                $arrApi['status'] = 0;
                                $arrApi['message'] = 'This Phone is already in user.';
                                $statusCode = 422;
                            } else {
                                $arrApi['status'] = 1;
                                $arrApi['message'] = 'Successfully updated customer data.';
                                $this->updateUserRecord($isActive, $company, $fName, $phone, $datime, $userId);
                                $this->updateBillingAddress($bStreet, $bCity, $bState, $bZip, $datime, $userId);
                                $this->updateShippingAddress($shipArr, $userId, $datime);
                                $this->updateDiscountData($prdArr, $userId);
                                $this->updateCustomerprofileData($trmId, $comment, $userId);
                            }
                        }
                    }
                }
            }
            catch(Exception $e) {
                throw $e->getMessage();
            }
            return new JsonResponse($arrApi, $statusCode);
        }
    }

    /**
     * @Route("/api/customer/paginateCustomers")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     */
    public function paginateCustomersAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $pageNo = $data->get('current_page');
            $limit = $data->get('limit');
            $custName = $data->get('customer_name');
            $sortBy = $data->get('sort_by');
            $order = $data->get('order');
            $offset = ($pageNo - 1)  * $limit;
            if (empty($pageNo) || empty($limit)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please post complete data';
                $statusCode = 422;
            } else {
                if (empty($custName) && empty($sortBy) && empty($order)) {
                    $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'),array('id' => 'DESC'), $limit, $offset);
                } else if (!empty($custName) && empty($sortBy) && empty($order)) {
                    $custIds = $this->getCustomerIdsByName($custName);
                    $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer','id' => $custIds),array('id' => 'DESC'), $limit, $offset);
                } else if (empty($custName) && !empty($sortBy) && !empty($order)) {
                    if ($sortBy == 'id' || $sortBy == 'createdAt') {
                        $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'),array($sortBy => $order), $limit, $offset);
                    } else {
                        $custIds =  $this->getCustomersIdsOnSortedData($sortBy, $order);
                        $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer','id' => $custIds), array(),$limit, $offset);
                    }
                } else if (!empty($custName) && !empty($sortBy) && !empty($order)) {
                    if ($sortBy == 'id' || $sortBy == 'createdAt') {
                        $custIds = $this->getCustomerIdsByName($custName);
                        $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer', 'id' => $custIds), array($sortBy => $order), $limit, $offset);
                    } else {
                        $custIds = $this->getSortedCustomerIds($custName, $sortBy, $order);
                        $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer', 'id' => $custIds), $limit, $offset);
                    }
                } else {
                    $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'), $limit, $offset);
                }
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived customers list';
                for ($i=0; $i<count($custData); $i++) {
                    $userId = $custData[$i]->getId();
                    if (!empty($userId)) {
                        $arrApi['data']['customers'][$i]['id'] = $custData[$i]->getId();
                        $arrApi['data']['customers'][$i]['fname'] = $this->getFnameById($userId);
                        $arrApi['data']['customers'][$i]['comapny'] = $this->getCompanyById($userId);
                        $arrApi['data']['customers'][$i]['createdDate'] = $this->getCreatedDateById($userId);
                    }
                }
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }

    // Reusable methods

    private function updateUserRecord($isActive, $company, $fName, $phone, $datime, $userId) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($userId);
        $user->setIsActive($isActive);
        $user->setUpdatedAt($datime);
        $em->persist($user);
        // Profile table update
        $profileId = $this->getProfileIdByUserId($userId);
        $profile = $em->getRepository(Profile::class)->find($profileId);
        $profile->setCompany($company);
        $profile->setFname($fName);
        $profile->setPhone($phone);
        $em->persist($profile);
        $em->flush();
    }

    private function updateBillingAddress($bStreet, $bCity, $bState, $bZip, $datime, $userId){
        $em = $this->getDoctrine()->getManager();
        $addressId = $this->getAddressIdByUserId('billing', $userId);
        $addresses = $em->getRepository(Addresses::class)->find($addressId);
        $addresses->setStreet($bStreet);
        $addresses->setCity($bCity);
        $addresses->setStateId($bState);
        $addresses->setZip($bZip);
        $addresses->setUpdatedAt($datime);
        $em->persist($addresses);
        $em->flush();

    }

    private function updateShippingAddress($shipArr, $userId, $datime){
        $deleteShipAdd = $this->deleteShippingAddressByUserId('shipping', $userId);
        if ($deleteShipAdd) {
            $em = $this->getDoctrine()->getManager();
            foreach ($shipArr as $val) {
                $addresses = new Addresses();
                $addresses->setNickname($val['nickname']);
                $addresses->setStreet($val['street']);
                $addresses->setCity($val['city']);
                $addresses->setStateId($val['state']);
                $addresses->setZip($val['zip']);
                $addresses->setDeliveryCharge($val['deliveryCharge']);
                $addresses->setSalesTaxRate($val['salesTaxRate']);
                $addresses->setAddressType('shipping');
                $addresses->setStatus(1);
                $addresses->setUserId($userId);
                $addresses->setUpdatedAt($datime);
                $em->persist($addresses);
                $em->flush();
            }
        }
    }

    private function updateDiscountData($prdArr, $userId) {
        $deleteDiscount = $this->deleteDiscountsDataByUserId($userId);
        if ($deleteDiscount) {
            $rate = 20;
            $sts = 1;
            $em = $this->getDoctrine()->getManager();
            foreach ($prdArr as $val) {
                $dis = new Discounts();
                $dis->setProductName($val['products']);
                $dis->setUserId($userId);
                $dis->setRate($rate);
                $dis->setStatus($sts);
                $em->persist($dis);
                $em->flush();
            }
            $em->flush();
        }
    }

    private function updateCustomerprofileData($trmId, $comment, $userId) {
        $em = $this->getDoctrine()->getManager();
        $custProfileId = $this->getCustProfIdByUserId($userId);
        if (!empty($custProfileId)) {
            $custProfile = $em->getRepository(CustomerProfiles::class)->find($custProfileId);
            $custProfile->setTermId($trmId);
            $custProfile->setComment($comment);
            $em->persist($custProfile);
        } else {
            $customerProfile = new CustomerProfiles();
            $customerProfile->setTermId($trmId);
            $customerProfile->setComment($comment);
            $customerProfile->setUserId($userId);
            $em->persist($customerProfile);
        }
        $em->flush();
    }

    private function getCustProfIdByUserId($userId) {
        $custProfile = $this->getDoctrine()->getRepository('AppBundle:CustomerProfiles')->findOneBy(array('userId' => $userId));
        if ( !empty($custProfile) ) {
            $custProfileId = $custProfile->getId();
        } else {
            $custProfileId = null;
        }
        return $custProfileId;
    }

    private function deleteShippingAddressByUserId($shipAdd, $userId) {
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="delete from addresses WHERE user_id = :userid AND address_type = :addType";
        $stmt=$conn->prepare($SQL);
        $stmt->bindParam(':userid',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':addType',$shipAdd,PDO::PARAM_STR);
        $stmt->execute();
        return true;
    }

    private function deleteDiscountsDataByUserId($userId) {
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="delete from discounts WHERE user_id = :userid";
        $stmt=$conn->prepare($SQL);
        $stmt->bindParam(':userid',$userId,PDO::PARAM_INT);
        $stmt->execute();
        return true;
    }

    private function getProfileIdByUserId($userId) {
        $profile = $this->getDoctrine()
            ->getRepository('AppBundle:Profile')
            ->findOneBy(array('userId' => $userId));
        if ( !empty($profile) ) {
            $profileId = $profile->getId();
        } else {
            $profileId = null;
        }
        return $profileId;
    }

    private function checkIfCustomerExists($userId) {
        if (!empty($userId)) {
            $userData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('id'=>$userId,'userType'=>'customer'));
            if (empty($userData)) {
                return false;
            } else {
                return true;
            }
        }
    }

    private function saveShippingAddress($shipArr, $lastUserId, $datime) {
        $em = $this->getDoctrine()->getManager();
        foreach ($shipArr as $val) {
            $addresses = new Addresses();
            $addresses->setNickname($val['nickname']);
            $addresses->setStreet($val['street']);
            $addresses->setCity($val['city']);
            $addresses->setStateId($val['state']);
            $addresses->setZip($val['zip']);
            $addresses->setDeliveryCharge($val['deliveryCharge']);
            $addresses->setSalesTaxRate($val['salesTaxRate']);
            $addresses->setAddressType('shipping');
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

    private function getFnameById($userid) {
        if (!empty($userid)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $userid));
            return $profileObj->getFname();
        }
    }

    private function getCompanyById($userid) {
        $profileObj = $this->getDoctrine()
            ->getRepository('AppBundle:Profile')
            ->findOneBy(array('userId' => $userid));
        return $profileObj->getCompany();
    }

    private function getCreatedDateById($userid) {
        $profileObj = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneById($userid);
        return $profileObj->getCreatedAt()->format('m/d/Y');
    }

    private function getCustomerData($userId) {
        if (!empty($userId)) {
            $user = array();
            $userData = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('id'=>$userId,'userType'=>'customer'));
            if (empty($userData)) {
                return false;
            } else {
                $user['id'] = $userData->getId();
                $user['username'] = $userData->getUsername();
                $user['roleId'] = $userData->getRoleId();
                $user['isActive'] = $userData->getIsActive();
                $user['userType'] = $userData->getUserType();
                $profileData = $this->getDoctrine()->getRepository('AppBundle:Profile')->findOneBy(array('userId' => $userId));
                if (!empty($profileData)) {
                    $user['company'] = $profileData->getCompany();
                    $user['fName'] = $profileData->getFname();
                    $user['email'] = $profileData->getEmail();
                    $user['phone'] = $profileData->getPhone();
                }
                $customerProfileData = $this->getDoctrine()->getRepository('AppBundle:CustomerProfiles')->findOneBy(array('userId' => $userId));
                if (!empty($customerProfileData)) {
                    $user['term'] = $customerProfileData->getTermId();
                    $user['comment'] = $customerProfileData->getComment();
                }
                $bAdd = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findOneBy(array('userId' => $userId,'addressType'=>'billing'));
                if (!empty($bAdd)) {
                    $user['bStreet'] = $bAdd->getStreet();
                    $user['billingState'] = $bAdd->getStateId();
                    $user['bCity'] = $bAdd->getCity();
                    $user['bZip'] = $bAdd->getZip();
                }
                $add = $this->getDoctrine()->getRepository('AppBundle:Addresses')->findBy(array('userId' => $userId,'addressType'=>'shipping'));
                if (!empty($add)) {
                    for ($i=0; $i < count($add); $i++) {
                        //$user['shipp'][$i]['id'] = $add[$i]->getId();
                        $user['shipp'][$i]['nickname'] = $add[$i]->getNickname();
                        $user['shipp'][$i]['street'] = $add[$i]->getStreet();
                        $user['shipp'][$i]['city'] = $add[$i]->getCity();
                        $user['shipp'][$i]['state'] = $add[$i]->getStateId();
                        $user['shipp'][$i]['zip'] = $add[$i]->getZip();
                        $user['shipp'][$i]['deliveryCharge'] = $add[$i]->getDeliveryCharge();
                        $user['shipp'][$i]['salesTaxRate'] = $add[$i]->getSalesTaxRate();
                        //$user['shipp'][$i]['addressType'] = $add[$i]->getAddressType();
                        //$user['shipp'][$i]['status'] = $add[$i]->getStatus();
                        //$user['shipp'][$i]['userId'] = $add[$i]->getUserId();
                    }
                }
                $discounts = $this->getDoctrine()->getRepository('AppBundle:Discounts')->findBy(array('userId' => $userId));
                if (!empty($discounts)) {
                    for ($i=0; $i< count($discounts); $i++) {
                        //$user['discount'][$i]['id'] = $discounts[$i]->getId();
                        //$user['discount'][$i]['userId'] = $discounts[$i]->getUserId();
                        $user['products'][$i]['products'] = $discounts[$i]->getProductName();
                        //$user['discount'][$i]['rate'] = $discounts[$i]->getRate();
                        //$user['discount'][$i]['status'] = $discounts[$i]->getStatus();
                    }
                }
                return $user;
            }
        }
    }

    private function checkIfOthrUsrHasThisPhone($phone,$userId){
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select * from profiles WHERE user_id != :userid AND phone = :phone";
        $stmt=$conn->prepare($SQL);
        $stmt->bindParam(':userid',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':phone',$phone,PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    private function getAddressIdByUserId($addType, $userId) {
        $address = $this->getDoctrine()
            ->getRepository('AppBundle:Addresses')
            ->findOneBy(array('userId' => $userId,'addressType'=>$addType));
        if ( !empty($address) ) {
            $addressId = $address->getId();
        } else {
            $addressId = null;
        }
        return $addressId;
    }

    private function getCustomerIdsByName($custName) {
        $data = $this->getDoctrine()->getRepository('AppBundle:Profile')->findBy(array('fname' => $custName));
        $ids = array();
        foreach ($data as $d) {
            $ids[] = $d->getUserId();
        }
        return $ids;
    }

    private function getSortedCustomerIds($custName, $sortBy, $order) {
        $data = $this->getDoctrine()->getRepository('AppBundle:Profile')->findBy(array('fname' => $custName), array($sortBy => $order));
        $ids = array();
        foreach ($data as $d) {
            $ids[] = $d->getUserId();
        }
        return $ids;
    }

    private function getCustomersIdsOnSortedData($sortBy, $order) {
        $data = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'), array('id' => 'desc'),5);
        $ids = array();
        $cIds = array();
        foreach ($data as $d) {
            $ids[] = $d->getId();
        }
        //print_r($ids);die;
        $pData = $this->getDoctrine()->getRepository('AppBundle:Profile')->findBy(array('userId' => $ids), array($sortBy => $order));
        foreach ($pData as $pD) {
            $cIds[] = $pD->getUserId();
        }
        return $cIds;
    }
}