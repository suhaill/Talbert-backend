<?php

namespace AppBundle\Controller;

use AppBundle\Entity\GrainPattern;
use AppBundle\Entity\FlakexFigured;
use AppBundle\Entity\Pattern;
use AppBundle\Entity\GrainDirection;
use AppBundle\Entity\FaceGrade;
use AppBundle\Entity\Thickness;
use AppBundle\Entity\CoreType;
use AppBundle\Entity\Backer;
use AppBundle\Entity\UvCured;
use AppBundle\Entity\UvCuredColor;
use AppBundle\Entity\Sheen;
use AppBundle\Entity\EdgeFinish;
use AppBundle\Entity\SizeEdgeMaterial;
use AppBundle\Entity\UnitMeasureCost;
use AppBundle\Entity\SameOn;
use AppBundle\Entity\BackerGrade;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Service\JsonToArrayGenerator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DropdowndataController extends Controller
{
    /**
     * @Route("/api/dropdowndata/getGrainpattern" , name="get_grainpattern")
     * @Method("GET")
     */

    public function getgrainpatternAction(Request $request)
    {
        
        
        $arrApi = array();

        $statusCode = 200;

        $grainpattern = $this->getDoctrine()->getRepository('AppBundle:GrainPattern')->findAll();

        if (empty($grainpattern) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no grain patterns.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the grain patterns list.';
            for($i=0;$i<count($grainpattern);$i++) {
                $arrApi['data']['grainpattern'][$i]['id'] = $grainpattern[$i]->getId();
                $arrApi['data']['grainpattern'][$i]['name'] = $grainpattern[$i]->getName();
            }
        }

        return new JsonResponse($arrApi,$statusCode);
        
    }


    /**
    * @Route("/api/dropdowndata/getFlakexfigured/{id}", name="get_flakexfigured", defaults={"id" = 0})
    * @Method("GET")
    */

    public function getflakexfiguredAction($id,Request $request)
    {
        

        if($id)
        {
           $flakexfigured = $this->getDoctrine()->getRepository('AppBundle:FlakexFigured')->findBy(array('gpId' => $id));
        }
        else
        {   
            $flakexfigured = $this->getDoctrine()->getRepository('AppBundle:FlakexFigured')->findAll();
        }

        
        $arrApi = array();
        $statusCode = 200;
        

        if (empty($flakexfigured) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no flakex figureds.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the flakex figureds list.';
            for($i=0;$i<count($flakexfigured);$i++) {
                $arrApi['data']['flakexfigured'][$i]['id'] = $flakexfigured[$i]->getId();
                $arrApi['data']['flakexfigured'][$i]['name'] = $flakexfigured[$i]->getName();
            }
        }

        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getPattern" , name="get_pattern")
     * @Method("GET")
     */

    public function getpatternAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $pattern = $this->getDoctrine()->getRepository('AppBundle:Pattern')->findAll();
        if (empty($pattern) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no patters.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the patterns list.';
            for($i=0;$i<count($pattern);$i++) {
                $arrApi['data']['pattern'][$i]['id'] = $pattern[$i]->getId();
                $arrApi['data']['pattern'][$i]['name'] = $pattern[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    
    }


    /**
     * @Route("/api/dropdowndata/getGraindirection" , name="get_graindirection")
     * @Method("GET")
     */

    public function getgraindirectionAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $graindirection = $this->getDoctrine()->getRepository('AppBundle:GrainDirection')->findAll();
        if (empty($graindirection) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no grain directions.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the grain directions list.';
            for($i=0;$i<count($graindirection);$i++) {
                $arrApi['data']['graindirection'][$i]['id'] = $graindirection[$i]->getId();
                $arrApi['data']['graindirection'][$i]['name'] = $graindirection[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    
    }

    /**
     * @Route("/api/dropdowndata/getFacegrade" , name="get_facegrade")
     * @Method("GET")
     */

    public function getfacegradeAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $facegrade = $this->getDoctrine()->getRepository('AppBundle:FaceGrade')->findAll();
        if (empty($facegrade) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no face grades.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the face grades list.';
            for($i=0;$i<count($facegrade);$i++) {
                $arrApi['data']['facegrade'][$i]['id'] = $facegrade[$i]->getId();
                $arrApi['data']['facegrade'][$i]['name'] = $facegrade[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getThickness" , name="get_thickness")
     * @Method("GET")
     */

    public function getthicknessAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $thickness = $this->getDoctrine()->getRepository('AppBundle:Thickness')->findAll();
        if (empty($thickness) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no thickness.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the thickness list.';
            for($i=0;$i<count($thickness);$i++) {
                $arrApi['data']['thickness'][$i]['id'] = $thickness[$i]->getId();
                $arrApi['data']['thickness'][$i]['name'] = $thickness[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getCoretype" , name="get_coretype")
     * @Method("GET")
     */

    public function getcoretypeAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $coretype = $this->getDoctrine()->getRepository('AppBundle:CoreType')->findAll();
        if (empty($coretype) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no core type.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the core type list.';
            for($i=0;$i<count($coretype);$i++) {
                $arrApi['data']['coretype'][$i]['id'] = $coretype[$i]->getId();
                $arrApi['data']['coretype'][$i]['name'] = $coretype[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getBacker" , name="get_backer")
     * @Method("GET")
     */

    public function getbackerAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $backer = $this->getDoctrine()->getRepository('AppBundle:Backer')->findAll();
        if (empty($backer) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no backer.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the backer list.';
            for($i=0;$i<count($backer);$i++) {
                $arrApi['data']['backer'][$i]['id'] = $backer[$i]->getId();
                $arrApi['data']['backer'][$i]['name'] = $backer[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }


    /**
     * @Route("/api/dropdowndata/getUvcured" , name="get_uvcured")
     * @Method("GET")
     */

    public function getuvcuredAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $uvcured = $this->getDoctrine()->getRepository('AppBundle:UvCured')->findAll();
        if (empty($uvcured) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no uv cured.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the uv cured list.';
            for($i=0;$i<count($uvcured);$i++) {
                $arrApi['data']['uvcured'][$i]['id'] = $uvcured[$i]->getId();
                $arrApi['data']['uvcured'][$i]['name'] = $uvcured[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }


    /**
     * @Route("/api/dropdowndata/getUvcuredcolor/{id}", defaults={"id" = 0}, name="get_uvcuredcolor")
     * @Method("GET")
     */

    public function getuvcuredcolorAction($id,Request $request)
    {
        
        if($id)
        {
           $uvcuredcolor = $this->getDoctrine()->getRepository('AppBundle:UvCuredColor')->findBy(array('uvcId' => $id));
        }
        else
        {   
            $uvcuredcolor = $this->getDoctrine()->getRepository('AppBundle:UvCuredColor')->findAll();
        }

        $arrApi = array();
        $statusCode = 200;
        
        if (empty($uvcuredcolor) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no uv cured colors.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the uv cured colors list.';
            for($i=0;$i<count($uvcuredcolor);$i++) {
                $arrApi['data']['uvcuredcolor'][$i]['id'] = $uvcuredcolor[$i]->getId();
                $arrApi['data']['uvcuredcolor'][$i]['name'] = $uvcuredcolor[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getSheen" , name="get_sheen")
     * @Method("GET")
     */

    public function getsheenAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $sheen = $this->getDoctrine()->getRepository('AppBundle:Sheen')->findAll();
        if (empty($sheen) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no sheens.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the sheens list.';
            for($i=0;$i<count($sheen);$i++) {
                $arrApi['data']['sheen'][$i]['id'] = $sheen[$i]->getId();
                $arrApi['data']['sheen'][$i]['name'] = $sheen[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getEdgefinish" , name="get_edgefinish")
     * @Method("GET")
     */

    public function getedgefinishAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $edgefinish = $this->getDoctrine()->getRepository('AppBundle:EdgeFinish')->findAll();
        if (empty($edgefinish) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no edge finishs.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the edge finishs list.';
            for($i=0;$i<count($edgefinish);$i++) {
                $arrApi['data']['edgefinish'][$i]['id'] = $edgefinish[$i]->getId();
                $arrApi['data']['edgefinish'][$i]['name'] = $edgefinish[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }


    /**
     * @Route("/api/dropdowndata/getSizeedgematerial" , name="get_sizeedgematerial")
     * @Method("GET")
     */

    public function getsizeedgematerialAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $sizeedgematerial = $this->getDoctrine()->getRepository('AppBundle:SizeEdgeMaterial')->findAll();
        if (empty($sizeedgematerial) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no size edge materials.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the size edge materials list.';
            for($i=0;$i<count($sizeedgematerial);$i++) {
                $arrApi['data']['sizeedgematerial'][$i]['id'] = $sizeedgematerial[$i]->getId();
                $arrApi['data']['sizeedgematerial'][$i]['name'] = $sizeedgematerial[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getUnitmeasurecost" , name="get_unitmeasurecost")
     * @Method("GET")
     */

    public function getunitmeasurecostAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $unitmeasurecost = $this->getDoctrine()->getRepository('AppBundle:UnitMeasureCost')->findAll();
        if (empty($unitmeasurecost) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no unit measure costs.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the unit measure costs list.';
            for($i=0;$i<count($unitmeasurecost);$i++) {
                $arrApi['data']['unitmeasurecost'][$i]['id'] = $unitmeasurecost[$i]->getId();
                $arrApi['data']['unitmeasurecost'][$i]['name'] = $unitmeasurecost[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getSameon" , name="get_sameon")
     * @Method("GET")
     */

    public function getsameonAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $sameon = $this->getDoctrine()->getRepository('AppBundle:SameOn')->findAll();
        if (empty($sameon) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no same ons.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the same ons list.';
            for($i=0;$i<count($sameon);$i++) {
                $arrApi['data']['sameon'][$i]['id'] = $sameon[$i]->getId();
                $arrApi['data']['sameon'][$i]['name'] = $sameon[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getBackergrade" , name="get_backergrade")
     * @Method("GET")
     */

    public function getbackergradeAction(Request $request)
    {
        
        
        $arrApi = array();
        $statusCode = 200;
        $backergrade = $this->getDoctrine()->getRepository('AppBundle:BackerGrade')->findAll();
        if (empty($backergrade) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no backer grades.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the backer grades list.';
            for($i=0;$i<count($backergrade);$i++) {
                $arrApi['data']['backergrade'][$i]['id'] = $backergrade[$i]->getId();
                $arrApi['data']['backergrade'][$i]['name'] = $backergrade[$i]->getName();
                $arrApi['data']['backergrade'][$i]['thickness'] = $backergrade[$i]->getBackerThickness();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
        
    }

    /**
     * @Route("/api/dropdowndata/getSpecies" , name="get_species")
     * @Method("GET")
     */

     public function getspeciesAction(Request $request)
     {
         
         
         $arrApi = array();
 
         $statusCode = 200;
 
         $species = $this->getDoctrine()->getRepository('AppBundle:Species')->findAll();
 
         if (empty($species) ) {
             $arrApi['status'] = 0;
             $arrApi['message'] = 'There is no species.';
         } else {
             $arrApi['status'] = 1;
             $arrApi['message'] = 'Successfully retreived the species list.';
             for($i=0;$i<count($species);$i++) {
                 $arrApi['data']['species'][$i]['id'] = $species[$i]->getId();
                 $arrApi['data']['species'][$i]['name'] = $species[$i]->getName();
             }
         }
 
         return new JsonResponse($arrApi,$statusCode);
         
     }

     /**
     * @Route("/api/dropdowndata/getSubSpecies/{id}" , defaults={"id" = 0}, name="get_subspecies")
     * @Method("GET")
     */

     public function getsubspeciesAction($id,Request $request)
     {
         
//        if($id)
//        {
//           $subspecies = $this->getDoctrine()->getRepository('AppBundle:Subspecies')->findBy(array('speciesId' => $id));
//        }
//        else
//        {
//            $subspecies = $this->getDoctrine()->getRepository('AppBundle:Subspecies')->findAll();
//        }

         $subspecies = $this->getDoctrine()->getRepository('AppBundle:GrainPattern')->findAll();

        $arrApi = array();
        $statusCode = 200;
        
        if (empty($subspecies) ) {
            $arrApi['status'] = 0;
            $arrApi['message'] = 'There is no sub species.';
        } else {
            $arrApi['status'] = 1;
            $arrApi['message'] = 'Successfully retreived the sub species list.';
            for($i=0;$i<count($subspecies);$i++) {
                $arrApi['data']['subspecies'][$i]['id'] = $subspecies[$i]->getId();
                $arrApi['data']['subspecies'][$i]['name'] = $subspecies[$i]->getName();
            }
        }
        return new JsonResponse($arrApi,$statusCode);
         
     }


}
