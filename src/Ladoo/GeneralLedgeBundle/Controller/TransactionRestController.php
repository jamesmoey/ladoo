<?php
namespace Ladoo\GeneralLedgeBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Ladoo\GeneralLedgeBundle\Repository\TransactionRepository;

/**
 * Class InvoiceRestController
 * @package Ladoo\GeneralLedgeBundle\Controller
 *
 * @Rest\RouteResource("Transaction")
 */
class TransactionRestController extends FOSRestController
{
    protected $repository;

    public function __construct(
        TransactionRepository $repo
    )
    {
        $this->repository = $repo;
    }

    public function cgetAction() {
        return $this->handleView(
            $this->view($this->repository->findAll())
        );
    }
}