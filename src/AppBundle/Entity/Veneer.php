<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Veneer
 *
 * @ORM\Table(name="veneers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VeneerRepository")
 */
class Veneer
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
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     * @var int
     *
     * @ORM\Column(name="quantityRemaining", type="integer", options={"default" = 0})
     */
    private $quantityRemaining;

    /**
     * @var int
     *
     * @ORM\Column(name="species_id", type="integer")
     */
    private $speciesId;

    /**
     * @var int
     *
     * @ORM\Column(name="grain_pattern_id", type="integer")
     */
    private $grainPatternId;

    /**
     * @var int
     *
     * @ORM\Column(name="flakex_figured_id", type="integer")
     */
    private $flakexFiguredId;

    /**
     * @var int
     *
     * @ORM\Column(name="pattern_id", type="integer")
     */
    private $patternId;

    /**
     * @var string
     *
     * @ORM\Column(name="grain_direction_id", type="string", length=10)
     */
    private $grainDirectionId;

    /**
     * @var int
     *
     * @ORM\Column(name="grade_id", type="integer")
     */
    private $gradeId;

    /**
     * @var int
     *
     * @ORM\Column(name="thickness_id", type="integer")
     */
    private $thicknessId;

    /**
     * @var float
     *
     * @ORM\Column(name="width", type="float")
     */
    private $width;

    /**
     * @var float
     *
     * @ORM\Column(name="widthFraction", type="float")
     */
    private $widthFraction;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_net_size", type="boolean")
     */
    private $isNetSize;

    /**
     * @var float
     *
     * @ORM\Column(name="length", type="float")
     */
    private $length;

    /**
     * @var float
     *
     * @ORM\Column(name="lengthFraction", type="float")
     */
    private $lengthFraction;

    /**
     * @var int
     *
     * @ORM\Column(name="core_type_id", type="integer")
     */
    private $coreTypeId;

    /**
     * @var int
     *
     * @ORM\Column(name="backer", type="integer")
     */
    private $backer;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_flex_sanded", type="boolean")
     */
    private $isFlexSanded;

    /**
     * @var bool
     *
     * @ORM\Column(name="sequenced", type="boolean")
     */
    private $sequenced;

    /**
     * @var string
     *
     * @ORM\Column(name="lumber_fee", type="string", length=15)
     */
    private $lumberFee;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text")
     */
    private $comments;

    /**
     * @var int
     *
     * @ORM\Column(name="quote_id", type="integer")
     */
    private $quoteId;

    /**
     *
     * @ORM\Column(name="file_id", type="integer")
     */
    private $fileId;

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
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isActive=true;

    /**
     * @ORM\Column(name="linitem_status_id", type="integer", nullable=true)
     */
    private $linitem_status_id;

    /**
     * @var string
     *
     * @ORM\Column(name="back_order_est_no", type="string", length=20, nullable=true)
     */
    private $backOrderEstNo;

    /**
     * @var string
     *
     * @ORM\Column(name="cust_markup_percent", type="string", length=10, nullable=true)
     */
    private $custMarkupPer;

    /**
     * @var string
     *
     * @ORM\Column(name="ven_cost", type="string", length=10, nullable=true, options={"default" = 25})
     */
    private $venCost;

    /**
     * @var string
     *
     * @ORM\Column(name="ven_waste", type="string", length=10, nullable=true, options={"default" = 1})
     */
    private $venWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_ven", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $subTotalVen;

    /**
     * @var string
     *
     * @ORM\Column(name="core_cost", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $coreCost;

    /**
     * @var string
     *
     * @ORM\Column(name="core_waste", type="string", length=10, nullable=true, options={"default" = 1})
     */
    private $coreWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_core", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $subTotalCore;

    /**
     * @var string
     *
     * @ORM\Column(name="backr_cost", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $backrCost;

    /**
     * @var string
     *
     * @ORM\Column(name="backr_waste", type="string", length=10, nullable=true, options={"default" = 1})
     */
    private $backrWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_backr", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $subTotalBackr;

    /**
     * @var string
     *
     * @ORM\Column(name="running_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $runningCost;

    /**
     * @var string
     *
     * @ORM\Column(name="running_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $runningWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_running", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalrunning;

    /**
     * @var string
     *
     * @ORM\Column(name="tot_cost_per_piece", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $totCostPerPiece;

    /**
     * @var string
     *
     * @ORM\Column(name="markup", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $markup;

    /**
     * @var string
     *
     * @ORM\Column(name="selling_price", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $sellingPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="lineitem_total", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $lineitemTotal;

    /**
     * @var string
     *
     * @ORM\Column(name="machine_setup", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $machineSetup;

    /**
     * @var string
     *
     * @ORM\Column(name="machine_tooling", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $machineTooling;

    /**
     * @var string
     *
     * @ORM\Column(name="pre_finish_setup", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $preFinishSetup;

    /**
     * @var string
     *
     * @ORM\Column(name="color_match", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $colorMatch;

    /**
     * @var string
     *
     * @ORM\Column(name="total_cost", type="string", length=10, nullable=true, options={"default" = 0})
     */
    private $totalCost;
    
    /**
     * @var int
     *
     * @ORM\Column(name="lineitem_num", type="integer")
     */
    private $lineItemNum;
    
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
     * @param bool $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Veneer
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getQuantityRemaining()
    {
        return $this->quantityRemaining;
    }

    /**
     * @param int $quantityRemaining
     */
    public function setQuantityRemaining($quantityRemaining)
    {
        $this->quantityRemaining = $quantityRemaining;
    }

    /**
     * Set speciesId
     *
     * @param integer $speciesId
     *
     * @return Veneer
     */
    public function setSpeciesId($speciesId)
    {
        $this->speciesId = $speciesId;

        return $this;
    }

    /**
     * Get speciesId
     *
     * @return int
     */
    public function getSpeciesId()
    {
        return $this->speciesId;
    }

    /**
     * Set grainPatternId
     *
     * @param integer $grainPatternId
     *
     * @return Veneer
     */
    public function setGrainPatternId($grainPatternId)
    {
        $this->grainPatternId = $grainPatternId;

        return $this;
    }

    /**
     * Get grainPatternId
     *
     * @return int
     */
    public function getGrainPatternId()
    {
        return $this->grainPatternId;
    }


    /**
     * Set flakexFiguredId
     *
     * @param integer $flakexFiguredId
     *
     * @return Veneer
     */
    public function setFlakexFiguredId($flakexFiguredId)
    {
        $this->flakexFiguredId = $flakexFiguredId;

        return $this;
    }

    /**
     * Get flakexFiguredId
     *
     * @return int
     */
    public function getFlakexFiguredId()
    {
        return $this->flakexFiguredId;
    }

    /**
     * Set pattern
     *
     * @param integer $pattern
     *
     * @return Veneer
     */
    public function setPatternId($pattern)
    {
        $this->patternId = $pattern;

        return $this;
    }

    /**
     * Get pattern
     *
     * @return int
     */
    public function getPatternId()
    {
        return $this->patternId;
    }

    /**
     * Set grainDirectionId
     *
     * @param string $grainDirectionId
     *
     * @return Veneer
     */
    public function setGrainDirectionId($grainDirectionId)
    {
        $this->grainDirectionId = $grainDirectionId;

        return $this;
    }

    /**
     * Get grainDirectionId
     *
     * @return string
     */
    public function getGrainDirectionId()
    {
        return $this->grainDirectionId;
    }

    /**
     * Set gradeId
     *
     * @param integer $gradeId
     *
     * @return Veneer
     */
    public function setGradeId($gradeId)
    {
        $this->gradeId = $gradeId;

        return $this;
    }

    /**
     * Get gradeId
     *
     * @return int
     */
    public function getGradeId()
    {
        return $this->gradeId;
    }

    /**
     * Set thicknessId
     *
     * @param integer $thicknessId
     *
     * @return Veneer
     */
    public function setThicknessId($thicknessId)
    {
        $this->thicknessId = $thicknessId;

        return $this;
    }

    /**
     * Get thicknessId
     *
     * @return int
     */
    public function getThicknessId()
    {
        return $this->thicknessId;
    }

    /**
     * Set width
     *
     * @param float $width
     *
     * @return Veneer
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return float
     */
    public function getWidthFraction()
    {
        return $this->widthFraction;
    }

    /**
     * @param float $widthFraction
     */
    public function setWidthFraction($widthFraction)
    {
        $this->widthFraction = $widthFraction;
    }

    /**
     * Set isNetSize
     *
     * @param boolean $isNetSize
     *
     * @return Veneer
     */
    public function setIsNetSize($isNetSize)
    {
        $this->isNetSize = $isNetSize;

        return $this;
    }

    /**
     * Get isNetSize
     *
     * @return bool
     */
    public function getIsNetSize()
    {
        return $this->isNetSize;
    }

    /**
     * Set length
     *
     * @param float $length
     *
     * @return Veneer
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return float
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return float
     */
    public function getLengthFraction()
    {
        return $this->lengthFraction;
    }

    /**
     * @param float $lengthFraction
     */
    public function setLengthFraction($lengthFraction)
    {
        $this->lengthFraction = $lengthFraction;
    }

    /**
     * Set coreTypeId
     *
     * @param integer $coreTypeId
     *
     * @return Veneer
     */
    public function setCoreTypeId($coreTypeId)
    {
        $this->coreTypeId = $coreTypeId;

        return $this;
    }

    /**
     * Get coreTypeId
     *
     * @return int
     */
    public function getCoreTypeId()
    {
        return $this->coreTypeId;
    }

    /**
     * Set backer
     *
     * @param integer $backer
     *
     * @return Veneer
     */
    public function setBacker($backer)
    {
        $this->backer = $backer;

        return $this;
    }

    /**
     * Get backer
     *
     * @return int
     */
    public function getBacker()
    {
        return $this->backer;
    }

    /**
     * Set isFlexSanded
     *
     * @param boolean $isFlexSanded
     *
     * @return Veneer
     */
    public function setIsFlexSanded($isFlexSanded)
    {
        $this->isFlexSanded = $isFlexSanded;

        return $this;
    }

    /**
     * Get isFlexSanded
     *
     * @return bool
     */
    public function getIsFlexSanded()
    {
        return $this->isFlexSanded;
    }

    /**
     * Set sequenced
     *
     * @param boolean $sequenced
     *
     * @return Veneer
     */
    public function setSequenced($sequenced)
    {
        $this->sequenced = $sequenced;

        return $this;
    }

    /**
     * Get sequenced
     *
     * @return bool
     */
    public function getSequenced()
    {
        return $this->sequenced;
    }

    /**
     * Set lumberFee
     *
     * @param string $lumberFee
     *
     * @return Veneer
     */
    public function setLumberFee($lumberFee)
    {
        $this->lumberFee = $lumberFee;

        return $this;
    }

    /**
     * Get lumberFee
     *
     * @return string
     */
    public function getLumberFee()
    {
        return $this->lumberFee;
    }

    /**
     * Set comments
     *
     * @param string $comments
     *
     * @return Veneer
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set quoteId
     *
     * @param integer $quoteId
     *
     * @return Veneer
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
     * @return mixed
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @param mixed $fileId
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Veneer
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
     * @return Veneer
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
     * @return mixed
     */
    public function getLinitemStatusId()
    {
        return $this->linitem_status_id;
    }

    /**
     * @param mixed $linitem_status_id
     */
    public function setLinitemStatusId($linitem_status_id)
    {
        $this->linitem_status_id = $linitem_status_id;
    }

    /**
     * @return string
     */
    public function getBackOrderEstNo()
    {
        return $this->backOrderEstNo;
    }

    /**
     * @param string $backOrderEstNo
     */
    public function setBackOrderEstNo($backOrderEstNo)
    {
        $this->backOrderEstNo = $backOrderEstNo;
    }

    /**
     * @return string
     */
    public function getCustMarkupPer()
    {
        return $this->custMarkupPer;
    }

    /**
     * @param string $custMarkupPer
     */
    public function setCustMarkupPer($custMarkupPer)
    {
        $this->custMarkupPer = $custMarkupPer;
    }

    /**
     * @return string
     */
    public function getVenCost()
    {
        return $this->venCost;
    }

    /**
     * @param string $venCost
     */
    public function setVenCost($venCost)
    {
        $this->venCost = $venCost;
    }

    /**
     * @return string
     */
    public function getVenWaste()
    {
        return $this->venWaste;
    }

    /**
     * @param string $venWaste
     */
    public function setVenWaste($venWaste)
    {
        $this->venWaste = $venWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalVen()
    {
        return $this->subTotalVen;
    }

    /**
     * @param string $subTotalVen
     */
    public function setSubTotalVen($subTotalVen)
    {
        $this->subTotalVen = $subTotalVen;
    }

    /**
     * @return string
     */
    public function getCoreCost()
    {
        return $this->coreCost;
    }

    /**
     * @param string $coreCost
     */
    public function setCoreCost($coreCost)
    {
        $this->coreCost = $coreCost;
    }

    /**
     * @return string
     */
    public function getSubTotalCore()
    {
        return $this->subTotalCore;
    }

    /**
     * @param string $subTotalCore
     */
    public function setSubTotalCore($subTotalCore)
    {
        $this->subTotalCore = $subTotalCore;
    }

    /**
     * @return string
     */
    public function getCoreWaste()
    {
        return $this->coreWaste;
    }

    /**
     * @param string $coreWaste
     */
    public function setCoreWaste($coreWaste)
    {
        $this->coreWaste = $coreWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalBackr()
    {
        return $this->subTotalBackr;
    }

    /**
     * @param string $subTotalBackr
     */
    public function setSubTotalBackr($subTotalBackr)
    {
        $this->subTotalBackr = $subTotalBackr;
    }

    /**
     * @return string
     */
    public function getBackrCost()
    {
        return $this->backrCost;
    }

    /**
     * @param string $backrCost
     */
    public function setBackrCost($backrCost)
    {
        $this->backrCost = $backrCost;
    }

    /**
     * @return string
     */
    public function getBackrWaste()
    {
        return $this->backrWaste;
    }

    /**
     * @param string $backrWaste
     */
    public function setBackrWaste($backrWaste)
    {
        $this->backrWaste = $backrWaste;
    }

    /**
     * @return string
     */
    public function getRunningCost()
    {
        return $this->runningCost;
    }

    /**
     * @param string $runningCost
     */
    public function setRunningCost($runningCost)
    {
        $this->runningCost = $runningCost;
    }

    /**
     * @return string
     */
    public function getRunningWaste()
    {
        return $this->runningWaste;
    }

    /**
     * @param string $runningWaste
     */
    public function setRunningWaste($runningWaste)
    {
        $this->runningWaste = $runningWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalrunning()
    {
        return $this->subTotalrunning;
    }

    /**
     * @param string $subTotalrunning
     */
    public function setSubTotalrunning($subTotalrunning)
    {
        $this->subTotalrunning = $subTotalrunning;
    }

    /**
     * @return string
     */
    public function getTotCostPerPiece()
    {
        return $this->totCostPerPiece;
    }

    /**
     * @param string $totCostPerPiece
     */
    public function setTotCostPerPiece($totCostPerPiece)
    {
        $this->totCostPerPiece = $totCostPerPiece;
    }

    /**
     * @return string
     */
    public function getMarkup()
    {
        return $this->markup;
    }

    /**
     * @param string $markup
     */
    public function setMarkup($markup)
    {
        $this->markup = $markup;
    }

    /**
     * @return string
     */
    public function getSellingPrice()
    {
        return $this->sellingPrice;
    }

    /**
     * @param string $sellingPrice
     */
    public function setSellingPrice($sellingPrice)
    {
        $this->sellingPrice = $sellingPrice;
    }

    /**
     * @return string
     */
    public function getLineitemTotal()
    {
        return $this->lineitemTotal;
    }

    /**
     * @param string $lineitemTotal
     */
    public function setLineitemTotal($lineitemTotal)
    {
        $this->lineitemTotal = $lineitemTotal;
    }

    /**
     * @return string
     */
    public function getMachineSetup()
    {
        return $this->machineSetup;
    }

    /**
     * @param string $machineSetup
     */
    public function setMachineSetup($machineSetup)
    {
        $this->machineSetup = $machineSetup;
    }

    /**
     * @return string
     */
    public function getMachineTooling()
    {
        return $this->machineTooling;
    }

    /**
     * @param string $machineTooling
     */
    public function setMachineTooling($machineTooling)
    {
        $this->machineTooling = $machineTooling;
    }

    /**
     * @return string
     */
    public function getPreFinishSetup()
    {
        return $this->preFinishSetup;
    }

    /**
     * @param string $preFinishSetup
     */
    public function setPreFinishSetup($preFinishSetup)
    {
        $this->preFinishSetup = $preFinishSetup;
    }

    /**
     * @return string
     */
    public function getColorMatch()
    {
        return $this->colorMatch;
    }

    /**
     * @param string $colorMatch
     */
    public function setColorMatch($colorMatch)
    {
        $this->colorMatch = $colorMatch;
    }

    /**
     * @return string
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @param string $totalCost
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
    }
    
    /**
     * @return int
     */
    public function getLineItemNum()
    {
        return $this->lineItemNum;
    }

    /**
     * @param int $lineItemNum
     */
    public function setLineItemNum($lineItemNum)
    {
        $this->lineItemNum = $lineItemNum;
    }
}