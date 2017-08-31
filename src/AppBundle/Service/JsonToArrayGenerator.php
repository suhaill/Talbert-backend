<?php
/**
 * Created by PhpStorm.
 * User: rajesh
 * Date: 27/8/17
 * Time: 9:38 AM
 */

namespace AppBundle\Service;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonToArrayGenerator
{

    public function getJson(Request $request)
    {
        $content = $request->getContent();


        if(empty($content)){
            throw new BadRequestHttpException("Content is empty");
        }



        return new ArrayCollection(json_decode($content, true));
    }
}