<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShippedQuantity
 *
 * @ORM\Table(name="shipped_quantity")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ShippedQuantityRepository")
 */
class ShippedQuantity
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
     * @ORM\Column(name="order_id", type="integer")
     */
    private $orderId;

    /**
     * @var int
     *
     * @ORM\Column(name="lineitem_id", type="integer")
     */
    private $lineitemId;

    /**
     * @var string
     *
     * @ORM\Column(name="lineitem_type", type="string", length=10)
     */
    private $lineitemType;

    /**
     * @var int
     *
     * @ORM\Column(name="qty_shipped", type="integer")
     */
    private $qtyShipped;

    /**
     * @var int
     *
     * @ORM\Column(name="loggedin_user_id", type="integer")
     */
    private $loggedinUserId;

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
     * Set orderId
     *
     * @param integer $orderId
     *
     * @return ShippedQuantity
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * Get orderId
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Set lineitemId
     *
     * @param integer $lineitemId
     *
     * @return ShippedQuantity
     */
    public function setLineitemId($lineitemId)
    {
        $this->lineitemId = $lineitemId;

        return $this;
    }

    /**
     * Get lineitemId
     *
     * @return int
     */
    public function getLineitemId()
    {
        return $this->lineitemId;
    }

    /**
     * Set lineitemType
     *
     * @param string $lineitemType
     *
     * @return ShippedQuantity
     */
    public function setLineitemType($lineitemType)
    {
        $this->lineitemType = $lineitemType;

        return $this;
    }

    /**
     * Get lineitemType
     *
     * @return string
     */
    public function getLineitemType()
    {
        return $this->lineitemType;
    }

    /**
     * Set qtyShipped
     *
     * @param integer $qtyShipped
     *
     * @return ShippedQuantity
     */
    public function setQtyShipped($qtyShipped)
    {
        $this->qtyShipped = $qtyShipped;

        return $this;
    }

    /**
     * Get qtyShipped
     *
     * @return int
     */
    public function getQtyShipped()
    {
        return $this->qtyShipped;
    }

    /**
     * Set loggedinUserId
     *
     * @param integer $loggedinUserId
     *
     * @return ShippedQuantity
     */
    public function setLoggedinUserId($loggedinUserId)
    {
        $this->loggedinUserId = $loggedinUserId;

        return $this;
    }

    /**
     * Get loggedinUserId
     *
     * @return int
     */
    public function getLoggedinUserId()
    {
        return $this->loggedinUserId;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
}

