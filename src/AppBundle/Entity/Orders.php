<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Orders
 *
 * @ORM\Table(name="orders")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrdersRepository")
 */
class Orders
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
     * @var string
     *
     * @ORM\Column(name="est_number", type="string", length=20, nullable=true)
     */
    private $estNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="order_number", type="string", length=20, nullable=true)
     */
    private $orderNumber;

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="approved_by", type="string", length=50, nullable=true)
     */
    private $approvedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="via", type="string", length=20, nullable=true)
     */
    private $via;

    /**
     * @var string
     *
     * @ORM\Column(name="other", type="string", length=50, nullable=true)
     */
    private $other;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="order_date", type="string", length=30, nullable=true)
     */
    private $orderDate;

    /**
     * @var string
     *
     * @ORM\Column(name="product_name", type="string", length=255, nullable=true)
     */
    private $productName;

    /**
     * @var string
     *
     * @ORM\Column(name="po_number", type="string", length=20, nullable=true)
     */
    private $poNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ship_date", type="string", length=30, nullable=true)
     */
    private $shipDate;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":1})
     */
    private $isActive=true;
    
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
     * @return int
     */
    public function getQuoteId()
    {
        return $this->quoteId;
    }

    /**
     * @param int $quoteId
     */
    public function setQuoteId($quoteId)
    {
        $this->quoteId = $quoteId;
    }

    /**
     * @return string
     */
    public function getApprovedBy()
    {
        return $this->approvedBy;
    }

    /**
     * @param string $approvedBy
     */
    public function setApprovedBy($approvedBy)
    {
        $this->approvedBy = $approvedBy;
    }

    /**
     * @return string
     */
    public function getVia()
    {
        return $this->via;
    }

    /**
     * @param string $via
     */
    public function setVia($via)
    {
        $this->via = $via;
    }

    /**
     * @return string
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * @param string $other
     */
    public function setOther($other)
    {
        $this->other = $other;
    }

    /**
     * Set estNumber
     *
     * @param string $estNumber
     *
     * @return Orders
     */
    public function setEstNumber($estNumber)
    {
        $this->estNumber = $estNumber;

        return $this;
    }

    /**
     * Get estNumber
     *
     * @return string
     */
    public function getEstNumber()
    {
        return $this->estNumber;
    }

    /**
     * Set orderDate
     *
     * @param \DateTime $orderDate
     *
     * @return Orders
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * Get orderDate
     *
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * Set productName
     *
     * @param string $productName
     *
     * @return Orders
     */
    public function setProductName($productName)
    {
        $this->productName = $productName;

        return $this;
    }

    /**
     * Get productName
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->productName;
    }

    /**
     * Set poNumber
     *
     * @param string $poNumber
     *
     * @return Orders
     */
    public function setPoNumber($poNumber)
    {
        $this->poNumber = $poNumber;

        return $this;
    }

    /**
     * Get poNumber
     *
     * @return string
     */
    public function getPoNumber()
    {
        return $this->poNumber;
    }

    /**
     * Set shipDate
     *
     * @param \DateTime $shipDate
     *
     * @return Orders
     */
        public function setShipDate($shipDate)
    {
        $this->shipDate = $shipDate;

        return $this;
    }

    /**
     * Get shipDate
     *
     * @return \DateTime
     */
    public function getShipDate()
    {
        return $this->shipDate;
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
}

