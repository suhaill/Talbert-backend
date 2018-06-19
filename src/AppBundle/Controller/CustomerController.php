<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Addresses;
use AppBundle\Entity\CustomerProfiles;
use AppBundle\Entity\Discounts;
use AppBundle\Service\JsonToArrayGenerator;
use function GuzzleHttp\Promise\is_fulfilled;
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
use AppBundle\Entity\CustomerContacts;
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
            $contactInfo = $data->get('contacts');
            $company = trim($data->get('company'));
//            $fName = !empty(trim($contactInfo[0]['fname']))?trim($contactInfo[0]['fname']):'';
            $passwd = password_hash('123456', PASSWORD_DEFAULT);
            $roleId = '11';
            $isActive = $data->get('is_active');
            $usrType = 'customer';
//            $phone = !empty(trim($contactInfo[0]['phone']))?trim($contactInfo[0]['phone']):'';
            $email = !empty(trim($contactInfo[0]['email']))?trim($contactInfo[0]['email']):'';
            $usrname = $email;
            $trmId = $data->get('term');
            $comment = trim($data->get('comment'));
            $bStreet = trim($data->get('billingStreet'));
            $bCity = trim($data->get('billingCity'));
            $bState = $data->get('billingState');
            $bZip = trim($data->get('billingZip'));
            $prdArr = $data->get('products');
            $shipArr = $data->get('shipp');
            $is_checked = $data->get('is_checked');
            $isName=false;
            $isPhone=false;
            $isEmail=false;
            $isPhoneExist=false;
            $isEmailExist=false;
            if(!empty($contactInfo)){
                for($i=0;$i<count($contactInfo);$i++){
                    if(empty(trim($contactInfo[$i]['fname']))){
                        $isName=true;
                    }
                    if(is_numeric(trim($contactInfo[$i]['phone'])) && strlen(trim($contactInfo[$i]['phone'])) > 10){
                        $isPhone=true;
                    } else {
                        if($this->checkIfPhoneExists($contactInfo[$i]['phone'])==true){
                            $isPhoneExist=true;
                        }
                    }
                    if(!filter_var(trim($contactInfo[$i]['email']),FILTER_VALIDATE_EMAIL )){
                        $isEmail=true;
                    } else {
                        if($this->checkIfEmailExists($contactInfo[$i]['email'])==true){
                            $isEmailExist=true;
                        }
                    }
                }
            }
            $datime = new \DateTime('now');
            $isValidShipAdd = $this->validateShippingAddress($shipArr);
            if ( empty($currLoggedInUserRoleId) || $currLoggedInUserRoleId != '1') {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'You are not allowed to add customers';
            } else {
                if ( empty($company) || $isActive > 1 || empty ($trmId) ||
                        empty ($bStreet) || empty ($bCity) || empty($bState) || empty ($bZip)  || empty ($shipArr) || 
                        empty (trim($shipArr[0]['nickname'])) || empty (trim($shipArr[0]['street'])) || 
                        empty (trim($shipArr[0]['city'])) || empty (trim($shipArr[0]['state'])) || 
                        empty (trim($shipArr[0]['zip'])) || empty (trim($shipArr[0]['deliveryCharge'])) || 
                        empty (trim($shipArr[0]['salesTaxRate'])) || empty($isValidShipAdd) || $isName==true 
                ) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Please fill all required fields';
                } else {
                    if ( $isPhone==true) {
                        $arrApi['status'] = 0;
                        $arrApi['message'] = 'Please enter correct phone number';
                    } else {
                        if ( $isEmail==true) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Invalid email address';
                        } else {
//                            $phoneCount = $this->checkIfPhoneExists($phone);
                            if ($isPhoneExist) {
                                $arrApi['status'] = 0;
                                $arrApi['message'] = 'Entered Phone number already exists.';
                            } else {
//                                $emailCount = $this->checkIfEmailExists($email);
                                if ($isEmailExist) {
                                    $arrApi['status'] = 0;
                                    $arrApi['message'] = 'Entered Email already exists.';
                                } else {
                                    $lastUserId = $this->saveCustomerData($usrname, $passwd, $roleId, $isActive, $datime, 
                                            $usrType);
                                    if (empty($lastUserId)) {
                                        $arrApi['status'] = 0;
                                        $arrApi['message'] = 'Error while adding customer.';
                                    } else {
                                        $arrApi['status'] = 1;
                                        $arrApi['message'] = 'Customer Added Successfully.';
                                        $lstPrfId = $this->saveProfileData($lastUserId, $company);
                                        if (empty($lstPrfId)) {
                                            $arrApi['profileMessage'] = 'Profile Data not saved.';
                                        } else {
                                            $this->saveCustomerProfile($trmId, $comment, $lastUserId,$is_checked);
                                            $this->saveBillingAddress($bStreet, $bCity, $bState, $bZip, $lastUserId, $datime);
                                            $this->saveShippingAddress($shipArr, $lastUserId, $datime);
                                            $this->saveDiscountData($prdArr, $lastUserId);
                                            $this->saveCustomerContact($contactInfo,$lastUserId);
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
//            $users = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'),array('id' => 'DESC'),5);
            $query = $this->getDoctrine()->getManager();
            $users = $query->createQueryBuilder()
                ->select(['u.id','p.company','u.createdAt','c.contactName as fname'])
                ->from('AppBundle:User', 'u')
                ->innerJoin('AppBundle:CustomerContacts', 'c', 'WITH', 'c.userId=u.id')
                ->innerJoin('AppBundle:Profile', 'p', 'WITH', 'p.userId=u.id')
                ->where("u.userType = 'customer'")
                ->orderBy('u.id', 'DESC')
                ->groupBy('c.userId')
                ->getQuery()
                ->getResult();
            if ( empty($users) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no user.';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the customers list.';
                foreach ($users as $v) {
                    $arrApi['data']['customers'][]=[
                        'id'=>$v['id'],
                        'fname'=>$v['fname'],
                        'comapny'=>$v['company'],
                        'createdDate'=>$v['createdAt']->format('m/d/Y')
                    ];
                }
//                for ($i=0; $i<count($users); $i++) {
//                    $userId = $users[$i]->getId();
//                    if (!empty($userId)) {
//                        $arrApi['data']['customers'][$i]['id'] = $users[$i]->getId();
//                        $arrApi['data']['customers'][$i]['fname'] = $this->getFnameById($userId);
//                        $arrApi['data']['customers'][$i]['comapny'] = $this->getCompanyById($userId);
//                        $arrApi['data']['customers'][$i]['createdDate'] = $this->getCreatedDateById($userId);
//                    }
//                }
            }
            return new JsonResponse($arrApi);
        }
    }

    /**
     * @Route("/api/customer/getAllCustomersList")
     * @Security("is_granted('ROLE_USER')")
     * @Method("GET")
     */
    public function getAllCustomersAction(Request $request){
        if ($request->getMethod() == 'GET') {
            $arrApi = [];
            /*$users = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'),array('id' => 'DESC'));
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
            }*/
            $query = $this->getDoctrine()->getManager();
            $users = $query->createQueryBuilder()
                ->select(['u.id','u.createdAt'])
                ->from('AppBundle:User', 'u')
                ->leftJoin('AppBundle:Profile', 'p', 'WITH', "u.id = p.userId")
                ->addSelect(['p.company as comapny',"p.fname",'p.lname'])
                ->where('p.company is not null')
                ->orderBy('p.company','ASC')
                ->getQuery()
                ->getResult();
            if ( empty($users) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no user.';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the customers list.';
                for ($i=0; $i<count($users); $i++) {
//                    $userId = $users[$i]->getId();
                    $arrApi['data']['customers'][$i]['id'] = $users[$i]['id'];
                    $arrApi['data']['customers'][$i]['fname'] = $users[$i]['fname'].(!empty($users[$i]['lname'])?' '.$users[$i]['lname']:'');
                    $arrApi['data']['customers'][$i]['comapny'] = $users[$i]['comapny'];
                    $arrApi['data']['customers'][$i]['createdDate'] = $users[$i]['createdAt']->format('m/d/Y');
                }
            }
//        print_r($result);die;
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
                
                $contactInfo = $data->get('contacts');
                $userId = $data->get('customer_id');
                $currLoggedInUserId = $data->get('current_user_id');
                $currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);
                $company = trim($data->get('company'));
//                $fName = !empty(trim($contactInfo[0]['fname']))?trim($contactInfo[0]['fname']):'';
                $isActive = $data->get('is_active');
//                $phone = !empty(trim($contactInfo[0]['phone']))?trim($contactInfo[0]['phone']):'';
                $trmId = $data->get('term');
                $comment = trim($data->get('comment'));
                $bStreet = trim($data->get('billingStreet'));
                $bCity = trim($data->get('billingCity'));
                $bState = $data->get('billingState');
                $bZip = trim($data->get('billingZip'));
                $prdArr = $data->get('products');
                $shipArr = $data->get('shipp');
                $datime = new \DateTime('now');
                $is_checked = $data->get('is_checked');
                $isValidShipAdd = $this->validateShippingAddress($shipArr);
                $isName=false;
                $isPhone=false;
                $isEmail=false;
                $isPhoneExist=false;
                $isEmailExist=false;
                if(!empty($contactInfo)){
                    for($i=0;$i<count($contactInfo);$i++){
                        if(empty(trim($contactInfo[$i]['fname']))){
                            $isName=true;
                        }
                        if(is_numeric(trim($contactInfo[$i]['phone'])) && strlen(trim($contactInfo[$i]['phone'])) > 10){
                            $isPhone=true;
                        } else {
                            if($this->checkIfOthrUsrHasThisPhone($contactInfo[$i]['phone'], $userId)==true){
                                $isPhoneExist=true;
                            }
                        }
                        if(!filter_var(trim($contactInfo[$i]['email']),FILTER_VALIDATE_EMAIL )){
                            $isEmail=true;
                        } else {
                            if($this->checkIfOthrUsrHasThisEmail($contactInfo[$i]['email'], $userId)==true){
                                $isEmailExist=true;
                            }
                        }
                    }
                }
                
                if ( empty($currLoggedInUserRoleId) || $currLoggedInUserRoleId != '1') {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'You are not allowed to update customers';
                    $statusCode = 422;
                } else {
                    if ( empty($company) || $isActive > 1 || empty ($trmId) || 
                            empty ($bStreet) || empty ($bCity) || empty($bState) || empty ($bZip) || empty($isValidShipAdd) 
                            || $isName==true 
                    ) {
                        $arrApi['status'] = 0;
                        $arrApi['message'] = 'Please fill all required fields';
                    } else {
                        if ($isEmail==true) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Invalid email address';
                        } else if ($isEmailExist) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Entered Email already exists.';
                        } else if ($isPhone==true) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Please enter correct phone number';
                        } else {
//                            $phoneCount = $this->checkIfOthrUsrHasThisPhone($phone, $userId);
                            if ($isPhoneExist) {
                                $arrApi['status'] = 0;
                                $arrApi['message'] = 'Entered phone number already exists.';

                            } else {
                                $arrApi['status'] = 1;
                                $arrApi['message'] = 'Successfully updated customer data.';
                                $this->updateUserRecord($isActive, $company, $datime, $userId);
                                $this->updateBillingAddress($bStreet, $bCity, $bState, $bZip, $datime, $userId);
                                $this->updateShippingAddress($shipArr, $userId, $datime);
                                $this->updateDiscountData($prdArr, $userId);
                                $this->updateCustomerprofileData($trmId, $comment, $userId,$is_checked);
                                $this->saveCustomerContact($contactInfo,$userId,'edit');
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
                    $custIds = $this->getCustomerIdsByNameLike($custName);
                    $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer','id' => $custIds),array('id' => 'DESC'), $limit, $offset);
                } else if (empty($custName) && !empty($sortBy) && !empty($order)) {
                    if ($sortBy == 'id' || $sortBy == 'createdAt') {
                        $custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer'),array($sortBy => $order), $limit, $offset);
                    } else {
                        $arrApi['data']['customers'] =  $this->getCustomersIdsOnSortedData($sortBy, $order);
                        return new JsonResponse($arrApi, $statusCode);
                        die;
                        //$custData = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(array('userType' => 'customer','id' => $custIds));
                    }
                } else if (!empty($custName) && !empty($sortBy) && !empty($order)) {
                    if ($sortBy == 'id' || $sortBy == 'createdAt') {
                        $custIds = $this->getCustomerIdsByNameLike($custName);
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

    /**
     * @Route("/api/customer/importCustomers")
     * @Security("is_granted('ROLE_USER')")
     * @Method("POST")
     */
    public function importCustomersAction(Request $request) {
        $arrApi = array();
        $statusCode = 200;
        try {
            $jsontoarraygenerator = new JsonToArrayGenerator();
            $data = $jsontoarraygenerator->getJson($request);
            $passwd = password_hash('123456', PASSWORD_DEFAULT);
            $datime = new \DateTime('now');
            $isDataCorrect = $this->checkIfAllRequiredDataIsThere($data);
            if ($isDataCorrect == 2 ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Please fill all the details';
            } elseif ($isDataCorrect == 3 ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Unique username is required';
            } elseif ($isDataCorrect == 4 ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Unique email is required';
            } elseif ($isDataCorrect == 5 ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Unique phone is required';
            } else {
                if (!empty($data)) {
                    for ($i=0;$i<count($data); $i++) {
                        $comment = (empty($data[$i]['Note'])) ? '' : $data[$i]['Note'];
                        $lastUserId = $this->saveCustomerData($data[$i]['Contact'], $passwd, 11, 1, $datime, 'customer');
                        $lstPrfId = $this->saveProfileData($lastUserId, $data[$i]['Customer'], $data[$i]['Contact'], $data[$i]['Email'], $data[$i]['Phone']);
                        $this->saveCustomerProfile($data[$i]['Term'], $comment, $lastUserId,0);
                        $this->saveBillingAddress($data[$i]['Street1'], $data[$i]['City'], $data[$i]['State'], $data[$i]['Zip'], $lastUserId, $datime);
                        $this->saveShipAdd($data[$i]['Nickname'], $data[$i]['StreetShip'], $data[$i]['CityShip'], $data[$i]['StateShip'], $data[$i]['ZipShip'], $data[$i]['DelChrgShip'], $data[$i]['SlsTxRtShip'], $lastUserId, $datime);
                        $this->saveDisDataImp($data[$i]['Dis'], $lastUserId);
                    }
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully imported';
                } else {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'File is empty';
                    $statusCode = 422;
                }
            }
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }
        return new JsonResponse($arrApi, $statusCode);
    }
    // Reusable methods

    private function checkIfAllRequiredDataIsThere($data) {
        //print_r($data);die;
        for ($i = 0; $i < count($data); $i++) {
            if ( empty($data[$i]['Customer']) || empty($data[$i]['Contact']) || empty($data[$i]['Phone']) || empty($data[$i]['Email']) || empty($data[$i]['Term']) || empty($data[$i]['Street1']) || empty($data[$i]['City']) || empty($data[$i]['State']) || empty($data[$i]['Zip']) || empty($data[$i]['Nickname']) || empty($data[$i]['StreetShip']) || empty($data[$i]['CityShip']) || empty($data[$i]['StateShip']) || empty($data[$i]['ZipShip']) || empty($data[$i]['DelChrgShip']) || empty($data[$i]['SlsTxRtShip']) || empty($data[$i]['Dis']) ) {
                return 2;
            } else if ($this->checkIfUsernameExists($data[$i]['Contact'])) {
                return 3;
            } else if ($this->checkIfEmailExists($data[$i]['Email'])) {
                return 4;
            } else if ($this->checkIfPhoneExists($data[$i]['Phone'])) {
                return 5;
            }
        }
        return 1;
    }

    private function checkIfUsernameExists($username) {
        $usernameData = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(array('username' => $username));
        if (count($usernameData) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    private function saveShipAdd($nickname, $street, $city, $state, $zip, $deliveryCharge, $salestaxRate, $lastUserId, $datime) {
        $em = $this->getDoctrine()->getManager();
        $addresses = new Addresses();
        $addresses->setNickname($nickname);
        $addresses->setStreet($street);
        $addresses->setCity($city);
        $addresses->setStateId($state);
        $addresses->setZip($zip);
        $addresses->setDeliveryCharge($deliveryCharge);
        $addresses->setSalesTaxRate($salestaxRate);
        $addresses->setAddressType('shipping');
        $addresses->setStatus(1);
        $addresses->setUserId($lastUserId);
        $addresses->setUpdatedAt($datime);
        $em->persist($addresses);
        $em->flush();
    }

    private function saveDisDataImp($prdName, $lastUserId) {
        $rate = 10;
        $sts = 1;
        $em = $this->getDoctrine()->getManager();
        $dis = new Discounts();
        $dis->setProductName($prdName);
        $dis->setUserId($lastUserId);
        $dis->setRate($rate);
        $dis->setStatus($sts);
        $em->persist($dis);
        $em->flush();
    }

    private function updateUserRecord($isActive, $company, $datime, $userId) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($userId);
        $user->setIsActive($isActive);
        $user->setUpdatedAt($datime);
        $em->persist($user);
        // Profile table update
        $profileId = $this->getProfileIdByUserId($userId);
        $profile = $em->getRepository(Profile::class)->find($profileId);
        $profile->setCompany($company);
//        $profile->setFname($fName);
//        $profile->setPhone($phone);
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
                $addresses->setNickname(trim($val['nickname']));
                $addresses->setStreet(trim($val['street']));
                $addresses->setCity(trim($val['city']));
                $addresses->setStateId(trim($val['state']));
                $addresses->setZip(trim($val['zip']));
                $addresses->setDeliveryCharge($this->formateDeliveryCharge(trim($val['deliveryCharge'])));
                $addresses->setSalesTaxRate($this->formateSalestax(trim($val['salesTaxRate'])));
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
        if (!empty($prdArr)) {
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
    }

    private function updateCustomerprofileData($trmId, $comment, $userId,$is_checked) {
        $em = $this->getDoctrine()->getManager();
        $custProfileId = $this->getCustProfIdByUserId($userId);
        if (!empty($custProfileId)) {
            $custProfile = $em->getRepository(CustomerProfiles::class)->find($custProfileId);
            $custProfile->setTermId($trmId);
            $custProfile->setComment($comment);
            $custProfile->setIsChecked($is_checked);
            $em->persist($custProfile);
        } else {
            $customerProfile = new CustomerProfiles();
            $customerProfile->setTermId($trmId);
            $customerProfile->setComment($comment);
            $customerProfile->setUserId($userId);
            $custProfile->setIsChecked($is_checked);
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
            $addresses->setNickname(trim($val['nickname']));
            $addresses->setStreet(trim($val['street']));
            $addresses->setCity(trim($val['city']));
            $addresses->setStateId(trim($val['state']));
            $addresses->setZip(trim($val['zip']));
            $addresses->setDeliveryCharge($this->formateDeliveryCharge(trim($val['deliveryCharge'])));
            $addresses->setSalesTaxRate($this->formateSalestax(trim($val['salesTaxRate'])));
            $addresses->setAddressType('shipping');
            $addresses->setStatus(1);
            $addresses->setUserId($lastUserId);
            $addresses->setUpdatedAt($datime);
            $em->persist($addresses);
            $em->flush();
        }
    }

    private function formateDeliveryCharge($dc) {
        $arrExp = array('$',',');
        $arrRep = array('','');
        return str_replace($arrExp, $arrRep, $dc);
    }

    private function formateSalestax($st) {
        return str_replace('%', '', $st);
    }

    private function saveDiscountData($prdArr, $lastUserId) {
        $rate = 10;
        $sts = 1;
        if (!empty($prdArr)) {
            $em = $this->getDoctrine()->getManager();
            foreach ($prdArr as $val) {
                $dis = new Discounts();
                $dis->setProductName(trim($val['products']));
                $dis->setUserId($lastUserId);
                $dis->setRate($rate);
                $dis->setStatus($sts);
                $em->persist($dis);
                $em->flush();
            }
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
        $user->setIsSalesman(0);
        $em->persist($user);
        $em->flush();
        return $user->getId();
    }

    private function saveProfileData($lastUserId, $company) {
        $em = $this->getDoctrine()->getManager();
        $profile = new Profile();
        $profile->setUserId($lastUserId);
        $profile->setCompany($company);
//        $profile->setFname($fName);
//        $profile->setEmail($email);
//        $profile->setPhone($phone);
        $em->persist($profile);
        $em->flush();
        return $profile->getId();
    }

    private function saveCustomerProfile($trmId, $comment, $lastUserId,$is_checked) {
        $em = $this->getDoctrine()->getManager();
        $custProf = new CustomerProfiles();
        $custProf->setTermId($trmId);
        $custProf->setComment($comment);
        $custProf->setUserId($lastUserId);
        $custProf->setIsChecked($is_checked);
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
            ->getRepository('AppBundle:CustomerContacts')
            ->findOneBy(array('email' => $email));
        if (count($emailData) > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    private function checkIfPhoneExists($phone) {
        $phoneData = $this->getDoctrine()
            ->getRepository('AppBundle:CustomerContacts')
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
//                    $user['fName'] = $profileData->getFname();
//                    $user['email'] = $profileData->getEmail();
//                    $user['phone'] = $profileData->getPhone();
                }
                $customerProfileData = $this->getDoctrine()->getRepository('AppBundle:CustomerProfiles')->findOneBy(array('userId' => $userId));
                if (!empty($customerProfileData)) {
                    $user['term'] = $customerProfileData->getTermId();
                    $user['comment'] = $customerProfileData->getComment();
                    $user['is_checked'] = $customerProfileData->getIsChecked();
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
                $contact = $this->getDoctrine()->getRepository('AppBundle:CustomerContacts')->findBy(['userId' => $userId]);
                $user['fname'] = $contact[0]->getContactName();
                $user['email'] = $contact[0]->getEmail();
                $user['phone'] = $contact[0]->getPhone();
                if(!empty($contact)){
                    $j=0;
                    foreach ($contact as $v) {
                        $user['contacts'][$j]['fname'] = $v->getContactName();
                        $user['contacts'][$j]['email'] = $v->getEmail();
                        $user['contacts'][$j]['phone'] = $v->getPhone();
                        $j++;
                    }
                }                
                return $user;
            }
        }
    }

    private function checkIfOthrUsrHasThisPhone($phone,$userId){
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select * from customer_contacts WHERE user_id != :userid AND phone = :phone";
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
    
    private function checkIfOthrUsrHasThisEmail($email,$userId){
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select * from customer_contacts WHERE user_id != :userid AND email = :email";
        $stmt=$conn->prepare($SQL);
        $stmt->bindParam(':userid',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':email',$email,PDO::PARAM_STR);
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

    private function getCustomerIdsByNameLike($custName) {
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select user_id from profiles WHERE fname LIKE '$custName%'";
        $stmt=$conn->prepare($SQL);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $data = array();
        $i=0;
        foreach ($result as $pD) {
            $data[] = $pD['user_id'];
            $i++;
        }
        return $data;
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
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select u.id, p.fname, p.company, u.created_at from users u inner join profiles p WHERE u.id = p.user_id AND u.user_type='customer' order by p.$sortBy $order limit 5";
        $stmt=$conn->prepare($SQL);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $data = array();
        $i=0;
        foreach ($result as $pD) {
            $data[$i]['id'] = $pD['id'];
            $data[$i]['fname'] = $pD['fname'];
            $data[$i]['comapny'] = $pD['company'];
            $data[$i]['createdDate'] = $this->formateDate($pD['created_at']);
            $i++;
        }
        return $data;
//        $cIds = array();
//        foreach ($result as $pD) {
//            $cIds[] = $pD['id'];
//        }
//        return $cIds;
    }
    private function formateDate($date) {
        $expDate = explode(' ', $date)[0];
        $expDate1 = explode('-',$expDate);
        return $expDate1[1].'/'.$expDate1[2].'/'.$expDate1[0];
    }

    private function validateShippingAddress($shipArr) {
        $res = true;
        if (!empty($shipArr)) {
            for ($i=0;$i<count($shipArr);$i++) {
                if ( empty (trim($shipArr[$i]['nickname'])) || empty (trim($shipArr[$i]['street'])) || empty (trim($shipArr[$i]['city'])) || empty (trim($shipArr[$i]['state'])) || empty (trim($shipArr[$i]['zip'])) || empty (trim($shipArr[$i]['deliveryCharge'])) || empty (trim($shipArr[$i]['salesTaxRate'])) ) {
                    $res = false;
                }
            }
        }
        return $res;
    }
    
    private function saveCustomerContact($contactInfo,$lastUserId,$type=''){
        if(!empty($contactInfo)){
            $em = $this->getDoctrine()->getManager();
            if(!empty($type) && $type == 'edit'){
                $conn = $this->getDoctrine()->getConnection('default');
                $SQL="delete from customer_contacts WHERE user_id = :userid";
                $stmt=$conn->prepare($SQL);
                $stmt->bindParam(':userid',$lastUserId,PDO::PARAM_INT);
                $stmt->execute();
            }
            for ($i=0;$i<count($contactInfo);$i++){
                $a = new CustomerContacts();
                $a->setContactName($contactInfo[$i]['fname']);
                $a->setEmail($contactInfo[$i]['email']);
                $a->setPhone($contactInfo[$i]['phone']);
                $a->setUserId($lastUserId);
                $em->persist($a);
                $em->flush();
            }
        }
    }
}