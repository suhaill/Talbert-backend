<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plywood
 *
 * @ORM\Table(name="plywood")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PlywoodRepository")
 */
class Plywood
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
     * @ORM\Column(name="speciesId", type="integer")
     */
    private $speciesId;

    /**
     * @var int
     *
     * @ORM\Column(name="grainPatternId", type="integer")
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
     * @ORM\Column(name="patternId", type="integer")
     */
    private $patternId;

    /**
     * @var int
     *
     * @ORM\Column(name="grainDirectionId", type="integer")
     */
    private $grainDirectionId;

    /**
     * @var int
     *
     * @ORM\Column(name="gradeId", type="integer")
     */
    private $gradeId;

    /**
     * @var int
     *
     * @ORM\Column(name="thicknessId", type="integer")
     */
    private $thicknessId;

    /**
     * @var float
     *
     * @ORM\Column(name="plywoodWidth", type="float")
     */
    private $plywoodWidth;

    /**
     * @var float
     *
     * @ORM\Column(name="widthFraction", type="float")
     */
    private $widthFraction;

    /**
     * @var float
     *
     * @ORM\Column(name="plywoodLength", type="float")
     */
    private $plywoodLength;

    /**
     * @var float
     *
     * @ORM\Column(name="lengthFraction", type="float")
     */
    private $lengthFraction;

    /**
     * @var int
     *
     * @ORM\Column(name="finishThickId", type="float")
     */
    private $finishThickId;

    /**
     * @var string
     *
     * @ORM\Column(name="finishThickType", type="string", length=10)
     */
    private $finishThickType;

    /**
     * @var int
     *
     * @ORM\Column(name="backerId", type="integer")
     */
    private $backerId;

    /**
     * @var bool
     *
     * @ORM\Column(name="isSequenced", type="boolean")
     */
    private $isSequenced;

    /**
     * @var int
     *
     * @ORM\Column(name="coreType", type="integer")
     */
    private $coreType;

    /**
     * @var string
     *
     * @ORM\Column(name="thickness", type="string", length=100)
     */
    private $thickness;

    /**
     * @var string
     *
     * @ORM\Column(name="finish", type="string", length=255)
     */
    private $finish;

    /**
     * @var string
     *
     * @ORM\Column(name="facPaint", type="integer")
     */
    private $facPaint;

    /**
     * @var int
     *
     * @ORM\Column(name="uvCuredId", type="integer")
     */
    private $uvCuredId;


    /**
     * @var int
     *
     * @ORM\Column(name="uvColorId", type="integer")
     */
    private $uvColorId;

    /**
     * @var int
     *
     * @ORM\Column(name="sheenId", type="integer")
     */
    private $sheenId;

    /**
     * @var int
     *
     * @ORM\Column(name="shameOnId", type="integer", options={"default":0})
     */
    private $shameOnId;

    /**
     * @var int
     *
     * @ORM\Column(name="coreSameOnbe", type="integer", options={"default":0})
     */
    private $coreSameOnbe;

    /**
     * @var int
     *
     * @ORM\Column(name="coreSameOnte", type="integer", options={"default":0})
     */
    private $coreSameOnte;

    /**
     * @var int
     *
     * @ORM\Column(name="coreSameOnre", type="integer", options={"default":0})
     */
    private $coreSameOnre;

    /**
     * @var int
     *
     * @ORM\Column(name="coreSameOnle", type="integer", options={"default":0})
     */
    private $coreSameOnle;
    /**
     * @var bool
     *
     * @ORM\Column(name="edgeDetail", type="boolean")
     */
    private $edgeDetail;

    /**
     * @var int
     *
     * @ORM\Column(name="edgeSameOnB", type="integer", options={"default":0})
     */
    private $edgeSameOnB;

    /**
     * @var int
     *
     * @ORM\Column(name="edgeSameOnR", type="integer", options={"default":0})
     */
    private $edgeSameOnR;

    /**
     * @var int
     *
     * @ORM\Column(name="edgeSameOnL", type="integer", options={"default":0})
     */
    private $edgeSameOnL;

    /**
     * @return int
     */
    public function getEdgeSameOnB()
    {
        return $this->edgeSameOnB;
    }

    /**
     * @param int $edgeSameOnB
     */
    public function setEdgeSameOnB($edgeSameOnB)
    {
        $this->edgeSameOnB = $edgeSameOnB;
    }

    /**
     * @return int
     */
    public function getEdgeSameOnR()
    {
        return $this->edgeSameOnR;
    }

    /**
     * @param int $edgeSameOnR
     */
    public function setEdgeSameOnR($edgeSameOnR)
    {
        $this->edgeSameOnR = $edgeSameOnR;
    }

    /**
     * @return int
     */
    public function getEdgeSameOnL()
    {
        return $this->edgeSameOnL;
    }

    /**
     * @param int $edgeSameOnL
     */
    public function setEdgeSameOnL($edgeSameOnL)
    {
        $this->edgeSameOnL = $edgeSameOnL;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="topEdge", type="integer")
     */
    private $topEdge;

    /**
     * @var int
     *
     * @ORM\Column(name="edgeMaterialId", type="integer")
     */
    private $edgeMaterialId;

    /**
     * @var int
     *
     * @ORM\Column(name="edgeFinishSpeciesId", type="integer")
     */
    private $edgeFinishSpeciesId;

    /**
     * @var int
     *
     * @ORM\Column(name="bottomEdge", type="integer")
     */
    private $bottomEdge;

    /**
     * @var int
     *
     * @ORM\Column(name="bedgeMaterialId", type="integer")
     */
    private $bedgeMaterialId;

    /**
     * @var int
     *
     * @ORM\Column(name="bedgeFinishSpeciesId", type="integer")
     */
    private $bedgeFinishSpeciesId;

    /**
     * @var int
     *
     * @ORM\Column(name="rightEdge", type="integer")
     */
    private $rightEdge;

    /**
     * @var int
     *
     * @ORM\Column(name="redgeMaterialId", type="integer")
     */
    private $redgeMaterialId;

    /**
     * @var int
     *
     * @ORM\Column(name="redgeFinishSpeciesId", type="integer")
     */
    private $redgeFinishSpeciesId;

    /**
     * @var int
     *
     * @ORM\Column(name="leftEdge", type="integer")
     */
    private $leftEdge;

    /**
     * @var int
     *
     * @ORM\Column(name="ledgeMaterialId", type="integer")
     */
    private $ledgeMaterialId;

    /**
     * @var int
     *
     * @ORM\Column(name="ledgeFinishSpeciesId", type="integer")
     */
    private $ledgeFinishSpeciesId;

    /**
     * @var bool
     *
     * @ORM\Column(name="milling", type="boolean")
     */
    private $milling;

    /**
     * @var array
     *
     * @ORM\Column(name="millingDescription", type="text" ,)
     */
    private $millingDescription;

    /**
     * @var int
     *
     * @ORM\Column(name="unitMesureCostId", type="integer")
     */
    private $unitMesureCostId;

    /**
     * @var bool
     *
     * @ORM\Column(name="running", type="boolean")
     */
    private $running;

    /**
     * @var array
     *
     * @ORM\Column(name="runningDescription", type="text" ,)
     */
    private $runningDescription;

    /**
     * @var int
     *
     * @ORM\Column(name="unitMesureCostIdR", type="integer")
     */
    private $unitMesureCostIdR;

    /**
     * @var bool
     *
     * @ORM\Column(name="isLabels", type="boolean")
     */
    private $isLabels;

    /**
     * @var int
     *
     * @ORM\Column(name="numberLabels", type="integer")
     */
    private $numberLabels;

    /**
     * @var string
     *
     * @ORM\Column(name="auto_number", type="string", length=255)
     */
    private $autoNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="lumberFee", type="string", length=255)
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
     * @ORM\Column(type="integer",name="file_id")
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
     * @var string
     *
     * @ORM\Column(name="cust_markup_percent", type="string", length=10, nullable=true, options={"default":25})
     */
    private $custMarkupPer;

    /**
     * @var string
     *
     * @ORM\Column(name="ven_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $venCost;

    /**
     * @var string
     *
     * @ORM\Column(name="ven_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $venWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_ven", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalVen;

    /**
     * @var string
     *
     * @ORM\Column(name="core_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $coreCost;

    /**
     * @var string
     *
     * @ORM\Column(name="core_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $coreWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_core", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalCore;

    /**
     * @var string
     *
     * @ORM\Column(name="backr_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $backrCost;

    /**
     * @var string
     *
     * @ORM\Column(name="backr_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $backrWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_backr", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalBackr;

    /**
     * @var string
     *
     * @ORM\Column(name="finish_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $finishCost;

    /**
     * @var string
     *
     * @ORM\Column(name="finish_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $finishWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_finish", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalFinish;

    /**
     * @var string
     *
     * @ORM\Column(name="edgeint_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $edgeintCost;

    /**
     * @var string
     *
     * @ORM\Column(name="edgeint_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $edgeintWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_edgeint", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalEdgeint;

    /**
     * @var string
     *
     * @ORM\Column(name="edgev_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $edgevCost;

    /**
     * @var string
     *
     * @ORM\Column(name="edgev_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $edgevWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_edgev", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalEdgev;

    /**
     * @var string
     *
     * @ORM\Column(name="finishedge_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $finishEdgeCost;

    /**
     * @var string
     *
     * @ORM\Column(name="finishedge_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $finishEdgeWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_finishedge", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalFinishEdge;

    /**
     * @var string
     *
     * @ORM\Column(name="milling_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $millingCost;

    /**
     * @var string
     *
     * @ORM\Column(name="milling_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $millingWaste;

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_milling", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalMilling;

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
     * @ORM\Column(name="total_cost_per_piece", type="string", length=10, nullable=true, options={"default":0})
     */
    private $totalcostPerPiece;

    /**
     * @var string
     *
     * @ORM\Column(name="markup", type="string", length=10, nullable=true, options={"default":0})
     */
    private $markup;

    /**
     * @var string
     *
     * @ORM\Column(name="selling_price", type="string", length=10, nullable=true, options={"default":0})
     */
    private $sellingPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="lineitem_total", type="string", length=10, nullable=true, options={"default":0})
     */
    private $lineitemTotal;

    /**
     * @var string
     *
     * @ORM\Column(name="machine_setup", type="string", length=10, nullable=true, options={"default":0})
     */
    private $machineSetup;

    /**
     * @var string
     *
     * @ORM\Column(name="machine_tooling", type="string", length=10, nullable=true, options={"default":0})
     */
    private $machineTooling;

    /**
     * @var string
     *
     * @ORM\Column(name="pre_finish_setup", type="string", length=10, nullable=true, options={"default":0})
     */
    private $preFinishSetup;

    /**
     * @var string
     *
     * @ORM\Column(name="color_match", type="string", length=10, nullable=true, options={"default":0})
     */
    private $colorMatch;

    /**
     * @var string
     *
     * @ORM\Column(name="total_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $totalCost;

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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Plywood
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
     * Set speciesId
     *
     * @param integer $speciesId
     *
     * @return Plywood
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
     * @return Plywood
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
     * Set patternId
     *
     * @param integer $patternId
     *
     * @return Plywood
     */
    public function setPatternId($patternId)
    {
        $this->patternId = $patternId;

        return $this;
    }

    /**
     * Get patternId
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
     * @param integer $grainDirectionId
     *
     * @return Plywood
     */
    public function setGrainDirectionId($grainDirectionId)
    {
        $this->grainDirectionId = $grainDirectionId;

        return $this;
    }

    /**
     * Get grainDirectionId
     *
     * @return int
     */
    public function getGrainDirectionId()
    {
        return $this->grainDirectionId;
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
     * Set gradeId
     *
     * @param integer $gradeId
     *
     * @return Plywood
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
     * @return Plywood
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
     * Set plywoodWidth
     *
     * @param float $plywoodWidth
     *
     * @return Plywood
     */
    public function setPlywoodWidth($plywoodWidth)
    {
        $this->plywoodWidth = $plywoodWidth;

        return $this;
    }

    /**
     * Get plywoodWidth
     *
     * @return float
     */
    public function getPlywoodWidth()
    {
        return $this->plywoodWidth;
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
     * Set plywoodLength
     *
     * @param float $plywoodLength
     *
     * @return Plywood
     */
    public function setPlywoodLength($plywoodLength)
    {
        $this->plywoodLength = $plywoodLength;

        return $this;
    }

    /**
     * Get plywoodLength
     *
     * @return float
     */
    public function getPlywoodLength()
    {
        return $this->plywoodLength;
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
     * Set finishThickId
     *
     * @param integer $finishThickId
     *
     * @return Plywood
     */
    public function setFinishThickId($finishThickId)
    {
        $this->finishThickId = $finishThickId;

        return $this;
    }

    /**
     * Get finishThickId
     *
     * @return int
     */
    public function getFinishThickId()
    {
        return $this->finishThickId;
    }

    /**
     * @return string
     */
    public function getFinishThickType()
    {
        return $this->finishThickType;
    }

    /**
     * @param string $finishThickType
     */
    public function setFinishThickType($finishThickType)
    {
        $this->finishThickType = $finishThickType;
    }

    /**
     * Set backerId
     *
     * @param integer $backerId
     *
     * @return Plywood
     */
    public function setBackerId($backerId)
    {
        $this->backerId = $backerId;

        return $this;
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
     * Set isSequenced
     *
     * @param boolean $isSequenced
     *
     * @return Plywood
     */
    public function setIsSequenced($isSequenced)
    {
        $this->isSequenced = $isSequenced;

        return $this;
    }

    /**
     * Get isSequenced
     *
     * @return bool
     */
    public function getIsSequenced()
    {
        return $this->isSequenced;
    }

    /**
     * Set coreType
     *
     * @param integer $coreType
     *
     * @return Plywood
     */
    public function setCoreType($coreType)
    {
        $this->coreType = $coreType;

        return $this;
    }

    /**
     * Get coreType
     *
     * @return int
     */
    public function getCoreType()
    {
        return $this->coreType;
    }

    /**
     * Set thickness
     *
     * @param string $thickness
     *
     * @return Plywood
     */
    public function setThickness($thickness)
    {
        $this->thickness = $thickness;

        return $this;
    }

    /**
     * Get thickness
     *
     * @return string
     */
    public function getThickness()
    {
        return $this->thickness;
    }

    /**
     * Set finish
     *
     * @param string $finish
     *
     * @return Plywood
     */
    public function setFinish($finish)
    {
        $this->finish = $finish;

        return $this;
    }

    /**
     * Get finish
     *
     * @return string
     */
    public function getFinish()
    {
        return $this->finish;
    }

    /**
     * @return string
     */
    public function getFacPaint()
    {
        return $this->facPaint;
    }

    /**
     * @param string $facPaint
     */
    public function setFacPaint($facPaint)
    {
        $this->facPaint = $facPaint;
    }

    /**
     * Set uvCuredId
     *
     * @param integer $uvCuredId
     *
     * @return Plywood
     */
    public function setUvCuredId($uvCuredId)
    {
        $this->uvCuredId = $uvCuredId;

        return $this;
    }

    /**
     * Get uvCuredId
     *
     * @return int
     */
    public function getUvCuredId()
    {
        return $this->uvCuredId;
    }

    /**
     * Set uvColorId
     *
     * @param integer $uvColorId
     *
     * @return Plywood
     */
    public function setUvColorId($uvColorId)
    {
        $this->uvColorId = $uvColorId;

        return $this;
    }

    /**
     * Get uvColorId
     *
     * @return int
     */
    public function getUvColorId()
    {
        return $this->uvColorId;
    }

    /**
     * Set sheenId
     *
     * @param integer $sheenId
     *
     * @return Plywood
     */
    public function setSheenId($sheenId)
    {
        $this->sheenId = $sheenId;

        return $this;
    }

    /**
     * Get sheenId
     *
     * @return int
     */
    public function getSheenId()
    {
        return $this->sheenId;
    }

    /**
     * Set shameOnId
     *
     * @param integer $shameOnId
     *
     * @return Plywood
     */
    public function setShameOnId($shameOnId)
    {
        $this->shameOnId = $shameOnId;

        return $this;
    }

    /**
     * Get shameOnId
     *
     * @return int
     */
    public function getShameOnId()
    {
        return $this->shameOnId;
    }

    /**
     * Set coreSameOnbe
     *
     * @param integer $coreSameOnbe
     *
     * @return Plywood
     */
    public function setCoreSameOnbe($coreSameOnbe)
    {
        $this->coreSameOnbe = $coreSameOnbe;

        return $this;
    }

    /**
     * Get coreSameOnbe
     *
     * @return int
     */
    public function getCoreSameOnbe()
    {
        return $this->coreSameOnbe;
    }


    /**
     * Set coreSameOnte
     *
     * @param integer $coreSameOnte
     *
     * @return Plywood
     */
    public function setCoreSameOnte($coreSameOnte)
    {
        $this->coreSameOnte = $coreSameOnte;

        return $this;
    }

    /**
     * Get coreSameOnte
     *
     * @return int
     */
    public function getCoreSameOnte()
    {
        return $this->coreSameOnte;
    }

    /**
     * Set coreSameOnre
     *
     * @param integer $coreSameOnre
     *
     * @return Plywood
     */
    public function setCoreSameOnre($coreSameOnre)
    {
        $this->coreSameOnre = $coreSameOnre;

        return $this;
    }

    /**
     * Get coreSameOnre
     *
     * @return int
     */
    public function getCoreSameOnre()
    {
        return $this->coreSameOnre;
    }


    /**
     * Set coreSameOnle
     *
     * @param integer $coreSameOnle
     *
     * @return Plywood
     */
    public function setCoreSameOnle($coreSameOnle)
    {
        $this->coreSameOnle = $coreSameOnle;
        return $this;
    }

    /**
     * Get coreSameOnle
     *
     * @return int
     */
    public function getCoreSameOnle()
    {
        return $this->coreSameOnle;
    }

    /**
     * Set edgeDetail
     *
     * @param boolean $edgeDetail
     *
     * @return Plywood
     */
    public function setEdgeDetail($edgeDetail)
    {
        $this->edgeDetail = $edgeDetail;

        return $this;
    }

    /**
     * Get edgeDetail
     *
     * @return bool
     */
    public function getEdgeDetail()
    {
        return $this->edgeDetail;
    }

    /**
     * Set topEdge
     *
     * @param integer $topEdge
     *
     * @return Plywood
     */
    public function setTopEdge($topEdge)
    {
        $this->topEdge = $topEdge;

        return $this;
    }

    /**
     * Get topEdge
     *
     * @return int
     */
    public function getTopEdge()
    {
        return $this->topEdge;
    }

    /**
     * Set bottomEdge
     *
     * @param integer $bottomEdge
     *
     * @return Plywood
     */
    public function setBottomEdge($bottomEdge)
    {
        $this->bottomEdge = $bottomEdge;

        return $this;
    }

    /**
     * Get bottomEdge
     *
     * @return int
     */
    public function getBottomEdge()
    {
        return $this->bottomEdge;
    }

    /**
     * Set rightEdge
     *
     * @param integer $rightEdge
     *
     * @return Plywood
     */
    public function setRightEdge($rightEdge)
    {
        $this->rightEdge = $rightEdge;

        return $this;
    }

    /**
     * Get rightEdge
     *
     * @return int
     */
    public function getRightEdge()
    {
        return $this->rightEdge;
    }

    /**
     * Set leftEdge
     *
     * @param integer $leftEdge
     *
     * @return Plywood
     */
    public function setLeftEdge($leftEdge)
    {
        $this->leftEdge = $leftEdge;

        return $this;
    }

    /**
     * Get leftEdge
     *
     * @return int
     */
    public function getLeftEdge()
    {
        return $this->leftEdge;
    }

    /**
     * Set edgeMaterialId
     *
     * @param integer $edgeMaterialId
     *
     * @return Plywood
     */
    public function setEdgeMaterialId($edgeMaterialId)
    {
        $this->edgeMaterialId = $edgeMaterialId;

        return $this;
    }

    /**
     * Get edgeMaterialId
     *
     * @return int
     */
    public function getEdgeMaterialId()
    {
        return $this->edgeMaterialId;
    }

    /**
     * Set bedgeMaterialId
     *
     * @param integer $bedgeMaterialId
     *
     * @return Plywood
     */
    public function setBedgeMaterialId($bedgeMaterialId)
    {
        $this->bedgeMaterialId = $bedgeMaterialId;

        return $this;
    }

    /**
     * Get bedgeMaterialId
     *
     * @return int
     */
    public function getBedgeMaterialId()
    {
        return $this->bedgeMaterialId;
    }

    /**
     * Set redgeMaterialId
     *
     * @param integer $redgeMaterialId
     *
     * @return Plywood
     */
    public function setRedgeMaterialId($redgeMaterialId)
    {
        $this->redgeMaterialId = $redgeMaterialId;

        return $this;
    }

    /**
     * Get redgeMaterialId
     *
     * @return int
     */
    public function getRedgeMaterialId()
    {
        return $this->redgeMaterialId;
    }

    /**
     * Set ledgeMaterialId
     *
     * @param integer $ledgeMaterialId
     *
     * @return Plywood
     */
    public function setLedgeMaterialId($ledgeMaterialId)
    {
        $this->ledgeMaterialId = $ledgeMaterialId;

        return $this;
    }

    /**
     * Get ledgeMaterialId
     *
     * @return int
     */
    public function getLedgeMaterialId()
    {
        return $this->ledgeMaterialId;
    }

    /**
     * Set edgeFinishSpeciesId
     *
     * @param integer $edgeFinishSpeciesId
     *
     * @return Plywood
     */
    public function setEdgeFinishSpeciesId($edgeFinishSpeciesId)
    {
        $this->edgeFinishSpeciesId = $edgeFinishSpeciesId;

        return $this;
    }

    /**
     * Get edgeFinishSpeciesId
     *
     * @return int
     */
    public function getEdgeFinishSpeciesId()
    {
        return $this->edgeFinishSpeciesId;
    }

    /**
     * Set bedgeFinishSpeciesId
     *
     * @param integer $bedgeFinishSpeciesId
     *
     * @return Plywood
     */
    public function setBedgeFinishSpeciesId($bedgeFinishSpeciesId)
    {
        $this->bedgeFinishSpeciesId = $bedgeFinishSpeciesId;

        return $this;
    }

    /**
     * Get bedgeFinishSpeciesId
     *
     * @return int
     */
    public function getBedgeFinishSpeciesId()
    {
        return $this->bedgeFinishSpeciesId;
    }

    /**
     * Set redgeFinishSpeciesId
     *
     * @param integer $redgeFinishSpeciesId
     *
     * @return Plywood
     */
    public function setRedgeFinishSpeciesId($redgeFinishSpeciesId)
    {
        $this->redgeFinishSpeciesId = $redgeFinishSpeciesId;

        return $this;
    }

    /**
     * Get redgeFinishSpeciesId
     *
     * @return int
     */
    public function getRedgeFinishSpeciesId()
    {
        return $this->redgeFinishSpeciesId;
    }

    /**
     * Set ledgeFinishSpeciesId
     *
     * @param integer $ledgeFinishSpeciesId
     *
     * @return Plywood
     */
    public function setLedgeFinishSpeciesId($ledgeFinishSpeciesId)
    {
        $this->ledgeFinishSpeciesId = $ledgeFinishSpeciesId;

        return $this;
    }

    /**
     * Get ledgeFinishSpeciesId
     *
     * @return int
     */
    public function getLedgeFinishSpeciesId()
    {
        return $this->ledgeFinishSpeciesId;
    }

    /**
     * Set milling
     *
     * @param boolean $milling
     *
     * @return Plywood
     */
    public function setMilling($milling)
    {
        $this->milling = $milling;

        return $this;
    }

    /**
     * Get milling
     *
     * @return bool
     */
    public function getMilling()
    {
        return $this->milling;
    }

    /**
     * Set millingDescription
     *
     * @param string $millingDescription
     *
     * @return Plywood
     */
    public function setMillingDescription($millingDescription)
    {
        $this->millingDescription = $millingDescription;

        return $this;
    }

    /**
     * Get millingDescription
     *
     * @return string
     */
    public function getMillingDescription()
    {
        return $this->millingDescription;
    }

    /**
     * Set unitMesureCostId
     *
     * @param integer $unitMesureCostId
     *
     * @return Plywood
     */
    public function setUnitMesureCostId($unitMesureCostId)
    {
        $this->unitMesureCostId = $unitMesureCostId;

        return $this;
    }

    /**
     * Get unitMesureCostId
     *
     * @return int
     */
    public function getUnitMesureCostId()
    {
        return $this->unitMesureCostId;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->running;
    }

    /**
     * @param bool $running
     */
    public function setRunning($running)
    {
        $this->running = $running;
    }

    /**
     * @return array
     */
    public function getRunningDescription()
    {
        return $this->runningDescription;
    }

    /**
     * @param array $runningDescription
     */
    public function setRunningDescription($runningDescription)
    {
        $this->runningDescription = $runningDescription;
    }

    /**
     * @return int
     */
    public function getUnitMesureCostIdR()
    {
        return $this->unitMesureCostIdR;
    }

    /**
     * @param int $unitMesureCostIdR
     */
    public function setUnitMesureCostIdR($unitMesureCostIdR)
    {
        $this->unitMesureCostIdR = $unitMesureCostIdR;
    }

    /**
     * Set isLabels
     *
     * @param boolean $isLabels
     *
     * @return Plywood
     */
    public function setIsLabels($isLabels)
    {
        $this->isLabels = $isLabels;

        return $this;
    }

    /**
     * Get isLabels
     *
     * @return bool
     */
    public function getIsLabels()
    {
        return $this->isLabels;
    }

    /**
     * Set numberLabels
     *
     * @param integer $numberLabels
     *
     * @return Plywood
     */
    public function setNumberLabels($numberLabels)
    {
        $this->numberLabels = $numberLabels;

        return $this;
    }

    /**
     * Get numberLabels
     *
     * @return int
     */
    public function getNumberLabels()
    {
        return $this->numberLabels;
    }

    /**
     * Set autoNumber
     *
     * @param string $autoNumber
     *
     * @return Plywood
     */
    public function setAutoNumber($autoNumber)
    {
        $this->autoNumber = $autoNumber;

        return $this;
    }

    /**
     * Get autoNumber
     *
     * @return string
     */
    public function getAutoNumber()
    {
        return $this->autoNumber;
    }

    /**
     * Set lumberFee
     *
     * @param string $lumberFee
     *
     * @return Plywood
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
     * @return Plywood
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
    public function getFinishCost()
    {
        return $this->finishCost;
    }

    /**
     * @param string $finishCost
     */
    public function setFinishCost($finishCost)
    {
        $this->finishCost = $finishCost;
    }

    /**
     * @return string
     */
    public function getFinishWaste()
    {
        return $this->finishWaste;
    }

    /**
     * @param string $finishWaste
     */
    public function setFinishWaste($finishWaste)
    {
        $this->finishWaste = $finishWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalFinish()
    {
        return $this->subTotalFinish;
    }

    /**
     * @param string $subTotalFinish
     */
    public function setSubTotalFinish($subTotalFinish)
    {
        $this->subTotalFinish = $subTotalFinish;
    }

    /**
     * @return string
     */
    public function getEdgeintCost()
    {
        return $this->edgeintCost;
    }

    /**
     * @param string $edgeintCost
     */
    public function setEdgeintCost($edgeintCost)
    {
        $this->edgeintCost = $edgeintCost;
    }

    /**
     * @return string
     */
    public function getEdgeintWaste()
    {
        return $this->edgeintWaste;
    }

    /**
     * @param string $edgeintWaste
     */
    public function setEdgeintWaste($edgeintWaste)
    {
        $this->edgeintWaste = $edgeintWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalEdgeint()
    {
        return $this->subTotalEdgeint;
    }

    /**
     * @param string $subTotalEdgeint
     */
    public function setSubTotalEdgeint($subTotalEdgeint)
    {
        $this->subTotalEdgeint = $subTotalEdgeint;
    }

    /**
     * @return string
     */
    public function getEdgevCost()
    {
        return $this->edgevCost;
    }

    /**
     * @param string $edgevCost
     */
    public function setEdgevCost($edgevCost)
    {
        $this->edgevCost = $edgevCost;
    }

    /**
     * @return string
     */
    public function getEdgevWaste()
    {
        return $this->edgevWaste;
    }

    /**
     * @param string $edgevWaste
     */
    public function setEdgevWaste($edgevWaste)
    {
        $this->edgevWaste = $edgevWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalEdgev()
    {
        return $this->subTotalEdgev;
    }

    /**
     * @param string $subTotalEdgev
     */
    public function setSubTotalEdgev($subTotalEdgev)
    {
        $this->subTotalEdgev = $subTotalEdgev;
    }

    /**
     * @return string
     */
    public function getFinishEdgeCost()
    {
        return $this->finishEdgeCost;
    }

    /**
     * @param string $finishEdgeCost
     */
    public function setFinishEdgeCost($finishEdgeCost)
    {
        $this->finishEdgeCost = $finishEdgeCost;
    }

    /**
     * @return string
     */
    public function getFinishEdgeWaste()
    {
        return $this->finishEdgeWaste;
    }

    /**
     * @param string $finishEdgeWaste
     */
    public function setFinishEdgeWaste($finishEdgeWaste)
    {
        $this->finishEdgeWaste = $finishEdgeWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalFinishEdge()
    {
        return $this->subTotalFinishEdge;
    }

    /**
     * @param string $subTotalFinishEdge
     */
    public function setSubTotalFinishEdge($subTotalFinishEdge)
    {
        $this->subTotalFinishEdge = $subTotalFinishEdge;
    }

    /**
     * @return string
     */
    public function getMillingCost()
    {
        return $this->millingCost;
    }

    /**
     * @param string $millingCost
     */
    public function setMillingCost($millingCost)
    {
        $this->millingCost = $millingCost;
    }

    /**
     * @return string
     */
    public function getMillingWaste()
    {
        return $this->millingWaste;
    }

    /**
     * @param string $millingWaste
     */
    public function setMillingWaste($millingWaste)
    {
        $this->millingWaste = $millingWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalMilling()
    {
        return $this->subTotalMilling;
    }

    /**
     * @param string $subTotalMilling
     */
    public function setSubTotalMilling($subTotalMilling)
    {
        $this->subTotalMilling = $subTotalMilling;
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
    public function getTotalcostPerPiece()
    {
        return $this->totalcostPerPiece;
    }

    /**
     * @param string $totalcostPerPiece
     */
    public function setTotalcostPerPiece($totalcostPerPiece)
    {
        $this->totalcostPerPiece = $totalcostPerPiece;
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


}