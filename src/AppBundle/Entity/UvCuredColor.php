<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UvCuredColor
 *
 * @ORM\Table(name="uv_cured_colors")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UvCuredColorRepository")
 */
class UvCuredColor
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
     * @ORM\Column(name="uvc_id", type="integer")
     */
    private $uvcId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;


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
     * Set uvcId
     *
     * @param integer $uvcId
     *
     * @return UvCuredColor
     */
    public function setUvcId($uvcId)
    {
        $this->uvcId = $uvcId;

        return $this;
    }

    /**
     * Get uvcId
     *
     * @return int
     */
    public function getUvcId()
    {
        return $this->uvcId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return UvCuredColor
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return UvCuredColor
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}

