<?php

namespace App\Controller;

use App\Entity\Sneaker;
use App\Repository\SneakerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
   /**
     * @Route("/", name="app_homepage")
     */
    public function index(SneakerRepository $sneakerRepository): Response
    {
        $sneakers = $sneakerRepository->findAll();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'sneakers' => $sneakers
        ]);
    }
   
}

