<?php

namespace App\Controller;

use App\Entity\Sneaker;
use App\Repository\SneakerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SneakerController extends AbstractController
{
    public function __construct(EntityManagerInterface $entityManager){}

    /**
     * @Route("/sneakerBoutique", name="sneaker_index")
     */
    public function index(SneakerRepository $sneakerRepository): Response
    {
        $sneakers = $sneakerRepository->findAll();
        return $this->render('sneaker/index.html.twig',['sneakers'=> $sneakers]);
    
    }
}
