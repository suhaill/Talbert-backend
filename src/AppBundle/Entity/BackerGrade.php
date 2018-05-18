<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BackerGrade
 *
 * @ORM\Table(name="backer_grades")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BackerGradeRepository")
 */
class BackerGrade
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="abbr", type="string", length=20, nullable=true)
     */
    private $abbr;

    /**
     * @var string
     *
     * @ORM\Column(name="backerThickness", type="float")
     */
     private $backerThickness;

    
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
     * Set name
     *
     * @param string $name
     *
     * @return BackerGrade
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
     * Set abbr
     *
     * @param string $abbr
     *
     * @return BackerGrade
     */
    public function setAbbr($abbr)
    {
        $this->abbr = $abbr;

        return $this;
    }

    /**
     * Get abbr
     *
     * @return string
     */
    public function getAbbr()
    {
        return $this->abbr;
    }

    /**
     * Set createdAt
     *
     * @param string $createdAt
     *
     * @return BackerGrade
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get backerId
     *
     * @return int
     */
     public function getBackerId()
     {
         return $this->backerId;
     }
 
     /**
      * Set backerThickness
      *
      * @param integer $backerThickness
      *
      * @return Plywood
      */
      public function setBackerThickness($backerThickness)
      {
          $this->backerThickness = $backerThickness;
  
          return $this;
      }
  
      /**
       * Get backerThickness
       *
       * @return int
       */
      public function getBackerThickness()
      {
          return $this->backerThickness;
      }
}

