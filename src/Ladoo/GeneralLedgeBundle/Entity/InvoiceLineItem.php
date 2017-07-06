<?php

namespace Ladoo\GeneralLedgeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * InvoiceLineItem
 *
 * @ORM\Table(name="invoice_line_item")
 * @ORM\Entity(repositoryClass="Ladoo\GeneralLedgeBundle\Repository\InvoiceLineItemRepository")
 */
class InvoiceLineItem
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @JMS\Type("string")
     * @ORM\Column(name="price", type="decimal", precision=2, scale=10)
     */
    private $price;

    /**
     * @var Invoice
     *
     * @ORM\ManyToOne(targetEntity="Ladoo\GeneralLedgeBundle\Entity\Invoice", inversedBy="lineItems")
     * @ORM\JoinColumn(name="invoice_id", referencedColumnName="id")
     */
    private $invoice;

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
            if ($this->invoice !== null) {
                $this->invoice->removeLineItem($this);
            }
            if ($invoice !== null) {
                $invoice->addLineItem($this);
            }
            $this->invoice = $invoice;
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
     * Set name
     *
     * @param string $name
     *
     * @return InvoiceLineItem
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
     * Set price
     *
     * @param string $price
     *
     * @return InvoiceLineItem
     */
    public function setPrice(string $price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }
}

