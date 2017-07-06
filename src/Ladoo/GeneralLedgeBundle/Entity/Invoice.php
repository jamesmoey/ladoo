<?php

namespace Ladoo\GeneralLedgeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Invoice
 *
 * @ORM\Table(name="invoice")
 * @ORM\Entity(repositoryClass="Ladoo\GeneralLedgeBundle\Repository\InvoiceRepository")
 */
class Invoice
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
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @JMS\ReadOnly()
     * @ORM\Column(name="total", type="decimal", precision=2, scale=10)
     */
    private $total;

    /**
     * Many Users have One Address.
     *
     * @var ArrayCollection|InvoiceLineItem[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Ladoo\GeneralLedgeBundle\Entity\InvoiceLineItem",
     *     mappedBy="invoice",
     *     fetch="LAZY",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @JMS\Groups({"details"})
     */
    private $lineItems;

    /**
     * @var ArrayCollection|Transaction[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Ladoo\GeneralLedgeBundle\Entity\Transaction",
     *     mappedBy="invoice",
     *     fetch="EAGER",
     *     cascade={"persist","remove"},
     *     orphanRemoval=true
     * )
     * @JMS\Groups({"details"})
     */
    private $transactions;

    public function __construct()
    {
        $this->lineItems = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->total = 0;
        $this->createdAt = new \DateTime();
    }

    /**
     * @JMS\PostDeserialize()
     */
    public function doUpdateTotal()
    {
        $total = '0';
        $this->lineItems->forAll(function($key, InvoiceLineItem $l) use (&$total) {
            $total = gmp_add($total, $l->getPrice());
            return true;
        });
        $this->total = gmp_strval($total);
    }

    /**
     * Get immutable copy of the line items
     * @return InvoiceLineItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems->getValues();
    }

    /**
     * @param InvoiceLineItem $lineItem
     *
     * @return $this
     */
    public function addLineItem($lineItem)
    {
        if (!$this->lineItems->contains($lineItem)) {
            $this->lineItems->add($lineItem);
            $this->total = gmp_strval(gmp_add(
                $this->total,
                $lineItem->getPrice()
            ));
            $lineItem->setInvoice($this);
        }
        return $this;
    }

    /**
     * @param InvoiceLineItem $lineItem
     * @return $this
     */
    public function removeLineItem($lineItem) {
        if ($this->lineItems->contains($lineItem)) {
            $this->lineItems->removeElement($lineItem);
            $this->total = gmp_strval(gmp_sub(
                $this->total,
                $lineItem->getPrice()
            ));
            $lineItem->setInvoice(null);
        }
        return $this;
    }

    /**
     * Get immutable copy of the transaction
     *
     * @return Transaction[]
     */
    public function getTransactions()
    {
        return $this->transactions->getValues();
    }

    /**
     * @param Transaction $transaction
     *
     * @return $this
     */
    public function addTransaction($transaction)
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setInvoice($this);
        }
        return $this;
    }

    /**
     * @param Transaction $transaction
     * @return $this
     */
    public function removeTransaction($transaction) {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            $transaction->setInvoice(null);
        }
        return $this;
    }

    /**
     * Pay Invoice
     *
     * @param float $amount
     * @param string $method
     *
     * @return $this
     */
    public function pay($amount, $method) {
        $transaction = new Transaction();
        $transaction
            ->setTotal($amount)
            ->setPaymentMethod($method)
            ->setInvoice($this);
        return $this;
    }

    /**
     * Get the amount still owning on this invoice.
     *
     * @return float
     * @JMS\VirtualProperty()
     */
    public function getBalance() {
        $paid = '0';
        $this->transactions->forAll(function($key, Transaction $t) use (&$paid) {
            $paid = gmp_add($paid, $t->getTotal());
            return true;
        });
        return floatval(gmp_strval(gmp_sub($this->total, $paid)));
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
     * Set name
     *
     * @param string $name
     *
     * @return Invoice
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Invoice
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Invoice
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
     * Get total
     *
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }
}

