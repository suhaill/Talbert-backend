<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DoorCalculator
 *
 * @ORM\Table(name="door_calculator")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DoorCalculatorRepository")
 */
class DoorCalculator
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
     * @ORM\Column(name="door_id", type="integer")
     */
    private $doorId;

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
     * @ORM\Column(name="panel_cost", type="string", length=10, nullable=true, options={"default":0})
     */
    private $panelCost;

    /**
     * @var string
     *
     * @ORM\Column(name="panel_waste", type="string", length=10, nullable=true, options={"default":1})
     */
    private $panelWaste;
    

    /**
     * @var string
     *
     * @ORM\Column(name="sub_total_panel", type="string", length=10, nullable=true, options={"default":0})
     */
    private $subTotalPanel;

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
     * @ORM\Column(name="flat_price", type="string", length=10, nullable=true, options={"default":0})
     */
    private $flatPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="door_frame", type="string", length=10, nullable=true, options={"default":0})
     */
    private $doorFrame;

    /**
     * @var string
     *
     * @ORM\Column(name="louvers", type="string", length=10, nullable=true, options={"default":0})
     */
    private $louvers;

    /**
     * @var string
     *
     * @ORM\Column(name="light_opening", type="string", length=10, nullable=true, options={"default":0})
     */
    private $lightOpening;

    /**
     * @var string
     *
     * @ORM\Column(name="surface_machining", type="string", length=10, nullable=true, options={"default":0})
     */
    private $surfaceMachining;

    /**
     * @var string
     *
     * @ORM\Column(name="styles", type="string", length=10, nullable=true, options={"default":0})
     */
    private $styles;

    /**
     * @var string
     *
     * @ORM\Column(name="machining", type="string", length=10, nullable=true, options={"default":0})
     */
    private $machining;

    /**
     * @var string
     *
     * @ORM\Column(name="face_preps", type="string", length=10, nullable=true, options={"default":0})
     */
    private $facePreps;

    /**
     * @var string
     *
     * @ORM\Column(name="glass", type="string", length=10, nullable=true, options={"default":0})
     */
    private $glass;

    /**
     * @var string
     *
     * @ORM\Column(name="blocking", type="string", length=10, nullable=true, options={"default":0})
     */
    private $blocking;

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
     * @var bool
     *
     * @ORM\Column(name="calcTW", type="boolean")
     */
    private $calcTW;
    
    /**
     * @return bool
     */
    public function isCalcTW()
    {
        return $this->calcTW;
    }
    
    public function getCalcTW()
    {
        return $this->calcTW;
    }
    /**
     * @param bool $calcTW
     */
    public function setCalcTW($calcTW)
    {
        $this->calcTW = $calcTW;
    }
    
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
     * Set doorId
     *
     * @param integer $doorId
     *
     * @return DoorCalculator
     */
    public function setDoorId($doorId)
    {
        $this->doorId = $doorId;

        return $this;
    }

    /**
     * Get doorId
     *
     * @return int
     */
    public function getDoorId()
    {
        return $this->doorId;
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
    public function getPanelCost()
    {
        return $this->panelCost;
    }

    /**
     * @param string $panelCost
     */
    public function setPanelCost($panelCost)
    {
        $this->panelCost = $panelCost;
    }

    /**
     * @return string
     */
    public function getPanelWaste()
    {
        return $this->panelWaste;
    }

    /**
     * @param string $panelWaste
     */
    public function setPanelWaste($panelWaste)
    {
        $this->panelWaste = $panelWaste;
    }

    /**
     * @return string
     */
    public function getSubTotalPanel()
    {
        return $this->subTotalPanel;
    }

    /**
     * @param string $subTotalPanel
     */
    public function setSubTotalPanel($subTotalPanel)
    {
        $this->subTotalPanel = $subTotalPanel;
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
    public function getFlatPrice()
    {
        return $this->flatPrice;
    }

    /**
     * @param string $flatPrice
     */
    public function setFlatPrice($flatPrice)
    {
        $this->flatPrice = $flatPrice;
    }

    /**
     * @return string
     */
    public function getDoorFrame()
    {
        return $this->doorFrame;
    }

    /**
     * @param string $doorFrame
     */
    public function setDoorFrame($doorFrame)
    {
        $this->doorFrame = $doorFrame;
    }

    /**
     * @return string
     */
    public function getLouvers()
    {
        return $this->louvers;
    }

    /**
     * @param string $louvers
     */
    public function setLouvers($louvers)
    {
        $this->louvers = $louvers;
    }

    /**
     * @return string
     */
    public function getLightOpening()
    {
        return $this->lightOpening;
    }

    /**
     * @param string $lightOpening
     */
    public function setLightOpening($lightOpening)
    {
        $this->lightOpening = $lightOpening;
    }

    /**
     * @return string
     */
    public function getSurfaceMachining()
    {
        return $this->surfaceMachining;
    }

    /**
     * @param string $surfaceMachining
     */
    public function setSurfaceMachining($surfaceMachining)
    {
        $this->surfaceMachining = $surfaceMachining;
    }

    /**
     * @return string
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @param string $styles
     */
    public function setStyles($styles)
    {
        $this->styles = $styles;
    }

    /**
     * @return string
     */
    public function getMachining()
    {
        return $this->machining;
    }

    /**
     * @param string $machining
     */
    public function setMachining($machining)
    {
        $this->machining = $machining;
    }

    /**
     * @return string
     */
    public function getFacePreps()
    {
        return $this->facePreps;
    }

    /**
     * @param string $facePreps
     */
    public function setFacePreps($facePreps)
    {
        $this->facePreps = $facePreps;
    }

    /**
     * @return string
     */
    public function getGlass()
    {
        return $this->glass;
    }

    /**
     * @param string $glass
     */
    public function setGlass($glass)
    {
        $this->glass = $glass;
    }

    /**
     * @return string
     */
    public function getBlocking()
    {
        return $this->blocking;
    }

    /**
     * @param string $blocking
     */
    public function setBlocking($blocking)
    {
        $this->blocking = $blocking;
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

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

}

