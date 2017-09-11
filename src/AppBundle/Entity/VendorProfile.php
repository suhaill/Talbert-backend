<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * VendorProfile
 *
 * @ORM\Table(name="vendor_profile")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VendorProfileRepository")
 */
class VendorProfile
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
     * @ORM\Column(name="term_id", type="integer")
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
     * @return VendorProfile
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
     * @return VendorProfile
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

