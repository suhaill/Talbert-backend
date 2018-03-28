<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Doors
 *
 * @ORM\Table(name="doors")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DoorsRepository")
 */
class Doors
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
     * @ORM\Column(name="qty", type="string", length=255)
     */
    private $qty;

    /**
     * @var bool
     *
     * @ORM\Column(name="pair", type="boolean")
     */
    private $pair;

    /**
     * @var string
     *
     * @ORM\Column(name="swing", type="string", length=5)
     */
    private $swing;

    /**
     * @var string
     *
     * @ORM\Column(name="width", type="string", length=10)
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
     * @var string
     *
     * @ORM\Column(name="length", type="string", length=10)
     */
    private $length;

    /**
     * @var float
     *
     * @ORM\Column(name="lengthFraction", type="float")
     */
    private $lengthFraction;

    /**
     * @var string
     *
     * @ORM\Column(name="thickness", type="string", length=10)
     */
    private $thickness;

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
     * @var string
     *
     * @ORM\Column(name="panelThickness", type="string", length=100)
     */
    private $panelThickness;

    /**
     * @var string
     *
     * @ORM\Column(name="door_use", type="string", length=20)
     */
    private $doorUse;

    /**
     * @var string
     *
     * @ORM\Column(name="construction", type="string", length=100)
     */
    private $construction;

    /**
     * @var string
     *
     * @ORM\Column(name="fire_rating", type="string", length=100)
     */
    private $fireRating;

    /**
     * @var string
     *
     * @ORM\Column(name="door_core", type="string", length=100)
     */
    private $doorCore;

    /**
     * @var string
     *
     * @ORM\Column(name="sequence", type="string", length=10)
     */
    private $sequence;

    /**
     * @var bool
     *
     * @ORM\Column(name="sound", type="boolean")
     */
    private $sound;

    /**
     * @var string
     *
     * @ORM\Column(name="sound_drop", type="string", length=5)
     */
    private $soundDrop;

    /**
     * @var string
     *
     * @ORM\Column(name="specification", type="string", length=200)
     */
    private $specification;

    /**
     * @var bool
     *
     * @ORM\Column(name="louvers", type="boolean")
     */
    private $louvers;

    /**
     * @var string
     *
     * @ORM\Column(name="louvers_drop", type="string", length=15)
     */
    private $louversDrop;

    /**
     * @var bool
     *
     * @ORM\Column(name="bevel", type="boolean")
     */
    private $bevel;

    /**
     * @var string
     *
     * @ORM\Column(name="bevel_drop", type="string", length=15)
     */
    private $bevelDrop;

    /**
     * @var bool
     *
     * @ORM\Column(name="edge_finish", type="boolean")
     */
    private $edgeFinish;

    /**
     * @var int
     *
     * @ORM\Column(name="top_edge", type="integer")
     */
    private $topEdge;

    /**
     * @var string
     *
     * @ORM\Column(name="top_edge_material", type="string", length=100, nullable=true)
     */
    private $topEdgeMaterial;

    /**
     * @var int
     *
     * @ORM\Column(name="top_edge_species", type="integer")
     */
    private $topEdgeSpecies;

    /**
     * @var int
     *
     * @ORM\Column(name="bottom_edge", type="integer")
     */
    private $bottomEdge;

    /**
     * @var string
     *
     * @ORM\Column(name="bottom_edge_material", type="string", length=100, nullable=true)
     */
    private $bottomEdgeMaterial;

    /**
     * @var int
     *
     * @ORM\Column(name="bottom_edge_species", type="integer")
     */
    private $bottomEdgeSpecies;

    /**
     * @var int
     *
     * @ORM\Column(name="right_edge", type="integer")
     */
    private $rightEdge;

    /**
     * @var string
     *
     * @ORM\Column(name="r_edge_mat", type="string", length=100, nullable=true)
     */
    private $rEdgeMat;

    /**
     * @var int
     *
     * @ORM\Column(name="e_edge_sp", type="integer")
     */
    private $eEdgeSp;

    /**
     * @var int
     *
     * @ORM\Column(name="left_edge", type="integer")
     */
    private $leftEdge;

    /**
     * @var string
     *
     * @ORM\Column(name="l_edge_mat", type="string", length=100, nullable=true)
     */
    private $lEdgeMat;

    /**
     * @var int
     *
     * @ORM\Column(name="l_edge_sp", type="integer")
     */
    private $lEdgeSp;

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
     * @ORM\Column(name="light_opening", type="boolean")
     */
    private $lightOpening;

    /**
     * @var string
     *
     * @ORM\Column(name="light_op_drop", type="string", length=100)
     */
    private $lightOpDrop;

    /**
     * @var string
     *
     * @ORM\Column(name="location_from_top", type="string", length=100, nullable=true)
     */
    private $locationFromTop;

    /**
     * @var string
     *
     * @ORM\Column(name="loc_from_lockEdge", type="string", length=100, nullable=true)
     */
    private $locFromLockEdge;

    /**
     * @var string
     *
     * @ORM\Column(name="opening_size", type="string", length=100, nullable=true)
     */
    private $openingSize;

    /**
     * @var string
     *
     * @ORM\Column(name="stop_size", type="string", length=100, nullable=true)
     */
    private $stopSize;

    /**
     * @var bool
     *
     * @ORM\Column(name="glass", type="boolean")
     */
    private $glass;

    /**
     * @var string
     *
     * @ORM\Column(name="glass_drop", type="string", length=15)
     */
    private $glassDrop;

    /**
     * @var string
     *
     * @ORM\Column(name="finish", type="string", length=10)
     */
    private $finish;

    /**
     * @var string
     *
     * @ORM\Column(name="fac_paint", type="string", length=30, nullable=true)
     */
    private $facPaint;

    /**
     * @var string
     *
     * @ORM\Column(name="uv_cured", type="string", length=30)
     */
    private $uvCured;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=10)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="sheen", type="string", length=5)
     */
    private $sheen;

    /**
     * @var bool
     *
     * @ORM\Column(name="same_on_back", type="boolean")
     */
    private $sameOnBack;

    /**
     * @var bool
     *
     * @ORM\Column(name="same_on_bottom", type="boolean")
     */
    private $sameOnBottom;

    /**
     * @var bool
     *
     * @ORM\Column(name="same_on_top", type="boolean")
     */
    private $sameOnTop;

    /**
     * @var bool
     *
     * @ORM\Column(name="same_on_right", type="boolean")
     */
    private $sameOnRight;

    /**
     * @var bool
     *
     * @ORM\Column(name="same_on_left", type="boolean")
     */
    private $sameOnLeft;

    /**
     * @var bool
     *
     * @ORM\Column(name="door_frame", type="boolean")
     */
    private $doorFrame;

    /**
     * @var string
     *
     * @ORM\Column(name="door_drop", type="string", length=15)
     */
    private $doorDrop;

    /**
     * @var bool
     *
     * @ORM\Column(name="surface_machning", type="boolean")
     */
    private $surfaceMachning;

    /**
     * @var string
     *
     * @ORM\Column(name="surface_style", type="string", length=20)
     */
    private $surfaceStyle;

    /**
     * @var string
     *
     * @ORM\Column(name="surface_depth", type="string", length=20)
     */
    private $surfaceDepth;

    /**
     * @var string
     *
     * @ORM\Column(name="surface_sides", type="string", length=20)
     */
    private $surfaceSides;

    /**
     * @var bool
     *
     * @ORM\Column(name="styles", type="boolean")
     */
    private $styles;

    /**
     * @var string
     *
     * @ORM\Column(name="style_width", type="string", length=100, nullable=true)
     */
    private $styleWidth;

    /**
     * @var bool
     *
     * @ORM\Column(name="machning", type="boolean")
     */
    private $machning;

    /**
     * @var string
     *
     * @ORM\Column(name="hindge_model_no", type="string", length=100, nullable=true)
     */
    private $hindgeModelNo;

    /**
     * @var string
     *
     * @ORM\Column(name="hindge_weight", type="string", length=30)
     */
    private $hindgeWeight;

    /**
     * @var string
     *
     * @ORM\Column(name="pos_from_top", type="string", length=100, nullable=true)
     */
    private $posFromTop;

    /**
     * @var string
     *
     * @ORM\Column(name="hindge_size", type="string", length=20)
     */
    private $hindgeSize;

    /**
     * @var string
     *
     * @ORM\Column(name="back_set", type="string", length=100, nullable=true)
     */
    private $backSet;

    /**
     * @var string
     *
     * @ORM\Column(name="handle_bolt", type="string", length=100, nullable=true)
     */
    private $handleBolt;

    /**
     * @var string
     *
     * @ORM\Column(name="pos_from_top_mach", type="string", length=100, nullable=true)
     */
    private $posFromTopMach;

    /**
     * @var string
     *
     * @ORM\Column(name="vertical_rod", type="string", length=15)
     */
    private $verticalRod;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_label", type="boolean")
     */
    private $isLabel;

    /**
     * @var string
     *
     * @ORM\Column(name="labels", type="string", length=600, nullable=true)
     */
    private $labels;

    /**
     * @var string
     *
     * @ORM\Column(name="auto_number", type="string", length=255)
     */
    private $autoNumber;

    /**
     * @return string
     */
    public function getAutoNumber()
    {
        return $this->autoNumber;
    }

    /**
     * @param string $autoNumber
     */
    public function setAutoNumber($autoNumber)
    {
        $this->autoNumber = $autoNumber;
    }

    /**
     * @var bool
     *
     * @ORM\Column(name="face_preps", type="boolean")
     */
    private $facePreps;

    /**
     * @var bool
     *
     * @ORM\Column(name="blocking_charge", type="boolean")
     */
    private $blockingCharge;
    
    
    /**
     * @var string
     *
     * @ORM\Column(name="blocking_upcharge", type="string", length=200, nullable=true)
     */
    private $blockingUpcharge;

    /**
     * @var string
     *
     * @ORM\Column(name="lum_fee", type="string", length=10, nullable=true)
     */
    private $lumFee;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=1000, nullable=true)
     */
    private $comment;

    /**
     * @var bool
     *
     * @ORM\Column(name="status", type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;
    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;
    
    /**
     * @var int
     *
     * @ORM\Column(name="coreType", type="integer")
     */
    private $coreType;

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
     * Set quoteId
     *
     * @param integer $quoteId
     *
     * @return Doors
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
     * Set qty
     *
     * @param string $qty
     *
     * @return Doors
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty
     *
     * @return string
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set pair
     *
     * @param boolean $pair
     *
     * @return Doors
     */
    public function setPair($pair)
    {
        $this->pair = $pair;

        return $this;
    }

    /**
     * Get pair
     *
     * @return bool
     */
    public function getPair()
    {
        return $this->pair;
    }

    /**
     * Set swing
     *
     * @param string $swing
     *
     * @return Doors
     */
    public function setSwing($swing)
    {
        $this->swing = $swing;

        return $this;
    }

    /**
     * Get swing
     *
     * @return string
     */
    public function getSwing()
    {
        return $this->swing;
    }

    /**
     * Set width
     *
     * @param string $width
     *
     * @return Doors
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return string
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
     * @return bool
     */
    public function isNetSize()
    {
        return $this->isNetSize;
    }

    /**
     * @param bool $isNetSize
     */
    public function setIsNetSize($isNetSize)
    {
        $this->isNetSize = $isNetSize;
    }

    /**
     * Set length
     *
     * @param string $length
     *
     * @return Doors
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return string
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
     * Set thickness
     *
     * @param string $thickness
     *
     * @return Doors
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
     * @return int
     */
    public function getFinishThickId()
    {
        return $this->finishThickId;
    }

    /**
     * @param int $finishThickId
     */
    public function setFinishThickId($finishThickId)
    {
        $this->finishThickId = $finishThickId;
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
     * @return string
     */
    public function getPanelThickness()
    {
        return $this->panelThickness;
    }

    /**
     * @param string $panelThickness
     */
    public function setPanelThickness($panelThickness)
    {
        $this->panelThickness = $panelThickness;
    }

    /**
     * Set doorUse
     *
     * @param string $doorUse
     *
     * @return Doors
     */
    public function setDoorUse($doorUse)
    {
        $this->doorUse = $doorUse;

        return $this;
    }

    /**
     * Get doorUse
     *
     * @return string
     */
    public function getDoorUse()
    {
        return $this->doorUse;
    }

    /**
     * Set construction
     *
     * @param string $construction
     *
     * @return Doors
     */
    public function setConstruction($construction)
    {
        $this->construction = $construction;

        return $this;
    }

    /**
     * Get construction
     *
     * @return string
     */
    public function getConstruction()
    {
        return $this->construction;
    }

    /**
     * Set fireRating
     *
     * @param string $fireRating
     *
     * @return Doors
     */
    public function setFireRating($fireRating)
    {
        $this->fireRating = $fireRating;

        return $this;
    }

    /**
     * Get fireRating
     *
     * @return string
     */
    public function getFireRating()
    {
        return $this->fireRating;
    }

    /**
     * Set doorCore
     *
     * @param string $doorCore
     *
     * @return Doors
     */
    public function setDoorCore($doorCore)
    {
        $this->doorCore = $doorCore;

        return $this;
    }

    /**
     * Get doorCore
     *
     * @return string
     */
    public function getDoorCore()
    {
        return $this->doorCore;
    }

    /**
     * Set sequence
     *
     * @param string $sequence
     *
     * @return Doors
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Get sequence
     *
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Set sound
     *
     * @param boolean $sound
     *
     * @return Doors
     */
    public function setSound($sound)
    {
        $this->sound = $sound;

        return $this;
    }

    /**
     * Get sound
     *
     * @return bool
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * Set soundDrop
     *
     * @param string $soundDrop
     *
     * @return Doors
     */
    public function setSoundDrop($soundDrop)
    {
        $this->soundDrop = $soundDrop;

        return $this;
    }

    /**
     * Get soundDrop
     *
     * @return string
     */
    public function getSoundDrop()
    {
        return $this->soundDrop;
    }

    /**
     * @return string
     */
    public function getSpecification()
    {
        return $this->specification;
    }

    /**
     * @param string $specification
     */
    public function setSpecification($specification)
    {
        $this->specification = $specification;
    }

    /**
     * Set louvers
     *
     * @param boolean $louvers
     *
     * @return Doors
     */
    public function setLouvers($louvers)
    {
        $this->louvers = $louvers;

        return $this;
    }

    /**
     * Get louvers
     *
     * @return bool
     */
    public function getLouvers()
    {
        return $this->louvers;
    }

    /**
     * Set louversDrop
     *
     * @param string $louversDrop
     *
     * @return Doors
     */
    public function setLouversDrop($louversDrop)
    {
        $this->louversDrop = $louversDrop;

        return $this;
    }

    /**
     * Get louversDrop
     *
     * @return string
     */
    public function getLouversDrop()
    {
        return $this->louversDrop;
    }

    /**
     * Set bevel
     *
     * @param boolean $bevel
     *
     * @return Doors
     */
    public function setBevel($bevel)
    {
        $this->bevel = $bevel;

        return $this;
    }

    /**
     * Get bevel
     *
     * @return bool
     */
    public function getBevel()
    {
        return $this->bevel;
    }

    /**
     * Set bevelDrop
     *
     * @param string $bevelDrop
     *
     * @return Doors
     */
    public function setBevelDrop($bevelDrop)
    {
        $this->bevelDrop = $bevelDrop;

        return $this;
    }

    /**
     * Get bevelDrop
     *
     * @return string
     */
    public function getBevelDrop()
    {
        return $this->bevelDrop;
    }

    /**
     * Set edgeFinish
     *
     * @param boolean $edgeFinish
     *
     * @return Doors
     */
    public function setEdgeFinish($edgeFinish)
    {
        $this->edgeFinish = $edgeFinish;

        return $this;
    }

    /**
     * Get edgeFinish
     *
     * @return bool
     */
    public function getEdgeFinish()
    {
        return $this->edgeFinish;
    }

    /**
     * Set topEdge
     *
     * @param integer $topEdge
     *
     * @return Doors
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
     * Set topEdgeMaterial
     *
     * @param string $topEdgeMaterial
     *
     * @return Doors
     */
    public function setTopEdgeMaterial($topEdgeMaterial)
    {
        $this->topEdgeMaterial = $topEdgeMaterial;

        return $this;
    }

    /**
     * Get topEdgeMaterial
     *
     * @return string
     */
    public function getTopEdgeMaterial()
    {
        return $this->topEdgeMaterial;
    }

    /**
     * Set topEdgeSpecies
     *
     * @param integer $topEdgeSpecies
     *
     * @return Doors
     */
    public function setTopEdgeSpecies($topEdgeSpecies)
    {
        $this->topEdgeSpecies = $topEdgeSpecies;

        return $this;
    }

    /**
     * Get topEdgeSpecies
     *
     * @return int
     */
    public function getTopEdgeSpecies()
    {
        return $this->topEdgeSpecies;
    }

    /**
     * Set bottomEdge
     *
     * @param integer $bottomEdge
     *
     * @return Doors
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
     * Set bottomEdgeMaterial
     *
     * @param string $bottomEdgeMaterial
     *
     * @return Doors
     */
    public function setBottomEdgeMaterial($bottomEdgeMaterial)
    {
        $this->bottomEdgeMaterial = $bottomEdgeMaterial;

        return $this;
    }

    /**
     * Get bottomEdgeMaterial
     *
     * @return string
     */
    public function getBottomEdgeMaterial()
    {
        return $this->bottomEdgeMaterial;
    }

    /**
     * Set bottomEdgeSpecies
     *
     * @param integer $bottomEdgeSpecies
     *
     * @return Doors
     */
    public function setBottomEdgeSpecies($bottomEdgeSpecies)
    {
        $this->bottomEdgeSpecies = $bottomEdgeSpecies;

        return $this;
    }

    /**
     * Get bottomEdgeSpecies
     *
     * @return int
     */
    public function getBottomEdgeSpecies()
    {
        return $this->bottomEdgeSpecies;
    }

    /**
     * Set rightEdge
     *
     * @param integer $rightEdge
     *
     * @return Doors
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
     * Set rEdgeMat
     *
     * @param string $rEdgeMat
     *
     * @return Doors
     */
    public function setREdgeMat($rEdgeMat)
    {
        $this->rEdgeMat = $rEdgeMat;

        return $this;
    }

    /**
     * Get rEdgeMat
     *
     * @return string
     */
    public function getREdgeMat()
    {
        return $this->rEdgeMat;
    }

    /**
     * Set eEdgeSp
     *
     * @param integer $eEdgeSp
     *
     * @return Doors
     */
    public function setEEdgeSp($eEdgeSp)
    {
        $this->eEdgeSp = $eEdgeSp;

        return $this;
    }

    /**
     * Get eEdgeSp
     *
     * @return int
     */
    public function getEEdgeSp()
    {
        return $this->eEdgeSp;
    }

    /**
     * Set leftEdge
     *
     * @param integer $leftEdge
     *
     * @return Doors
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
     * Set lEdgeMat
     *
     * @param string $lEdgeMat
     *
     * @return Doors
     */
    public function setLEdgeMat($lEdgeMat)
    {
        $this->lEdgeMat = $lEdgeMat;

        return $this;
    }

    /**
     * Get lEdgeMat
     *
     * @return string
     */
    public function getLEdgeMat()
    {
        return $this->lEdgeMat;
    }

    /**
     * Set lEdgeSp
     *
     * @param integer $lEdgeSp
     *
     * @return Doors
     */
    public function setLEdgeSp($lEdgeSp)
    {
        $this->lEdgeSp = $lEdgeSp;

        return $this;
    }

    /**
     * Get lEdgeSp
     *
     * @return int
     */
    public function getLEdgeSp()
    {
        return $this->lEdgeSp;
    }

    /**
     * @return bool
     */
    public function isMilling()
    {
        return $this->milling;
    }

    /**
     * @param bool $milling
     */
    public function setMilling($milling)
    {
        $this->milling = $milling;
    }

    /**
     * @return array
     */
    public function getMillingDescription()
    {
        return $this->millingDescription;
    }

    /**
     * @param array $millingDescription
     */
    public function setMillingDescription($millingDescription)
    {
        $this->millingDescription = $millingDescription;
    }

    /**
     * @return int
     */
    public function getUnitMesureCostId()
    {
        return $this->unitMesureCostId;
    }

    /**
     * @param int $unitMesureCostId
     */
    public function setUnitMesureCostId($unitMesureCostId)
    {
        $this->unitMesureCostId = $unitMesureCostId;
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
     * Set lightOpening
     *
     * @param boolean $lightOpening
     *
     * @return Doors
     */
    public function setLightOpening($lightOpening)
    {
        $this->lightOpening = $lightOpening;

        return $this;
    }

    /**
     * Get lightOpening
     *
     * @return bool
     */
    public function getLightOpening()
    {
        return $this->lightOpening;
    }

    /**
     * Set lightOpDrop
     *
     * @param string $lightOpDrop
     *
     * @return Doors
     */
    public function setLightOpDrop($lightOpDrop)
    {
        $this->lightOpDrop = $lightOpDrop;

        return $this;
    }

    /**
     * Get lightOpDrop
     *
     * @return string
     */
    public function getLightOpDrop()
    {
        return $this->lightOpDrop;
    }

    /**
     * Set locationFromTop
     *
     * @param string $locationFromTop
     *
     * @return Doors
     */
    public function setLocationFromTop($locationFromTop)
    {
        $this->locationFromTop = $locationFromTop;

        return $this;
    }

    /**
     * Get locationFromTop
     *
     * @return string
     */
    public function getLocationFromTop()
    {
        return $this->locationFromTop;
    }

    /**
     * Set locFromLockEdge
     *
     * @param string $locFromLockEdge
     *
     * @return Doors
     */
    public function setLocFromLockEdge($locFromLockEdge)
    {
        $this->locFromLockEdge = $locFromLockEdge;

        return $this;
    }

    /**
     * Get locFromLockEdge
     *
     * @return string
     */
    public function getLocFromLockEdge()
    {
        return $this->locFromLockEdge;
    }

    /**
     * Set openingSize
     *
     * @param string $openingSize
     *
     * @return Doors
     */
    public function setOpeningSize($openingSize)
    {
        $this->openingSize = $openingSize;

        return $this;
    }

    /**
     * Get openingSize
     *
     * @return string
     */
    public function getOpeningSize()
    {
        return $this->openingSize;
    }

    /**
     * Set stopSize
     *
     * @param string $stopSize
     *
     * @return Doors
     */
    public function setStopSize($stopSize)
    {
        $this->stopSize = $stopSize;

        return $this;
    }

    /**
     * Get stopSize
     *
     * @return string
     */
    public function getStopSize()
    {
        return $this->stopSize;
    }

    /**
     * Set glass
     *
     * @param boolean $glass
     *
     * @return Doors
     */
    public function setGlass($glass)
    {
        $this->glass = $glass;

        return $this;
    }

    /**
     * Get glass
     *
     * @return bool
     */
    public function getGlass()
    {
        return $this->glass;
    }

    /**
     * Set glassDrop
     *
     * @param string $glassDrop
     *
     * @return Doors
     */
    public function setGlassDrop($glassDrop)
    {
        $this->glassDrop = $glassDrop;

        return $this;
    }

    /**
     * Get glassDrop
     *
     * @return string
     */
    public function getGlassDrop()
    {
        return $this->glassDrop;
    }

    /**
     * Set finish
     *
     * @param string $finish
     *
     * @return Doors
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
     * Set uvCured
     *
     * @param string $uvCured
     *
     * @return Doors
     */
    public function setUvCured($uvCured)
    {
        $this->uvCured = $uvCured;

        return $this;
    }

    /**
     * Get uvCured
     *
     * @return string
     */
    public function getUvCured()
    {
        return $this->uvCured;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * Set sheen
     *
     * @param string $sheen
     *
     * @return Doors
     */
    public function setSheen($sheen)
    {
        $this->sheen = $sheen;

        return $this;
    }

    /**
     * Get sheen
     *
     * @return string
     */
    public function getSheen()
    {
        return $this->sheen;
    }

    /**
     * Set sameOnBack
     *
     * @param boolean $sameOnBack
     *
     * @return Doors
     */
    public function setSameOnBack($sameOnBack)
    {
        $this->sameOnBack = $sameOnBack;

        return $this;
    }

    /**
     * Get sameOnBack
     *
     * @return bool
     */
    public function getSameOnBack()
    {
        return $this->sameOnBack;
    }

    /**
     * Set sameOnBottom
     *
     * @param boolean $sameOnBottom
     *
     * @return Doors
     */
    public function setSameOnBottom($sameOnBottom)
    {
        $this->sameOnBottom = $sameOnBottom;

        return $this;
    }

    /**
     * Get sameOnBottom
     *
     * @return bool
     */
    public function getSameOnBottom()
    {
        return $this->sameOnBottom;
    }

    /**
     * Set sameOnTop
     *
     * @param boolean $sameOnTop
     *
     * @return Doors
     */
    public function setSameOnTop($sameOnTop)
    {
        $this->sameOnTop = $sameOnTop;

        return $this;
    }

    /**
     * Get sameOnTop
     *
     * @return bool
     */
    public function getSameOnTop()
    {
        return $this->sameOnTop;
    }

    /**
     * Set sameOnRight
     *
     * @param boolean $sameOnRight
     *
     * @return Doors
     */
    public function setSameOnRight($sameOnRight)
    {
        $this->sameOnRight = $sameOnRight;

        return $this;
    }

    /**
     * Get sameOnRight
     *
     * @return bool
     */
    public function getSameOnRight()
    {
        return $this->sameOnRight;
    }

    /**
     * Set sameOnLeft
     *
     * @param boolean $sameOnLeft
     *
     * @return Doors
     */
    public function setSameOnLeft($sameOnLeft)
    {
        $this->sameOnLeft = $sameOnLeft;

        return $this;
    }

    /**
     * Get sameOnLeft
     *
     * @return bool
     */
    public function getSameOnLeft()
    {
        return $this->sameOnLeft;
    }

    /**
     * Set doorFrame
     *
     * @param boolean $doorFrame
     *
     * @return Doors
     */
    public function setDoorFrame($doorFrame)
    {
        $this->doorFrame = $doorFrame;

        return $this;
    }

    /**
     * Get doorFrame
     *
     * @return bool
     */
    public function getDoorFrame()
    {
        return $this->doorFrame;
    }

    /**
     * Set doorDrop
     *
     * @param string $doorDrop
     *
     * @return Doors
     */
    public function setDoorDrop($doorDrop)
    {
        $this->doorDrop = $doorDrop;

        return $this;
    }

    /**
     * Get doorDrop
     *
     * @return string
     */
    public function getDoorDrop()
    {
        return $this->doorDrop;
    }

    /**
     * Set surfaceMachning
     *
     * @param boolean $surfaceMachning
     *
     * @return Doors
     */
    public function setSurfaceMachning($surfaceMachning)
    {
        $this->surfaceMachning = $surfaceMachning;

        return $this;
    }

    /**
     * Get surfaceMachning
     *
     * @return bool
     */
    public function getSurfaceMachning()
    {
        return $this->surfaceMachning;
    }

    /**
     * Set surfaceStyle
     *
     * @param string $surfaceStyle
     *
     * @return Doors
     */
    public function setSurfaceStyle($surfaceStyle)
    {
        $this->surfaceStyle = $surfaceStyle;

        return $this;
    }

    /**
     * Get surfaceStyle
     *
     * @return string
     */
    public function getSurfaceStyle()
    {
        return $this->surfaceStyle;
    }

    /**
     * Set surfaceDepth
     *
     * @param string $surfaceDepth
     *
     * @return Doors
     */
    public function setSurfaceDepth($surfaceDepth)
    {
        $this->surfaceDepth = $surfaceDepth;

        return $this;
    }

    /**
     * Get surfaceDepth
     *
     * @return string
     */
    public function getSurfaceDepth()
    {
        return $this->surfaceDepth;
    }

    /**
     * Set surfaceSides
     *
     * @param string $surfaceSides
     *
     * @return Doors
     */
    public function setSurfaceSides($surfaceSides)
    {
        $this->surfaceSides = $surfaceSides;

        return $this;
    }

    /**
     * Get surfaceSides
     *
     * @return string
     */
    public function getSurfaceSides()
    {
        return $this->surfaceSides;
    }

    /**
     * Set styles
     *
     * @param boolean $styles
     *
     * @return Doors
     */
    public function setStyles($styles)
    {
        $this->styles = $styles;

        return $this;
    }

    /**
     * Get styles
     *
     * @return bool
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Set styleWidth
     *
     * @param string $styleWidth
     *
     * @return Doors
     */
    public function setStyleWidth($styleWidth)
    {
        $this->styleWidth = $styleWidth;

        return $this;
    }

    /**
     * Get styleWidth
     *
     * @return string
     */
    public function getStyleWidth()
    {
        return $this->styleWidth;
    }

    /**
     * Set machning
     *
     * @param boolean $machning
     *
     * @return Doors
     */
    public function setMachning($machning)
    {
        $this->machning = $machning;

        return $this;
    }

    /**
     * Get machning
     *
     * @return bool
     */
    public function getMachning()
    {
        return $this->machning;
    }

    /**
     * Set hindgeModelNo
     *
     * @param string $hindgeModelNo
     *
     * @return Doors
     */
    public function setHindgeModelNo($hindgeModelNo)
    {
        $this->hindgeModelNo = $hindgeModelNo;

        return $this;
    }

    /**
     * Get hindgeModelNo
     *
     * @return string
     */
    public function getHindgeModelNo()
    {
        return $this->hindgeModelNo;
    }

    /**
     * Set hindgeWeight
     *
     * @param string $hindgeWeight
     *
     * @return Doors
     */
    public function setHindgeWeight($hindgeWeight)
    {
        $this->hindgeWeight = $hindgeWeight;

        return $this;
    }

    /**
     * Get hindgeWeight
     *
     * @return string
     */
    public function getHindgeWeight()
    {
        return $this->hindgeWeight;
    }

    /**
     * Set posFromTop
     *
     * @param string $posFromTop
     *
     * @return Doors
     */
    public function setPosFromTop($posFromTop)
    {
        $this->posFromTop = $posFromTop;

        return $this;
    }

    /**
     * Get posFromTop
     *
     * @return string
     */
    public function getPosFromTop()
    {
        return $this->posFromTop;
    }

    /**
     * Set hindgeSize
     *
     * @param string $hindgeSize
     *
     * @return Doors
     */
    public function setHindgeSize($hindgeSize)
    {
        $this->hindgeSize = $hindgeSize;

        return $this;
    }

    /**
     * Get hindgeSize
     *
     * @return string
     */
    public function getHindgeSize()
    {
        return $this->hindgeSize;
    }

    /**
     * Set backSet
     *
     * @param string $backSet
     *
     * @return Doors
     */
    public function setBackSet($backSet)
    {
        $this->backSet = $backSet;

        return $this;
    }

    /**
     * Get backSet
     *
     * @return string
     */
    public function getBackSet()
    {
        return $this->backSet;
    }

    /**
     * Set handleBolt
     *
     * @param string $handleBolt
     *
     * @return Doors
     */
    public function setHandleBolt($handleBolt)
    {
        $this->handleBolt = $handleBolt;

        return $this;
    }

    /**
     * Get handleBolt
     *
     * @return string
     */
    public function getHandleBolt()
    {
        return $this->handleBolt;
    }

    /**
     * Set posFromTopMach
     *
     * @param string $posFromTopMach
     *
     * @return Doors
     */
    public function setPosFromTopMach($posFromTopMach)
    {
        $this->posFromTopMach = $posFromTopMach;

        return $this;
    }

    /**
     * Get posFromTopMach
     *
     * @return string
     */
    public function getPosFromTopMach()
    {
        return $this->posFromTopMach;
    }

    /**
     * Set verticalRod
     *
     * @param string $verticalRod
     *
     * @return Doors
     */
    public function setVerticalRod($verticalRod)
    {
        $this->verticalRod = $verticalRod;

        return $this;
    }

    /**
     * Get verticalRod
     *
     * @return string
     */
    public function getVerticalRod()
    {
        return $this->verticalRod;
    }

    /**
     * Set isLabel
     *
     * @param boolean $isLabel
     *
     * @return Doors
     */
    public function setIsLabel($isLabel)
    {
        $this->isLabel = $isLabel;

        return $this;
    }

    /**
     * Get isLabel
     *
     * @return bool
     */
    public function getIsLabel()
    {
        return $this->isLabel;
    }

    /**
     * Set labels
     *
     * @param string $labels
     *
     * @return Doors
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * Get labels
     *
     * @return string
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Set facePreps
     *
     * @param boolean $facePreps
     *
     * @return Doors
     */
    public function setFacePreps($facePreps)
    {
        $this->facePreps = $facePreps;

        return $this;
    }

    /**
     * Get facePreps
     *
     * @return bool
     */
    public function getFacePreps()
    {
        return $this->facePreps;
    }

    /**
     * Set blockingCharge
     *
     * @param boolean $blockingCharge
     *
     * @return Doors
     */
    public function setBlockingCharge($blockingCharge)
    {
        $this->blockingCharge = $blockingCharge;

        return $this;
    }

    /**
     * Get blockingCharge
     *
     * @return bool
     */
    public function getBlockingCharge()
    {
        return $this->blockingCharge;
    }

    /**
     * Set blockingUpcharge
     *
     * @param string $blockingUpcharge
     *
     * @return Doors
     */
    public function setBlockingUpcharge($blockingUpcharge)
    {
        $this->blockingUpcharge = $blockingUpcharge;

        return $this;
    }

    /**
     * Get blockingUpcharge
     *
     * @return string
     */
    public function getBlockingUpcharge()
    {
        return $this->blockingUpcharge;
    }

    /**
     * Set lumFee
     *
     * @param string $lumFee
     *
     * @return Doors
     */
    public function setLumFee($lumFee)
    {
        $this->lumFee = $lumFee;

        return $this;
    }

    /**
     * Get lumFee
     *
     * @return string
     */
    public function getLumFee()
    {
        return $this->lumFee;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Doors
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}

