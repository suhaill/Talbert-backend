<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerProfiles
 *
 * @ORM\Table(name="customer_profiles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerProfilesRepository")
 */
class CustomerProfiles
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;


    /**
     * @ORM\Column(type="integer",name="userid",nullable=false)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="term_id", type="integer", nullable=true)
     */
    private $termId;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=500, nullable=true)
     */
    private $comment;
    
    /**
     * @ORM\Column(type="boolean", nullable=false, name="is_checked")
     */
    private $isChecked=true;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set termId
     *
     * @param integer $termId
     *
     * @return CustomerProfiles
     */
    public function setTermId($termId)
    {
        $this->termId = $termId;

        return $this;
    }

    /**
     * Get termId
     *
     * @return int
     */
    public function getTermId()
    {
        return $this->termId;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return CustomerProfiles
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
    
    /**
     * @return mixed
     */
    public function getIsChecked()
    {
        return $this->isChecked;
    }

    /**
     * @param mixed $isChecked
     */
    public function setIsChecked($isChecked)
    {
        $this->isChecked = $isChecked;
    }

}

