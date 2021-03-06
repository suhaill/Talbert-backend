<?php

namespace AppBundle\Repository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;

/**
 * ProfileRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProfileRepository extends \Doctrine\ORM\EntityRepository
{


    public function getVendors()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select(
            'p.userId','p.company','p.phone'
        )->innerJoin(
            'AppBundle:VendorProfile',
            'vp',
            Join::WITH,
            $qb->expr()->eq('p.userId', 'vp.userId')
        )->orderBy('p.userId', 'DESC')
            ->setFirstResult( 0 )
            ->setMaxResults( 20 );

        try {
            return $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new NoResultException("No record found");
            // return null;
        }
    }

    public function getMoreVendors($data) {
        $pageNo = $data->get('current_page');
        $limit = $data->get('limit');
        $companyName = $data->get('vender_name');
        $sortBy = $data->get('sort_by');
        $order = $data->get('order');
        $offset = ($pageNo - 1)  * $limit;
        if (empty($companyName) && empty($sortBy) && empty($order)) {
                $qb = $this->createQueryBuilder('p');
                $qb->select(
                    'p.userId','p.company','p.phone'
                )->innerJoin(
                    'AppBundle:VendorProfile',
                    'vp',
                    Join::WITH,
                    $qb->expr()->eq('p.userId', 'vp.userId')
                )->setFirstResult( $offset )
                 ->setMaxResults( $limit )
                 ->orderBy('p.userId', 'DESC');

        } elseif (!empty($companyName) && empty($sortBy) && empty($order)) {
            $qb = $this->createQueryBuilder('p');
            $qb->select(
                'p.userId','p.company','p.phone'
            )->innerJoin(
                'AppBundle:VendorProfile',
                'vp',
                Join::WITH,
                $qb->expr()->eq('p.userId', 'vp.userId')
            )->setFirstResult( $offset )
                ->setMaxResults( $limit )
                ->orderBy('p.userId', 'DESC')
                ->where('p.company LIKE :company')
                ->setParameter('company',$companyName."%");
        } elseif (empty($companyName) && !empty($sortBy) && !empty($order)) {
            $qb = $this->createQueryBuilder('p');
            $qb->select(
                'p.userId','p.company','p.phone'
            )->innerJoin(
                'AppBundle:VendorProfile',
                'vp',
                Join::WITH,
                $qb->expr()->eq('p.userId', 'vp.userId')
            )->setFirstResult( $offset )
                ->setMaxResults( $limit )
                ->orderBy('p.'.$sortBy, $order);
        } else {
            $qb = $this->createQueryBuilder('p');
            $qb->select(
                'p.userId','p.company','p.phone'
            )->innerJoin(
                'AppBundle:VendorProfile',
                'vp',
                Join::WITH,
                $qb->expr()->eq('p.userId', 'vp.userId')
            );
        }
        try {
            return $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new NoResultException("No record found");
            // return null;
        }

    }
    
    public function getVendor($id)
    {

        $qb = $this->createQueryBuilder('p');
        $qb->select(
           'p.id profileId', 'vp.id vprofileId','p.userId','p.company','p.fname','p.lname','p.email','p.phone','p.address','p.stateId','p.city','p.zip','vp.termId','vp.comment'
        )->innerJoin(
            'AppBundle:VendorProfile',
            'vp',
            Join::WITH,
            $qb->expr()->eq('p.userId', 'vp.userId')
        )->where('p.userId=:userid')
            ->setParameter('userid',$id);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            throw new NoResultException("No record found");
           // return null;
        }

    }

}
