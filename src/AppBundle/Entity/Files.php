<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Files
 *
 * @ORM\Table(name="files")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FilesRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Files
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
     * @ORM\Column(name="file_name", type="string", length=255)
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="original_name", type="string", length=500)
     */
     private $originalname;

    /**
     * @var string
     *
     * @ORM\Column(name="attachable_type", type="string", length=255)
     */
     private $attachabletype;

     /**
     * @var int
     *
     * @ORM\Column(name="attachable_id", type="integer")
     */

    private $attachableid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;


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
     * Set fileName
     *
     * @param string $fileName
     *
     * @return Files
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set originalname
     *
     * @param string $originalname
     *
     * @return Files
     */
     public function setOriginalName($originalname)
     {
         $this->originalname = $originalname;
 
         return $this;
     }
 
     /**
      * Get originalname
      *
      * @return string
      */
     public function getOriginalName()
     {
         return $this->originalname;
     }

    
    /**
     * Set attachabletype
     *
     * @param string $attachabletype
     *
     * @return Files
     */
     public function setAttachableType($attachabletype)
     {
         $this->attachabletype = $attachabletype;
 
         return $this;
     }
 
     /**
      * Get attachabletype
      *
      * @return string
      */
     public function getAttachableType()
     {
         return $this->attachabletype;
     }


     /**
     * Set attachableid
     *
     * @param string $attachableid
     *
     * @return Files
     */
     public function setAttachableId($attachableid)
     {
         $this->attachableid = $attachableid;
 
         return $this;
     }
 
     /**
      * Get attachableid
      *
      * @return int
      */
     public function getAttachableId()
     {
         return $this->attachableid;
     }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Files
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

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Files
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    /**
     * Triggered on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * Triggered on update
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime("now");
    }
    
}

