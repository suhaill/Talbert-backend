<?php

namespace AppBundle\Controller;
use AppBundle\Entity\VendorProfile;
use AppBundle\Service\JsonToArrayGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
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

class VendorController extends Controller
{


    /**
     * @Route("/api/vendor/add")
     * @Method({"POST"})
     * @Security("is_granted('ROLE_USER')")
     */
    public function addVendorAction(Request $request) {

        $arrApi = [];
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();
        $getJson = $jsontoarraygenerator->getJson($request);
        $username = trim($getJson->get('email'));
        $email    = trim($getJson->get('email'));
        $password = "vendor";
        $password = password_hash($password, PASSWORD_DEFAULT);
        $fname = $getJson->get('name');
        $fname = explode(" ",$fname);
        $fname = $fname[0];
        $lname = $fname[1];

        $phone = $getJson->get('phone');
        $street = $getJson->get('street');
        $city = $getJson->get('city');
        $state = $getJson->get('state');
        $zip = $getJson->get('zip');
        $term = $getJson->get('terms');
        $usertype = 'vendor';
        $comments = $getJson->get('comments');
        $roleId = 11;
        $isAct = 1;
        $cntId = 6;


        $userNameData = $this->checkIfUserNameExists($username);
        if ($userNameData) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'This Email already exists.';
            $statusCode = 303;
        } else if($this->checkIfEmailExists($email)){
            $arrApi['status'] = 0;
            $arrApi['message'] = 'This Email already exists.';
            $statusCode = 303;
        }else if($this->checkIfPhoneExists($phone)){
            $arrApi['status'] = 0;
            $arrApi['message'] = 'This Phone already exists.';
            $statusCode = 303;
        }else{
            try{
                $em = $this->getDoctrine()->getManager();
                $user = new User();
                $user->setUsername($username);
                $user->setPassword($password);
                $user->setRoleId($roleId);
                $user->setIsActive($isAct);
                $em->persist($user);
                $em->flush();
                $lastInsertId = $user->getId();
                if (empty($lastInsertId)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Error occured while inserting user data into database.';
                    $statusCode = 400;
                } else {
                    $profile = new Profile();
                    $profile->setUserId($lastInsertId);
                    //$profile->setCompany($company);
                    $profile->setFname($fname);
                    $profile->setLname($lname);
                    $profile->setEmail($email);
                    $profile->setPhone($phone);
                    $profile->setAddress($street);
                    $profile->setCountryId($cntId);
                    $profile->setStateId($state);
                    $profile->setCity($city);
                    $em->persist($profile);
                    $em->flush();
                    if (empty($profile->getId())) {
                        $arrApi['status'] = 0;
                        $arrApi['message'] = 'Error occured while inserting user profile data into database.';
                        $statusCode = 400;
                    } else {

                        $vendor = new VendorProfile();
                        $vendor->setTermId($term);
                        $vendor->setUserId($lastInsertId);
                        $vendor->setComment($comments);
                        $em->persist($vendor);
                        $em->flush();
                        if (empty($profile->getId())) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Error occured while inserting user profile data into database.';
                            $statusCode = 400;
                        }else{
                        $arrApi['status'] = 1;
                        $arrApi['message'] = 'User data inserted into database successfully.';
                            $statusCode = 200;
                        }
                    }
                }

            }catch (Exception $e){
                throw  $e;
            }
        }

        $response = new JsonResponse($arrApi,$statusCode);
        return $response;

    }



    /**
     * @Route("api/vendor/getvendors")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * parameters: None
     * url: http://localhost/Talbert/backend/web/app_dev.php/api/vendor/getvendors
     */

    public function getUsersListAction(Request $request) {
        $arrApi = [];
        $statusCode = 200;
            $vendor = $this->getDoctrine()->getRepository('AppBundle:VendorProfile')->getVendors();
        var_dump($vendor);
        die();

            if ( empty($users) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no vendor.';
                $statusCode = 204;

            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the vendor list.';
                $statusCode = 200;

                for ($i=0; $i<count($users); $i++) {
                    $userId = $users[$i]->getId();
                    if (!empty($userId)) {
                        $arrApi['data']['users'][$i]['id'] = $users[$i]->getId();
                        $arrApi['data']['users'][$i]['fname'] = $this->getFnameById($userId);
                        $arrApi['data']['users'][$i]['lname'] = $this->getLnameById($userId);
                        $arrApi['data']['users'][$i]['email'] = $this->getEmailById($userId);
                    }
                }
            }
            return new JsonResponse($arrApi,$statusCode);

    }

    /**
     * @Route("api/user/getUserDetailsAndEditUser")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     * parameters: In first case - user_id,role_id and in second case
     * mandatory: All
     * url: http://localhost/Talbert/backend/web/app_dev.php/api/user/getUserDetailsAndEditUser
     */

    public function getUserDetailsAndEditAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $_DATA = file_get_contents('php://input');
            $_DATA = json_decode($_DATA, true);
            $arrApi = array();
            $currLoggedInUserId = $_DATA['current_user_id'];
            $currLoggedInUserRoleId = $this->getRoleIdByUserId($currLoggedInUserId);
            if ( $currLoggedInUserRoleId != 1 ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no access.';
            } else {
                $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($_DATA['user_id']);
                if (empty($user)) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'This user does not exists.';
                } else {
                    if (count($_DATA) == 2 && array_key_exists('user_id', $_DATA) && array_key_exists('current_user_id', $_DATA)) {
                        if (empty($_DATA['user_id']) || empty($currLoggedInUserRoleId)) {
                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Parameter missing.';
                        } else {
                            $arrApi['status'] = 1;
                            $arrApi['message'] = 'Successfully retrerived the user details.';
                            $userId = $_DATA['user_id'];
                            $profileObj = $this->getProfileDataOfUser($userId);

                            $arrApi['data']['user']['id'] = $userId;
                            $arrApi['data']['user']['username'] = $user->getUsername();
                            $arrApi['data']['user']['role_id'] = $user->getRoleId();
                            $arrApi['data']['user']['is_active'] = $user->getIsActive();
                            //$arrApi['data']['user']['company'] = $profileObj->getCompany();
                            if (!empty($profileObj)) {
                                $arrApi['data']['user']['fname'] = $profileObj->getFname();
                                $arrApi['data']['user']['lname'] = $profileObj->getLname();
                                $arrApi['data']['user']['email'] = $profileObj->getEmail();
                                $arrApi['data']['user']['phone'] = $profileObj->getPhone();
                                $arrApi['data']['user']['address'] = $profileObj->getAddress();
                                $arrApi['data']['user']['country_id'] = $profileObj->getCountryId();
                                $arrApi['data']['user']['state_id'] = $profileObj->getStateId();
                                $arrApi['data']['user']['city'] = $profileObj->getCity();
                            }
                        }
                    } else {
                        if ( /* empty(trim($_DATA['company'])) ||*/ empty(trim($_DATA['fname'])) || empty(trim($_DATA['lname'])) ||
                            empty(trim($_DATA['email'])) || empty(trim($_DATA['phone'])) || empty(trim($_DATA['username'])) || $_DATA['is_active'] > 1 ||
                            empty(trim($_DATA['address'])) || empty($_DATA['country_id']) ||
                            empty($_DATA['state_id']) || empty(trim($_DATA['city'])) || empty($_DATA['role_id']) || empty($_DATA['user_id'])) {

                            $arrApi['status'] = 0;
                            $arrApi['message'] = 'Parameter missing.';
                        } else {
                            $userId = $_DATA['user_id'];
                            $profileObj = $this->getProfileDataOfUser($userId);
                            $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($userId);
                            //$company = $_DATA['company'];
                            $fname = $_DATA['fname'];
                            $lname = $_DATA['lname'];
                            $email = $_DATA['email'];
                            $phone = $_DATA['phone'];
                            $usrname = $_DATA['username'];
                            $roleId = $_DATA['role_id'];
                            $isAct = $_DATA['is_active'];
                            if(isset($_DATA['password'])){
                                $passwd = password_hash($_DATA['password'], PASSWORD_DEFAULT);
                            }
                            $addrs = $_DATA['address'];
                            $cntId = $_DATA['country_id'];
                            $stId = $_DATA['state_id'];
                            $city = $_DATA['city'];
                            $datime = new \DateTime('now');
                            if($profileObj->getEmail() != $email){
                                $emailCount = $this->checkIfOthrUsrHasThisEmail($email,$userId);
                            } else {
                                $emailCount = false;
                            }
                            if ($emailCount) {
                                $arrApi['status'] = 0;
                                $arrApi['message'] = 'This Email Address already exists in the database.';
                            } else {
                                if($profileObj->getPhone() != $phone){
                                    $phoneCount = $this->checkIfOthrUsrHasThisPhone($phone,$userId);
                                } else {
                                    $phoneCount = false;
                                }
                                if ($phoneCount) {
                                    $arrApi['status'] = 0;
                                    $arrApi['message'] = 'This Phone number already exists in the database.';
                                } else {
                                    if($user->getUsername() != $usrname){
                                        $userNameData = $this->checkIfOthrUsrHasThisUsername($usrname,$userId);
                                    }else{
                                        $usrNameData = false;
                                    }
                                    if ($phoneCount) {
                                        $arrApi['status'] = 0;
                                        $arrApi['message'] = 'This username already exists.';
                                    } else {
                                        // Update user table record
                                        $em = $this->getDoctrine()->getManager();
                                        $user = $em->getRepository(User::class)->find($userId);
                                        $user->setUsername($usrname);
                                        if ( !empty($passwd) ) {
                                            $user->setPassword($passwd);
                                        }
                                        $user->setRoleId($roleId);
                                        $user->setIsActive($isAct);
                                        $user->setUpdatedAt($datime);
                                        // Update profile table record
                                        $profileId = $this->getProfileIdByUserId($userId);
                                        $profile = $em->getRepository(Profile::class)->find($profileId);
                                        //$profile->setCompany($company);
                                        $profile->setFname($fname);
                                        $profile->setLname($lname);
                                        $profile->setEmail($email);
                                        $profile->setPhone($phone);
                                        $profile->setAddress($addrs);
                                        $profile->setCountryId($cntId);
                                        $profile->setStateId($stId);
                                        $profile->setCity($city);
                                        $em->flush();
                                        $arrApi['status'] = 1;
                                        $arrApi['message'] = 'Successfully updated user data.';
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
     * @Route("api/user/getUserName")
     * @Method("POST")
     * @Security("is_granted('ROLE_USER')")
     * parameters: user_id
     * mandatory: All
     * url: http://localhost/Talbert-backend/web/app_dev.php/api/user/getUserName
     */

    public function getUserNameAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $_DATA = file_get_contents('php://input');
            $_DATA = json_decode($_DATA, true);
            $arrApi = array();
            if ( empty($_DATA['user_id']) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Parameter missing.';
            } else {
                $user_id = $_DATA['user_id'];
                $username = $this->getUsernameByUserId($user_id);
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived username.';
                if (empty($username)) {
                    $arrApi['data']['user']['username'] = null;
                } else {
                    $arrApi['data']['user']['username'] = $username;
                    $arrApi['data']['user']['fname'] = $this->getFnameById($user_id);
                    $arrApi['data']['user']['lname'] = $this->getLnameById($user_id);
                }
            }
            return new JsonResponse($arrApi);
        }
    }


    // Reusable methods

    private function getRoleIdByUserId($currLoggedInUserId) {
        $loggedInUserData = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($currLoggedInUserId);
        if (empty($loggedInUserData)) {
            return null;
        } else {
            $roleId = $loggedInUserData->getRoleId();
            return $roleId;
        }
    }

    private function getUsernameByUserId($user_id) {
        $usrNameData = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneById($user_id);
        $username = $usrNameData->getUsername();
        if (empty($username)) {
            return null;
        } else {
            return $username;
        }
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

    private function checkIfOthrUsrHasThisUsername($usrname,$userId){
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select * from users WHERE id != :userid AND username = :username";
        $stmt=$conn->prepare($SQL);
        $stmt->bindParam(':userid',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':username',$usrname,PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0 ) {
            return true;
        } else {
            return false;
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

    private function checkIfOthrUsrHasThisEmail($email,$userId) {
        $conn = $this->getDoctrine()->getConnection('default');
        $SQL="select * from profiles WHERE user_id != :userid AND email = :emailid";
        $stmt=$conn->prepare($SQL);
        $stmt->bindParam(':userid',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':emailid',$email,PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if (count($result) > 0 ) {
            return true;
        } else {
            return false;
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

    private function checkIfUserNameExists($usrname) {
        $usrNameData = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findOneBy(array('username' => $usrname));
        if (count($usrNameData) > 0 ) {
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

    private function getLnameById($userid) {
        if (!empty($userid)) {
            $profileObj = $this->getDoctrine()
                ->getRepository('AppBundle:Profile')
                ->findOneBy(array('userId' => $userid));
            return $profileObj->getLname();
        }
    }

    private function getEmailById($userid) {
        $profileObj = $this->getDoctrine()
            ->getRepository('AppBundle:Profile')
            ->findOneBy(array('userId' => $userid));
        return $profileObj->getEmail();
    }


//    private function getEmailById($userid) {
//        $profileObj = $this->getDoctrine()
//            ->getRepository('AppBundle:Profile')
//            ->findOneBy(array('userId' => $userid));
//        return $profileObj->getEmail();
//    }
//

    private function getProfileDataOfUser($userId) {
        $profileData = $this->getDoctrine()
            ->getRepository('AppBundle:Profile')
            ->findOneBy(array('userId' => $userId));
        if (!empty($profileData)) {
            return $profileData;
        }

    }

}