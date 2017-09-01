<?php

namespace AppBundle\Controller;
use AppBundle\Service\JsonToArrayGenerator;
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


class ForgotController extends Controller
{
    
    /**
     * @Route("/api/user/forgot")
     * parameters: email
     * mandatory: yes
     * @Method({"POST"})
     */
    public function forgotAction(Request $request) {


        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();


        if ($request->getMethod() == 'POST') {
            $getJson = $jsontoarraygenerator->getJson($request);
            $email = trim($getJson->get('email'));


            if (empty($email) || $email == "" || !filter_var($email,FILTER_VALIDATE_EMAIL)) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Invalid Email';
                $statusCode = 422;
            } else {




                $em = $this->getDoctrine()->getManager();


                $user = $this->getDoctrine()->getRepository('AppBundle:Profile')->findOneBy(['email'=>$email]);

                if(!$user || is_null($user)){
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Record not found in our database';
                    $statusCode = 404;
                }else{
                    $userid = $user->getUserId();
                    $userEntity = $this->getDoctrine()->getRepository('AppBundle:User')->find($userid);
                    $token = openssl_random_pseudo_bytes(12);
                    $token = substr(str_replace(array('/', '+', '='), '', base64_encode($token)), 0, 12);

                    $userEntity->setToken($token);
                    $em->flush();

                    $servername = $this->getParameter('serverurl');
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Reset Password Request for Talbert')
                        ->setFrom('admin@talbert.com')
                        ->setTo($user->getEmail())
                        ->setBody(
                            $this->render(
                                'Emails/resetpassword.html.twig',
                                [
                                    "firstname"=>$user->getFname(),
                                    "lastname" => $user->getLname(),
                                    "servername" => $servername,
                                    "token" => $token
                                ]
                            ),
                            'text/html'
                        )
                    ;
                    $this->get('mailer')->send($message);
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Email has sent';
                    $statusCode = 200;

                }






            }

            $response = new JsonResponse($arrApi,$statusCode);
            return $response;
        }

        $arrApi['status'] = 0;
        $arrApi['message'] = 'Method not Allowed';
        $statusCode = 405;
        $response = new JsonResponse($arrApi    );
        return $response;

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @Route("/api/user/reset",name="resetpassword")
     * @Method({"POST"})
     */

    public function resetAction(Request $request) {


        $arrApi = array();
        $statusCode = 200;
        $jsontoarraygenerator = new JsonToArrayGenerator();


        if ($request->getMethod() == 'POST') {
            $getJson = $jsontoarraygenerator->getJson($request);
            $token = trim($getJson->get('token'));
            $newpassword = trim($getJson->get('newpassword'));
            $cnpassword = trim($getJson->get('cnpassword'));

             if(empty($token) || empty($newpassword) || empty($cnpassword)
                 || $token == "" || $newpassword == "" || $cnpassword == "" ||
                 is_null($token) || is_null($newpassword) || is_null($cnpassword)){
                 $arrApi['status'] = 0;
                 $arrApi['message'] = 'All fields required';
                 $statusCode = 422;


             }else {
                if($newpassword != $cnpassword){

                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Password and confirm password did not match';
                    $statusCode = 422;
                } else{

                $em = $this->getDoctrine()->getManager();
                $user = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(['token'=>$token]);

                if(!$user || is_null($user)){
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'Record not found in our database';
                    $statusCode = 422;
                }else{


                    $user->setPassword(password_hash($newpassword, PASSWORD_DEFAULT));
                    $em->flush();



                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Pasword updated';
                    $statusCode = 200;

                }
                 }





            }

            $response = new JsonResponse($arrApi,$statusCode);
            return $response;
        }

        $arrApi['status'] = 0;
        $arrApi['message'] = 'Method not Allowed';
        $statusCode = 405;
        $response = new JsonResponse($arrApi    );
        return $response;

    }







}