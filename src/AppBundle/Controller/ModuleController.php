<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use AppBundle\Entity\Module;

class ModuleController extends Controller
{
    /**
     * @Route("/api/module/getModulesList")
     * @Method("POST")
     * parameters: user_id
     * mandatory: All
     * url: http://localhost/Talbert-backend/web/app_dev.php/api/module/getModulesList
     */
    public function getModulesListAction(Request $request){
        if ($request->getMethod() == 'POST') {
            $arrApi = array();
            $_DATA = file_get_contents('php://input');
            $_DATA = json_decode($_DATA,true);
            if (empty ($_DATA['user_id']) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'Parameter missing.';
            } else {
                $userId = $_DATA['user_id'];
                $usrData = $this->checkIfUserExists($userId);
                if ( empty($usrData) ) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'This user does not exists.';
                } else {
                   $roleId = $this->getRoleIdByUserId($userId);
                   if ( empty($roleId) ) {
                       $arrApi['status'] = 0;
                       $arrApi['message'] = 'This user has no role.';
                   } else {
                       $moduleData = $this->getModuleData($roleId);
                       if ( empty($moduleData) ) {
                           $arrApi['status'] = 0;
                           $arrApi['message'] = 'This user has no module.';
                       } else {
                           $i=0;
                           foreach ($moduleData as $module) {
                               $moduleId = $module->getModuleId();
                               if ( !empty($moduleId) ) {
                                   $moduleRecord[$i] = $this->getModuleDataByModuleId($moduleId);
                               }
                               $i++;
                           }
                           $arrApi['status'] = 1;
                           $arrApi['message'] = 'Success in getting modules list.';
                           for ($j=0; $j<count($moduleRecord); $j++) {
                               $arrApi['data']['module'][$j]['id'] = $moduleRecord[$j]->getId();
                               $arrApi['data']['module'][$j]['name'] = $moduleRecord[$j]->getModuleName();
                               $arrApi['data']['module'][$j]['url'] = $moduleRecord[$j]->getModuleUrl();
                           }
                       }
                   }
                }
            }

        }
        return new JsonResponse($arrApi);
    }

    private function checkIfUserExists($userId) {
        $userData = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($userId);
        if ($userData) {
            return true;
        } else {
            return false;
        }
    }

    private function getRoleIdByUserId($userId) {
        $userData = $this->getDoctrine()->getRepository('AppBundle:User')->findOneById($userId);
        $roleId = $userData->getRoleId();
        if ( !empty($roleId) ) {
            return $roleId;
        } else {
            return false;
        }
    }

    private function getModuleData($roleId) {
        $moduleData = $this->getDoctrine()->getRepository('AppBundle:RoleModule')->findBy(array('roleId' => $roleId));
        if ( empty(!$moduleData) ) {
            return $moduleData;
        } else {
            return false;
        }
    }

    private function getModuleDataByModuleId($moduleId) {
        $moduleRecord = $this->getDoctrine()->getRepository('AppBundle:Module')->findOneById($moduleId);
        //print_r($moduleRecord);die;
        if ( !empty($moduleRecord) ) {
            return $moduleRecord;
        } else {
            return null;
        }
    }

}
