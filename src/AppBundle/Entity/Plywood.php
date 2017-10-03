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
     * @ORM\Column(name="plywoodLength", type="float")
     */
    private $plywoodLength;

    /**
     * @var int
     *
     * @ORM\Column(name="finishThickId", type="integer")
     */
    private $finishThickId;

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
     * @var int
     *
     * @ORM\Column(name="uvCuredId", type="integer")
     */
    private $uvCuredId;

    /**
     * @var int
     *
     * @ORM\Column(name="sheenId", type="integer")
     */
    private $sheenId;

    /**
     * @var int
     *
     * @ORM\Column(name="shameOnId", type="integer")
     */
    private $shameOnId;

    /**
     * @var bool
     *
     * @ORM\Column(name="edgeDetail", type="boolean")
     */
    private $edgeDetail;

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
     * @var float
     *
     * @ORM\Column(name="cost", type="float")
     */
    private $cost;

    /**
     * @var int
     *
     * @ORM\Column(name="unitMesureCostId", type="integer")
     */
    private $unitMesureCostId;

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
     * Set cost
     *
     * @param float $cost
     *
     * @return Plywood
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
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
 }
