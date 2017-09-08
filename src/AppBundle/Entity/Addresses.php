<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Addresses
 *
 * @ORM\Table(name="addresses")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AddressesRepository")
 */
class Addresses
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
     * @ORM\Column(name="nickname", type="string", length=200, nullable=true)
     */
    private $nickname;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=100, nullable=true)
     */
    private $street;

    /**
     * @var int
     *
     * @ORM\Column(name="state_id", type="integer", nullable=true)
     */
    private $stateId;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=100, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="zip", type="string", length=15, nullable=true)
     */
    private $zip;

    /**
     * @var int
     *
     * @ORM\Column(name="delivery_charge_id", type="integer", nullable=true)
     */
    private $deliveryChargeId;

    /**
     * @var string
     *
     * @ORM\Column(name="sales_tax_rate", type="string", length=10, nullable=true)
     */
    private $salesTaxRate;

    /**
     * @ORM\Column(name="address_type", type="string", columnDefinition="enum('billing', 'shipping')")
     */
    private $addressType;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

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
     * Set nickname
     *
     * @param string $nickname
     *
     * @return Addresses
     */
    public function setNickname($nickname)
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Get nickname
     *
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Addresses
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set stateId
     *
     * @param integer $stateId
     *
     * @return Addresses
     */
    public function setStateId($stateId)
    {
        $this->stateId = $stateId;

        return $this;
    }

    /**
     * Get stateId
     *
     * @return int
     */
    public function getStateId()
    {
        return $this->stateId;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return Addresses
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zip
     *
     * @param string $zip
     *
     * @return Addresses
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set deliveryChargeId
     *
     * @param integer $deliveryChargeId
     *
     * @return Addresses
     */
    public function setDeliveryChargeId($deliveryChargeId)
    {
        $this->deliveryChargeId = $deliveryChargeId;

        return $this;
    }

    /**
     * Get deliveryChargeId
     *
     * @return int
     */
    public function getDeliveryChargeId()
    {
        return $this->deliveryChargeId;
    }

    /**
     * Set salesTaxRate
     *
     * @param string $salesTaxRate
     *
     * @return Addresses
     */
    public function setSalesTaxRate($salesTaxRate)
    {
        $this->salesTaxRate = $salesTaxRate;

        return $this;
    }

    /**
     * Get salesTaxRate
     *
     * @return string
     */
    public function getSalesTaxRate()
    {
        return $this->salesTaxRate;
    }

    /**
     * Set addressType
     *
     * @param string $addressType
     *
     * @return Addresses
     */
    public function setAddressType($addressType)
    {
        $this->addressType = $addressType;

        return $this;
    }

    /**
     * Get addressType
     *
     * @return string
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Addresses
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return Addresses
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Addresses
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

