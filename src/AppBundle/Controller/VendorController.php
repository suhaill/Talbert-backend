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
        $fname = $this->split_name($fname);

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

    public function split_name($name) {
        $name = trim($name);
        $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
        return array($first_name, $last_name);
    }

}