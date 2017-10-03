<?php
/**
 * Created by PhpStorm.
 * User: d-14
 * Date: 3/10/17
 * Time: 2:17 PM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Files;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileuploadController extends Controller
{
    /**
     * @Route("/api/fileupload")
     * @param Request $request
     * @Method("POST")
     */
  public function fileUploadAction(Request $request){
      $arrApi = array();
      $statusCode = 200;

      $em = $this->getDoctrine()->getManager();
      $allowedExtension = $this->container->getParameter('allowed_extensions');
      $uploadDirectory = $this->container->getParameter('upload_file_destination');
    
      $file = $request->files->get('file');
      $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
      $filesize = $file->getSize();

      if($filesize > 5000000 ){
          $arrApi['message'] = "File too large, you can upload files up to 5 MB";
          $statusCode = 400;
      }
      else if(!in_array($ext,$allowedExtension)){
          $arrApi['message'] = "File not allowed";
          $statusCode = 400;
      }else{
          $fileName = md5(uniqid()).'.'.$ext;
          try{
              if($file->move($uploadDirectory,$fileName)){
                  $fileEntity = new Files();
                  $fileEntity->setFileName($fileName);
                  $em->persist($fileEntity);
                  $em->flush();
                  $fileId = $fileEntity->getId();
                  $statusCode = 200;
                  $arrApi['status'] = 1;
                  $arrApi['message'] = "Successfully Uploaded";
                  $arrApi['data']['fileId'] = $fileId;

              }

          }catch(Exception $e){
              $arrApi['error']= $e;
          }
      }
      return new JsonResponse($arrApi,$statusCode);
  }
}