<?php

namespace Ladoo\GeneralLedgeBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializerInterface;
use Ladoo\GeneralLedgeBundle\Entity\Invoice;
use Ladoo\GeneralLedgeBundle\Repository\InvoiceRepository;
use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


/**
 * Class InvoiceRestController
 * @package Ladoo\GeneralLedgeBundle\Controller
 *
 * @Rest\RouteResource("Invoice")
 */
class InvoiceRestController extends FOSRestController
{
    protected $repository;
    protected $em;
    protected $serializer;
    protected $validator;

    public function __construct(
        InvoiceRepository $repo,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    )
    {
        $this->repository = $repo;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function cgetAction() {
        $context = new Context();
        $context->setGroups(['Default', 'list']);
        return $this->handleView(
            $this->view($this->repository->findAll())->setContext($context)
        );
    }

    public function getAction($id) {
        $context = new Context();
        $context->setGroups(['details', 'Default']);
        return $this->handleView(
            $this->view($this->repository->find($id))->setContext($context)
        );
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function postAction(Request $req) {
        /** @var Invoice $invoice */
        $invoice = $this->serializer->deserialize($req->getContent(), Invoice::class, 'json');
        $validation = $this->validator->validate($invoice);
        if ($validation->count() > 0) {
            return JsonResponse::create(['message' => iterator_to_array($validation)], Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {
            $this->em->persist($invoice);
            $this->em->flush();
            $response = $this->getAction($invoice->getId());
            $response->setStatusCode(Response::HTTP_CREATED);
            return $response;
        }
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction($id) {
        /** @var Invoice $invoice */
        $invoice = $this->repository->find($id);
        if (count($invoice->getTransactions()) > 0) {
            return JsonResponse::create(['message' => 'can not delete invoice with transaction'], Response::HTTP_PRECONDITION_FAILED);
        } else {
            $this->em->remove($invoice);
            $this->em->flush();
            return JsonResponse::create();
        }
    }

    /**
     * @RequestParam(name="amount", requirements="[\d\.]+", allowBlank=false, nullable=false)
     * @RequestParam(name="method", allowBlank=false, nullable=false)
     *
     * @param Request $req
     * @param $id
     * @return Response
     */
    public function postPayAction(Request $req, $id) {
        /** @var Invoice $invoice */
        $invoice = $this->repository->find($id);
        if ($invoice === null) {
            return JsonResponse::create(['message' => 'not found'], Response::HTTP_NOT_FOUND);
        } else {
            $invoice->pay($req->request->getDigits('amount'), $req->request->get('method'));
            $this->em->persist($invoice);
            $this->em->flush();
            $response = $this->getAction($invoice->getId());
            return $response;
        }
    }
}
