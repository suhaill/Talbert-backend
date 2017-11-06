<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SentQuotes
 *
 * @ORM\Table(name="sent_quotes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SentQuotesRepository")
 */
class SentQuotes
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
     * @ORM\Column(name="quote", type="integer")
     */
    private $quote;

    /**
     * @var int
     *
     * @ORM\Column(name="curr_loggedin_user", type="integer")
     */
    private $currLoggedinUser;

    /**
     * @var int
     *
     * @ORM\Column(name="customer", type="integer")
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=1000, nullable=true)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="attachment", type="string", length=255, nullable=true)
     */
    private $attachment;

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
     * Set quote
     *
     * @param integer $quote
     *
     * @return SentQuotes
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get quote
     *
     * @return int
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set currLoggedinUser
     *
     * @param integer $currLoggedinUser
     *
     * @return SentQuotes
     */
    public function setCurrLoggedinUser($currLoggedinUser)
    {
        $this->currLoggedinUser = $currLoggedinUser;

        return $this;
    }

    /**
     * Get currLoggedinUser
     *
     * @return int
     */
    public function getCurrLoggedinUser()
    {
        return $this->currLoggedinUser;
    }

    /**
     * Set customer
     *
     * @param integer $customer
     *
     * @return SentQuotes
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return int
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return SentQuotes
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
     * Set attachment
     *
     * @param string $attachment
     *
     * @return SentQuotes
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;

        return $this;
    }

    /**
     * Get attachment
     *
     * @return string
     */
    public function getAttachment()
    {
        return $this->attachment;
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

