<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LineItemStatus
 *
 * @ORM\Table(name="line_item_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LineItemStatusRepository")
 */
class LineItemStatus
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
     * @ORM\Column(name="line_item_id", type="integer")
     */
    private $lineItemId;

    /**
     * @var int
     *
     * @ORM\Column(name="status_id", type="integer")
     */
    private $statusId;

    /**
     * @var string
     *
     * @ORM\Column(name="line_item_type", type="string", columnDefinition="enum('Plywood', 'Veneer','Door')")
     */
    private $lineItemType;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
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
     * Set lineItemId
     *
     * @param integer $lineItemId
     *
     * @return LineItemStatus
     */
    public function setLineItemId($lineItemId)
    {
        $this->lineItemId = $lineItemId;

        return $this;
    }

    /**
     * Get lineItemId
     *
     * @return int
     */
    public function getLineItemId()
    {
        return $this->lineItemId;
    }

    /**
     * Set statusId
     *
     * @param integer $statusId
     *
     * @return LineItemStatus
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
     * Set lineItemType
     *
     * @param string $lineItemType
     *
     * @return LineItemStatus
     */
    public function setLineItemType($lineItemType)
    {
        $this->lineItemType = $lineItemType;

        return $this;
    }

    /**
     * Get lineItemType
     *
     * @return string
     */
    public function getLineItemType()
    {
        return $this->lineItemType;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return LineItemStatus
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return LineItemStatus
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
     * @return LineItemStatus
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

