<?php
namespace Ladoo\GeneralLedgeBundle\Tests\Entity;

use Ladoo\GeneralLedgeBundle\Entity\Invoice;
use Ladoo\GeneralLedgeBundle\Entity\InvoiceLineItem;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    /** @var Invoice */
    protected $subject;

    public function setUp() {
        parent::setUp();
        $this->subject = new Invoice();
    }

    public function testCreatedField() {
        $this->assertNotNull($this->subject->getCreatedAt());
    }

    public function testCanAddLineItem() {
        $this->subject->addLineItem((new InvoiceLineItem())->setPrice('100'));
        $this->assertEquals(1, count($this->subject->getLineItems()));
        $this->assertEquals('100', $this->subject->getTotal());
    }

    public function testCanRemoveLineItem() {
        $this->subject
            ->addLineItem((new InvoiceLineItem())->setPrice('100'))
            ->addLineItem((new InvoiceLineItem())->setPrice('100'));
        $this->assertEquals(2, count($this->subject->getLineItems()));
        $this->assertEquals('200', $this->subject->getTotal());
        $this->subject->removeLineItem($this->subject->getLineItems()[0]);
        $this->assertEquals(1, count($this->subject->getLineItems()));
        $this->assertEquals('100', $this->subject->getTotal());
    }

    public function testCanPay() {
        $this->subject
            ->addLineItem((new InvoiceLineItem())->setPrice('100'))
            ->addLineItem((new InvoiceLineItem())->setPrice('100'));
        $this->subject->pay(99, 'cash');
        $this->assertEquals(1, count($this->subject->getTransactions()));
        $this->assertEquals(101, $this->subject->getBalance());
        $this->assertEquals('99', $this->subject->getTransactions()[0]->getTotal());
    }

    public function testCanMultiplePay() {
        $this->subject
            ->addLineItem((new InvoiceLineItem())->setPrice('200'));
        $this->subject->pay(99, 'cash');
        $this->subject->pay(100, 'cash');
        $this->assertEquals(2, count($this->subject->getTransactions()));
        $this->assertEquals(1, $this->subject->getBalance());
    }

    public function testCanPayMoreThanTotal() {
        $this->subject
            ->addLineItem((new InvoiceLineItem())->setPrice('200'));
        $this->subject->pay(300, 'cash');
        $this->assertEquals(-100, $this->subject->getBalance());
    }
}