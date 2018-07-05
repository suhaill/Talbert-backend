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

      $attachableId = $request->request->get('attachableId') ? $request->request->get('attachableId') : null;
      $attachableType = $request->request->get('attachableType');
      $originalname = $file->getClientOriginalName();
      
      $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
      $filesize = $file->getSize();

      if($filesize > 5000000 ){
          $arrApi['status'] = 0;
          $arrApi['message'] = "File too large, you can upload files up to 5 MB";
          $statusCode = 400;
      }
      else if(!in_array($ext,$allowedExtension)){
          $arrApi['status'] = 0;
          $arrApi['message'] = "File not allowed";
          $statusCode = 400;
      }else{
          $fileName = md5(uniqid()).'.'.$ext;
          try{
              if($file->move($uploadDirectory,$fileName)){
                  $fileEntity = new Files();
                  $fileEntity->setFileName($fileName);
                  $fileEntity->setAttachableId($attachableId);
                  $fileEntity->setAttachableType($attachableType);
                  $fileEntity->setOriginalName($originalname);
                  $em->persist($fileEntity);
                  $em->flush();
                  $fileId = $fileEntity->getId();
                  $arrApi['status'] = 1;
                  $arrApi['message'] = "Successfully Uploaded";
                  $arrApi['data']['fileId'] = $fileId;
                  $arrApi['data']['ext'] = $ext;
                  $arrApi['data']['originalname'] = $originalname;
              }
          }catch(Exception $e){
              $arrApi['error']= $e;
          }
      }
      return new JsonResponse($arrApi,$statusCode);
  }

  /**
     * @Route("api/fileDelete")
     * @Method("POST")
     */

     public function fileDeleteAction(Request $request) {
        $uploadDirectory = $this->container->getParameter('upload_file_destination');
        $_DATA = file_get_contents('php://input');
        $_DATA = json_decode($_DATA, true);
        $arrApi = array();
        $id = $_DATA['id'];
        //$type = $_DATA['type'];
        //$createdAt = new \DateTime('now');

        $em = $this->getDoctrine()->getManager();
        
        $file =  $this->getDoctrine()->getRepository('AppBundle:Files')->find($id);

        try{
            $fileName = $file->getFileName();
            $em->remove($file);
            $em->flush();
            unlink($uploadDirectory.'/'.$fileName);
        }
        catch(Exception $e) {
            throw $e->getMessage();
        }

        $statusCode = 200;
        $arrApi['status'] = 1;
        $arrApi['message'] = 'File deleted Successfully.';
            
        return new JsonResponse($arrApi,$statusCode);
    }

    /**
    * @Route("/api/fileDownload/{fileId}")
    * @Method("GET")
    */

    public function fileDownloadAction($fileId,Request $request) {

        $files = $this->getDoctrine()->getRepository('AppBundle:Files')->findOneById($fileId);
        
        //var_dump($files);
        
        //$baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
       
        $uploadDirectory = $this->container->getParameter('upload_file_destination');

        $filePath = $uploadDirectory.$files->getFileName();

        //var_dump($files);

        $newname = $files->getOriginalName();
        //$type = 'pdf';

        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $newname);
        header('Content-Length: ' . filesize($filePath));
        // Read file
        readfile($filePath);

        die();
    
    }
}