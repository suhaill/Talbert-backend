<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoleModule
 *
 * @ORM\Table(name="role_modules")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoleModuleRepository")
 */
class RoleModule
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
     * @ORM\Column(name="module_id", type="integer")
     */
    private $moduleId;

    /**
     * @var int
     *
     * @ORM\Column(name="role_id", type="integer")
     */
    private $roleId;


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
     * Set moduleId
     *
     * @param integer $moduleId
     *
     * @return RoleModule
     */
    public function setModuleId($moduleId)
    {
        $this->moduleId = $moduleId;

        return $this;
    }

    /**
     * Get moduleId
     *
     * @return int
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * Set roleId
     *
     * @param integer $roleId
     *
     * @return RoleModule
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get roleId
     *
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }
}

