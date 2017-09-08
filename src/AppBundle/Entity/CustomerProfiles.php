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
}

