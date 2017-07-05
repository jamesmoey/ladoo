<?php

namespace Ladoo\GeneralLedgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="Ladoo\GeneralLedgeBundle\Repository\TransactionRepository")
 */
class Transaction
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="total", type="decimal", precision=2, scale=10)
     */
    private $total;

    /**
     * @var string
     *
     * @ORM\Column(name="paymentMethod", type="string", length=255)
     */
    private $paymentMethod;

    /**
     * @var Invoice
     *
     * @ORM\ManyToOne(targetEntity="Ladoo\GeneralLedgeBundle\Entity\Invoice", inversedBy="transactions")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     */
    private $invoice;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function setInvoice($invoice)
    {
        if ($invoice !== $this->invoice) {
            $this->invoice = $invoice;
            if ($this->invoice !== null) {
                $this->invoice->removeTransaction($this);
            }
            if ($invoice !== null) {
                $invoice->addTransaction($this);
            }
        }
        return $this;
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Transaction
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
     * Set total
     *
     * @param string $total
     *
     * @return Transaction
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
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

    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     *
     * @return Transaction
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }
}

