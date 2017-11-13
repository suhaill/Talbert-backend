<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Quotes
 *
 * @ORM\Table(name="quotes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuotesRepository")
 */
class Quotes
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
     * @ORM\Column(name="ref_id", type="integer", nullable=true)
     */
    private $refid;

    /**
     * @return int
     */
    public function getRefid()
    {
        return $this->refid;
    }

    /**
     * @param int $refid
     */
    public function setRefid($refid)
    {
        $this->refid = $refid;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="estimate_date", type="string", length=30, nullable=true)
     */
    private $estimatedate;

    /**
     * @var int
     *
     * @ORM\Column(name="estimator_id", type="integer")
     */
    private $estimatorId;

    /**
     * @var string
     *
     * @ORM\Column(name="control_number", type="string", length=20, nullable=true)
     */
    private $controlNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer")
     */
    private $version;

    /**
     * @var int
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=true)
     */
    private $customerId;

    /**
     * @return string
     */
    public function getEstimatedate()
    {
        return $this->estimatedate;
    }

    /**
     * @param string $estimatedate
     */
    public function setEstimatedate($estimatedate)
    {
        $this->estimatedate = $estimatedate;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="ref_num", type="string", length=100, nullable=true)
     */
    private $refNum;

    /**
     * @var int
     *
     * @ORM\Column(name="salesman_id", type="integer", nullable=true)
     */
    private $salesmanId;

    /**
     * @var string
     *
     * @ORM\Column(name="job_name", type="string", length=100, nullable=true)
     */
    private $jobName;

    /**
     * @var int
     *
     * @ORM\Column(name="term_id", type="integer", nullable=true)
     */
    private $termId;

    /**
     * @var int
     *
     * @ORM\Column(name="ship_methd_id", type="integer", nullable=true)
     */
    private $shipMethdId;

    /**
     * @var int
     *
     * @ORM\Column(name="ship_add_id", type="integer", nullable=true)
     */
    private $shipAddId;

    /**
     * @var string
     *
     * @ORM\Column(name="lead_time", type="string", length=30, nullable=true)
     */
    private $leadTime;

    /**
     * @ORM\Column(name="status", type="string", columnDefinition="enum('Current', 'Dead','Hold','Approved')")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=500, nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="quote_total", type="string", length=10, nullable=true, options={"default":0})
     */
    private $quoteTot;

    /**
     * @var string
     *
     * @ORM\Column(name="exp_fee", type="string", length=10, nullable=true, options={"default":0})
     */
    private $expFee;

    /**
     * @var string
     *
     * @ORM\Column(name="discount", type="string", length=10, nullable=true, options={"default":0})
     */
    private $discount;

    /**
     * @var string
     *
     * @ORM\Column(name="lum_fee", type="string", length=10, nullable=true, options={"default":0})
     */
    private $lumFee;

    /**
     * @var string
     *
     * @ORM\Column(name="ship_charge", type="string", length=10, nullable=true, options={"default":0})
     */
    private $shipCharge;

    /**
     * @var string
     *
     * @ORM\Column(name="sales_tax", type="string", length=10, nullable=true, options={"default":0})
     */
    private $salesTax;

    /**
     * @var string
     *
     * @ORM\Column(name="project_tot", type="string", length=10, nullable=true, options={"default":0})
     */
    private $projectTot;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
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
     * Set estimatorId
     *
     * @param integer $estimatorId
     *
     * @return Quotes
     */
    public function setEstimatorId($estimatorId)
    {
        $this->estimatorId = $estimatorId;

        return $this;
    }

    /**
     * Get estimatorId
     *
     * @return int
     */
    public function getEstimatorId()
    {
        return $this->estimatorId;
    }

    /**
     * Set controlNumber
     *
     * @param string $controlNumber
     *
     * @return Quotes
     */
    public function setControlNumber($controlNumber)
    {
        $this->controlNumber = $controlNumber;

        return $this;
    }

    /**
     * Get controlNumber
     *
     * @return string
     */
    public function getControlNumber()
    {
        return $this->controlNumber;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set customerId
     *
     * @param integer $customerId
     *
     * @return Quotes
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set refNum
     *
     * @param string $refNum
     *
     * @return Quotes
     */
    public function setRefNum($refNum)
    {
        $this->refNum = $refNum;

        return $this;
    }

    /**
     * Get refNum
     *
     * @return string
     */
    public function getRefNum()
    {
        return $this->refNum;
    }

    /**
     * Set salesmanId
     *
     * @param integer $salesmanId
     *
     * @return Quotes
     */
    public function setSalesmanId($salesmanId)
    {
        $this->salesmanId = $salesmanId;

        return $this;
    }

    /**
     * Get salesmanId
     *
     * @return int
     */
    public function getSalesmanId()
    {
        return $this->salesmanId;
    }

    /**
     * Set jobName
     *
     * @param string $jobName
     *
     * @return Quotes
     */
    public function setJobName($jobName)
    {
        $this->jobName = $jobName;

        return $this;
    }

    /**
     * Get jobName
     *
     * @return string
     */
    public function getJobName()
    {
        return $this->jobName;
    }

    /**
     * Set termId
     *
     * @param integer $termId
     *
     * @return Quotes
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
     * Set shipMethdId
     *
     * @param integer $shipMethdId
     *
     * @return Quotes
     */
    public function setShipMethdId($shipMethdId)
    {
        $this->shipMethdId = $shipMethdId;

        return $this;
    }

    /**
     * Get shipMethdId
     *
     * @return int
     */
    public function getShipMethdId()
    {
        return $this->shipMethdId;
    }

    /**
     * Set shipAddId
     *
     * @param integer $shipAddId
     *
     * @return Quotes
     */
    public function setShipAddId($shipAddId)
    {
        $this->shipAddId = $shipAddId;

        return $this;
    }

    /**
     * Get shipAddId
     *
     * @return int
     */
    public function getShipAddId()
    {
        return $this->shipAddId;
    }

    /**
     * Set leadTime
     *
     * @param string $leadTime
     *
     * @return Quotes
     */
    public function setLeadTime($leadTime)
    {
        $this->leadTime = $leadTime;

        return $this;
    }

    /**
     * Get leadTime
     *
     * @return string
     */
    public function getLeadTime()
    {
        return $this->leadTime;
    }

    /**
     *
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getQuoteTot()
    {
        return $this->quoteTot;
    }

    /**
     * @param string $quoteTot
     */
    public function setQuoteTot($quoteTot)
    {
        $this->quoteTot = $quoteTot;
    }

    /**
     * @return string
     */
    public function getExpFee()
    {
        return $this->expFee;
    }

    /**
     * @param string $expFee
     */
    public function setExpFee($expFee)
    {
        $this->expFee = $expFee;
    }

    /**
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param string $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getLumFee()
    {
        return $this->lumFee;
    }

    /**
     * @param string $lumFee
     */
    public function setLumFee($lumFee)
    {
        $this->lumFee = $lumFee;
    }

    /**
     * @return string
     */
    public function getShipCharge()
    {
        return $this->shipCharge;
    }

    /**
     * @param string $shipCharge
     */
    public function setShipCharge($shipCharge)
    {
        $this->shipCharge = $shipCharge;
    }

    /**
     * @return string
     */
    public function getSalesTax()
    {
        return $this->salesTax;
    }

    /**
     * @param string $salesTax
     */
    public function setSalesTax($salesTax)
    {
        $this->salesTax = $salesTax;
    }

    /**
     * @return string
     */
    public function getProjectTot()
    {
        return $this->projectTot;
    }

    /**
     * @param string $projectTot
     */
    public function setProjectTot($projectTot)
    {
        $this->projectTot = $projectTot;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Quotes
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
     * @return Quotes
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

