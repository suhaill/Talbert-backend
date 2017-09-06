<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Module
 *
 * @ORM\Table(name="modules")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModuleRepository")
 */
class Module
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
     * @ORM\Column(name="name", type="string", length=30)
     */
    private $moduleName;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=100)
     */
    private $moduleUrl;


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
     * Set moduleName
     *
     * @param string $moduleName
     *
     * @return Module
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;

        return $this;
    }

    /**
     * Get moduleName
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Set moduleUrl
     *
     * @param string $moduleUrl
     *
     * @return Module
     */
    public function setModuleUrl($moduleUrl)
    {
        $this->moduleUrl = $moduleUrl;

        return $this;
    }

    /**
     * Get moduleUrl
     *
     * @return string
     */
    public function getModuleUrl()
    {
        return $this->moduleUrl;
    }
}

