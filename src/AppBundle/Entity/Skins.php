<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Skins
 *
 * @ORM\Table(name="skins")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SkinsRepository")
 */
class Skins
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
     * @ORM\Column(name="door_id", type="integer")
     */
    private $doorId;

    /**
     * @var string
     *
     * @ORM\Column(name="skin_type", type="string", length=15)
     */
    private $skinType;

    /**
     * @var string
     *
     * @ORM\Column(name="species", type="string", length=30)
     */
    private $species;

    /**
     * @var string
     *
     * @ORM\Column(name="grain", type="string", length=20)
     */
    private $grain;

    /**
     * @var string
     *
     * @ORM\Column(name="grain_dir", type="string", length=20)
     */
    private $grainDir;

    /**
     * @var string
     *
     * @ORM\Column(name="pattern", type="string", length=30)
     */
    private $pattern;

    /**
     * @var string
     *
     * @ORM\Column(name="grade", type="string", length=20)
     */
    private $grade;

    /**
     * @var string
     *
     * @ORM\Column(name="leed_reqs", type="string", length=50)
     */
    private $leedReqs;

    /**
     * @var string
     *
     * @ORM\Column(name="manufacturer", type="string", length=20)
     */
    private $manufacturer;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=100, nullable=true)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="edge", type="string", length=20)
     */
    private $edge;

    /**
     * @var string
     *
     * @ORM\Column(name="thickness", type="string", length=100, nullable=true)
     */
    private $thickness;

    /**
     * @var int
     *
     * @ORM\Column(name="frontSkinThickDrop", type="integer")
     */
    private $frontSkinThickDrop;

    /**
     * @var string
     *
     * @ORM\Column(name="frontSkinCoreThick", type="string", length=100, nullable=true)
     */
    private $frontSkinCoreThick;

    /**
     * @var string
     *
     * @ORM\Column(name="skin_type_back", type="string", length=15)
     */
    private $skinTypeBack;

    /**
     * @var string
     *
     * @ORM\Column(name="back_species", type="string", length=30)
     */
    private $backSpecies;

    /**
     * @var string
     *
     * @ORM\Column(name="back_grain", type="string", length=20)
     */
    private $backGrain;

    /**
     * @var string
     *
     * @ORM\Column(name="back_grain_dir", type="string", length=20)
     */
    private $backGrainDir;

    /**
     * @var string
     *
     * @ORM\Column(name="back_pattern", type="string", length=30)
     */
    private $backPattern;

    /**
     * @var string
     *
     * @ORM\Column(name="back_grade", type="string", length=20)
     */
    private $backGrade;

    /**
     * @var string
     *
     * @ORM\Column(name="back_leed_reqs", type="string", length=50)
     */
    private $backLeedReqs;

    /**
     * @var string
     *
     * @ORM\Column(name="back_manufacturer", type="string", length=20)
     */
    private $backManufacturer;

    /**
     * @var string
     *
     * @ORM\Column(name="back_color", type="string", length=100, nullable=true)
     */
    private $backColor;

    /**
     * @var string
     *
     * @ORM\Column(name="back_edge", type="string", length=20)
     */
    private $backEdge;

    /**
     * @var string
     *
     * @ORM\Column(name="back_thickness", type="string", length=100, nullable=true)
     */
    private $backThickness;

    /**
     * @var int
     *
     * @ORM\Column(name="backSkinThickDrop", type="integer")
     */
    private $backSkinThickDrop;

    /**
     * @var string
     *
     * @ORM\Column(name="backSkinCoreThick", type="string", length=100, nullable=true)
     */
    private $backSkinCoreThick;

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
     * Set quoteId
     *
     * @param integer $quoteId
     *
     * @return Skins
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
     * @param int $doorId
     */
    public function setDoorId($doorId)
    {
        $this->doorId = $doorId;
    }

    /**
     * @return int
     */
    public function getDoorId()
    {
        return $this->doorId;
    }

    /**
     * Set skinType
     *
     * @param string $skinType
     *
     * @return Skins
     */
    public function setSkinType($skinType)
    {
        $this->skinType = $skinType;

        return $this;
    }

    /**
     * Get skinType
     *
     * @return string
     */
    public function getSkinType()
    {
        return $this->skinType;
    }

    /**
     * Set species
     *
     * @param string $species
     *
     * @return Skins
     */
    public function setSpecies($species)
    {
        $this->species = $species;

        return $this;
    }

    /**
     * Get species
     *
     * @return string
     */
    public function getSpecies()
    {
        return $this->species;
    }

    /**
     * Set grain
     *
     * @param string $grain
     *
     * @return Skins
     */
    public function setGrain($grain)
    {
        $this->grain = $grain;

        return $this;
    }

    /**
     * Get grain
     *
     * @return string
     */
    public function getGrain()
    {
        return $this->grain;
    }

    /**
     * Set grainDir
     *
     * @param string $grainDir
     *
     * @return Skins
     */
    public function setGrainDir($grainDir)
    {
        $this->grainDir = $grainDir;

        return $this;
    }

    /**
     * Get grainDir
     *
     * @return string
     */
    public function getGrainDir()
    {
        return $this->grainDir;
    }

    /**
     * Set pattern
     *
     * @param string $pattern
     *
     * @return Skins
     */
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }

    /**
     * Get pattern
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set grade
     *
     * @param string $grade
     *
     * @return Skins
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return string
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * Set leedReqs
     *
     * @param string $leedReqs
     *
     * @return Skins
     */
    public function setLeedReqs($leedReqs)
    {
        $this->leedReqs = $leedReqs;

        return $this;
    }

    /**
     * Get leedReqs
     *
     * @return string
     */
    public function getLeedReqs()
    {
        return $this->leedReqs;
    }

    /**
     * Set manufacturer
     *
     * @param string $manufacturer
     *
     * @return Skins
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    /**
     * Get manufacturer
     *
     * @return string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * Set color
     *
     * @param string $color
     *
     * @return Skins
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set edge
     *
     * @param string $edge
     *
     * @return Skins
     */
    public function setEdge($edge)
    {
        $this->edge = $edge;

        return $this;
    }

    /**
     * Get edge
     *
     * @return string
     */
    public function getEdge()
    {
        return $this->edge;
    }

    /**
     * Set thickness
     *
     * @param string $thickness
     *
     * @return Skins
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
    public function getFrontSkinThickDrop()
    {
        return $this->frontSkinThickDrop;
    }

    /**
     * @param int $frontSkinThickDrop
     */
    public function setFrontSkinThickDrop($frontSkinThickDrop)
    {
        $this->frontSkinThickDrop = $frontSkinThickDrop;
    }

    /**
     * @return string
     */
    public function getFrontSkinCoreThick()
    {
        return $this->frontSkinCoreThick;
    }

    /**
     * @param string $frontSkinCoreThick
     */
    public function setFrontSkinCoreThick($frontSkinCoreThick)
    {
        $this->frontSkinCoreThick = $frontSkinCoreThick;
    }

    /**
     * @return string
     */
    public function getSkinTypeBack()
    {
        return $this->skinTypeBack;
    }

    /**
     * @param string $skinTypeBack
     */
    public function setSkinTypeBack($skinTypeBack)
    {
        $this->skinTypeBack = $skinTypeBack;
    }

    /**
     * @return string
     */
    public function getBackSpecies()
    {
        return $this->backSpecies;
    }

    /**
     * @param string $backSpecies
     */
    public function setBackSpecies($backSpecies)
    {
        $this->backSpecies = $backSpecies;
    }

    /**
     * @return string
     */
    public function getBackGrain()
    {
        return $this->backGrain;
    }

    /**
     * @param string $backGrain
     */
    public function setBackGrain($backGrain)
    {
        $this->backGrain = $backGrain;
    }

    /**
     * @return string
     */
    public function getBackGrainDir()
    {
        return $this->backGrainDir;
    }

    /**
     * @param string $backGrainDir
     */
    public function setBackGrainDir($backGrainDir)
    {
        $this->backGrainDir = $backGrainDir;
    }

    /**
     * @return string
     */
    public function getBackPattern()
    {
        return $this->backPattern;
    }

    /**
     * @param string $backPattern
     */
    public function setBackPattern($backPattern)
    {
        $this->backPattern = $backPattern;
    }

    /**
     * @return string
     */
    public function getBackGrade()
    {
        return $this->backGrade;
    }

    /**
     * @param string $backGrade
     */
    public function setBackGrade($backGrade)
    {
        $this->backGrade = $backGrade;
    }

    /**
     * @return string
     */
    public function getBackLeedReqs()
    {
        return $this->backLeedReqs;
    }

    /**
     * @param string $backLeedReqs
     */
    public function setBackLeedReqs($backLeedReqs)
    {
        $this->backLeedReqs = $backLeedReqs;
    }

    /**
     * @return string
     */
    public function getBackManufacturer()
    {
        return $this->backManufacturer;
    }

    /**
     * @param string $backManufacturer
     */
    public function setBackManufacturer($backManufacturer)
    {
        $this->backManufacturer = $backManufacturer;
    }

    /**
     * @return string
     */
    public function getBackColor()
    {
        return $this->backColor;
    }

    /**
     * @param string $backColor
     */
    public function setBackColor($backColor)
    {
        $this->backColor = $backColor;
    }

    /**
     * @return string
     */
    public function getBackEdge()
    {
        return $this->backEdge;
    }

    /**
     * @param string $backEdge
     */
    public function setBackEdge($backEdge)
    {
        $this->backEdge = $backEdge;
    }

    /**
     * @return string
     */
    public function getBackThickness()
    {
        return $this->backThickness;
    }

    /**
     * @param string $backThickness
     */
    public function setBackThickness($backThickness)
    {
        $this->backThickness = $backThickness;
    }

    /**
     * @return int
     */
    public function getBackSkinThickDrop()
    {
        return $this->backSkinThickDrop;
    }

    /**
     * @param int $backSkinThickDrop
     */
    public function setBackSkinThickDrop($backSkinThickDrop)
    {
        $this->backSkinThickDrop = $backSkinThickDrop;
    }

    /**
     * @return string
     */
    public function getBackSkinCoreThick()
    {
        return $this->backSkinCoreThick;
    }

    /**
     * @param string $backSkinCoreThick
     */
    public function setBackSkinCoreThick($backSkinCoreThick)
    {
        $this->backSkinCoreThick = $backSkinCoreThick;
    }
}

