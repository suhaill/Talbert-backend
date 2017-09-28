<?php
/**
 * Created by PhpStorm.
 * User: Suhail
 * Date: 22/9/17
 * Time: 3:09 PM
 */

namespace AppBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Doctrine\ORM\EntityManager;


class SharedJob
{
    /**
     *
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getFnameById($userid) {
        $profileObj = $this->getDoctrine()->getRepository('AppBundle:Profile')->findOneBy(array('userId' => $userid));
        return $profileObj->getFname();

    }
}