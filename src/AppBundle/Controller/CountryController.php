<?php

namespace AppBundle\Controller;

namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CountryController extends Controller
{
    /**
     * @Route("/api/country/getCountryList")
     * @Method("GET")
     * params: none
     * url: http://localhost/Talbert/backend/web/app_dev.php/api/country/getCountryList
     */
    public function getCountryListAction(Request $request)
    {
        if ($request->getMethod() == 'GET') {
            $arrApi = array();
            $countries = $this->getDoctrine()->getRepository('AppBundle:Country')->findAll();
            if (empty($countries) ) {
                $arrApi['status'] = 0;
                $arrApi['message'] = 'There is no country.';
            } else {
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the country list.';
                for($i=0;$i<count($countries);$i++) {
                    $arrApi['data']['countries'][$i]['id'] = $countries[$i]->getId();
                    $arrApi['data']['countries'][$i]['country_name'] = $countries[$i]->getCountryName();
                    $arrApi['data']['countries'][$i]['country_code'] = $countries[$i]->getCountryCode();
                }
            }
            return new JsonResponse($arrApi);
        }

    }

    /**
     * @Route("/api/state/getStateList")
     * @Method("POST")
     * params: country_id
     * mandatory: country_id
     * url: http://localhost/Talbert/backend/web/app_dev.php/api/state/getStateList
     */
    public function getStateListAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $_DATA = file_get_contents('php://input');
            $_DATA = json_decode($_DATA,true);
            $arrApi = array();
            if (empty($_DATA['country_id'])) {
                $states = $this->getDoctrine()->getRepository('AppBundle:State')->findAll();
                $arrApi['status'] = 1;
                $arrApi['message'] = 'Successfully retreived the state list.';
                for($i=0;$i<count($states);$i++) {
                    $arrApi['data']['states'][$i]['id'] = $states[$i]->getId();
                    $arrApi['data']['states'][$i]['state_name'] = $states[$i]->getStateName();
                    $arrApi['data']['states'][$i]['country_code'] = $states[$i]->getCountryId();
                }
            } else {
                $states = $this->getDoctrine()->getRepository('AppBundle:State')->findBy(array('countryId' => $_DATA['country_id']));
                if (empty($states) ) {
                    $arrApi['status'] = 0;
                    $arrApi['message'] = 'There is no state for this country.';
                } else {
                    $arrApi['status'] = 1;
                    $arrApi['message'] = 'Successfully retreived the state list.';
                    for($i=0;$i<count($states);$i++) {
                        $arrApi['data']['states'][$i]['id'] = $states[$i]->getId();
                        $arrApi['data']['states'][$i]['state_name'] = $states[$i]->getStateName();
                        $arrApi['data']['states'][$i]['country_code'] = $states[$i]->getCountryId();
                    }
                }
            }
            return new JsonResponse($arrApi);
        }
    }


    /**
     * @Route("/api/state/getStateList")
     * @Method("GET")
     * params: country_id
     * mandatory: country_id
     * url: http://localhost/Talbert/backend/web/app_dev.php/api/state/getStateList
     */

    public function getStates(Request $request) {

            $arrApi =[];
            $states = $this->getDoctrine()->getRepository('AppBundle:State')->findAll();
            for($i=0;$i<count($states);$i++) {
                $arrApi['data']['states'][$i]['id'] = $states[$i]->getId();
                $arrApi['data']['states'][$i]['state_name'] = $states[$i]->getStateName();
            }
            return new JsonResponse($arrApi);
    }


}
