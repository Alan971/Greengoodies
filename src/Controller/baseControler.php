<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class baseControler extends AbstractController
{
    /**
     * @Route("/", name="base")
     */
    #[Route("/", name: "base", methods: ['GET'])]
    public function index()
    {
        return $this->render('base.html.twig');
    }
}   