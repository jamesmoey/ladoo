<?php
namespace Ladoo\GeneralLedgeBundle\Tests\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Ladoo\GeneralLedgeBundle\Entity\Invoice;
use Ladoo\GeneralLedgeBundle\Entity\InvoiceLineItem;
use Ladoo\GeneralLedgeBundle\Entity\Transaction;
use Ladoo\GeneralLedgeBundle\Repository\InvoiceRepository;
use Ladoo\GeneralLedgeBundle\Tests\Controller\BaseControllerTestCase as BaseTestCase;

class InvoiceRestControllerTest extends BaseTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $repository;

    /** @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    public function setUp() {
        parent::setUp();
        $this->repository = $this->mockRepository(
            'ladoo_general_ledge.repository.invoice',
            InvoiceRepository::class
        );
        $this->em = $this
            ->getMockBuilder('Doctrine\ORM\Decorator\EntityManagerDecorator')
            ->setConstructorArgs([$this->client->getContainer()->get('doctrine.orm.default_entity_manager')])
            ->setMethods(['persist', 'flush', 'remove'])
            ->getMockForAbstractClass();
        $this->client->getContainer()->set('doctrine.orm.default_entity_manager', $this->em);
        $this->setUpRoute();
    }

    public function testListAllInvoice() {
        $this->repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue([
                (new Invoice())->setName('test')->addLineItem((new InvoiceLineItem())->setPrice('100')),
                (new Invoice())->setName('test2')->addLineItem((new InvoiceLineItem())->setPrice('200'))
            ]));
        $this->client->request('GET', '/test/invoices.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(2, count($content));
        $this->assertArrayNotHasKey('line_items', $content[0]);
        $this->assertArrayNotHasKey('transactions', $content[0]);
        $this->assertEquals(100, $content[0]['total']);
        $this->assertEquals(100, $content[0]['balance']);
        $this->assertEquals(200, $content[1]['total']);
        $this->assertEquals(200, $content[1]['balance']);
    }

    public function testListAnInvoice() {
        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->will($this->returnValue(
                (new Invoice())->setName('test')
                    ->addLineItem((new InvoiceLineItem())->setPrice('100'))
                    ->pay(50, 'cash')
            ));
        $this->client->request('GET', '/test/invoices/1.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('line_items', $content);
        $this->assertArrayHasKey('transactions', $content);
        $this->assertEquals(100, $content['total']);
        $this->assertEquals(50, $content['balance']);
    }

    public function testPayAnInvoice() {
        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->will($this->returnValue(
                (new Invoice())->setName('test')
                    ->addLineItem((new InvoiceLineItem())->setPrice('100'))
            ));
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function(Invoice $invoice) {
                $this->assertEquals(1, $invoice->getBalance());
                $this->assertEquals(1, count($invoice->getTransactions()));
                $this->assertEquals('99', $invoice->getTransactions()[0]->getTotal());
                $this->assertEquals('cash', $invoice->getTransactions()[0]->getPaymentMethod());
            });
        $this->client->request('POST', '/test/invoices/1/pays.json', ['method' => 'cash', 'amount' => 99]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(100, $content['total']);
        $this->assertEquals(1, $content['balance']);
        $this->assertEquals(99, $content['transactions'][0]['total']);
        $this->assertEquals('cash', $content['transactions'][0]['payment_method']);
    }

    public function testPayAnInvoiceFailed() {
        $this->client->request('POST', '/test/invoices/1/pays.json');
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateInvoice() {
        $this->em->expects($this->once())
            ->method('persist')
            ->willReturnCallback(function(Invoice $invoice) {
                $this->assertEquals('test', $invoice->getName());
                $this->assertEquals('test@example.com', $invoice->getEmail());
                $this->assertEquals(1, count($invoice->getLineItems()));
                $this->assertEquals('100', $invoice->getTotal());
            });
        $this->repository->expects($this->once())
            ->method('find')
            ->will($this->returnValue((new Invoice())->setName('testA')));
        $this->client->request('POST', '/test/invoices.json', [], [], [], json_encode([
            'name' => 'test',
            'email' => 'test@example.com',
            'line_items' => [
                [ 'price' => 100 ]
            ]
        ]));
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('testA', $content['name']);
    }

    public function testRemoveInvoice() {
        $this->em->expects($this->once())
            ->method('remove')
            ->willReturnCallback(function(Invoice $invoice) {
                $this->assertEquals('test', $invoice->getName());
            });
        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->will($this->returnValue(
                (new Invoice())->setName('test')
                    ->addLineItem((new InvoiceLineItem())->setPrice('100'))
            ));
        $this->client->request('DELETE', '/test/invoices/1.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testRemoveInvoiceFail() {
        $this->em->expects($this->never())
            ->method('remove');
        $this->repository->expects($this->once())
            ->method('find')
            ->with($this->equalTo(1))
            ->will($this->returnValue(
                (new Invoice())->setName('test')
                    ->addLineItem((new InvoiceLineItem())->setPrice('100'))
                    ->addTransaction((new Transaction())->setTotal('100'))
            ));
        $this->client->request('DELETE', '/test/invoices/1.json');
        $this->assertEquals(412, $this->client->getResponse()->getStatusCode());
    }
}