<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuoteStatus
 *
 * @ORM\Table(name="quote_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuoteStatusRepository")
 */
class QuoteStatus
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
     * @ORM\Column(name="quote_id", type="integer")
     */
    private $quoteId;

    /**
     * @var int
     *
     * @ORM\Column(name="status_id", type="integer")
     */
    private $statusId;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive=true;

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
     * Set quoteId
     *
     * @param integer $quoteId
     *
     * @return QuoteStatus
     */
    public function setQuoteId($quoteId)
    {
        $this->quoteId = $quoteId;

        return $this;
    }

    /**
     * Get quoteId
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->quoteId;
    }

    /**
     * Set statusId
     *
     * @param integer $statusId
     *
     * @return QuoteStatus
     */
    public function setStatusId($statusId)
    {
        $this->statusId = $statusId;

        return $this;
    }

    /**
     * Get statusId
     *
     * @return int
     */
    public function getStatusId()
    {
        return $this->statusId;
    }
    
    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return QuoteStatus
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
     * @return QuoteStatus
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
}

