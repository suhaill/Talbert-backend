<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlakexFigured
 *
 * @ORM\Table(name="flakex_figureds")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FlakexFiguredRepository")
 */
class FlakexFigured
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
     * @ORM\Column(name="gp_id", type="integer")
     */
    private $gpId;

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
     * Set gpId
     *
     * @param integer $gpId
     *
     * @return FlakexFigured
     */
    public function setGpId($gpId)
    {
        $this->gpId = $gpId;

        return $this;
    }

    /**
     * Get gpId
     *
     * @return int
     */
    public function getGpId()
    {
        return $this->gpId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return FlakexFigured
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
     * @return FlakexFigured
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

