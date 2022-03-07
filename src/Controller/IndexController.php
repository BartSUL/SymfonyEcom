<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        $products = [];
        for($i=0; $i < 2; $i++){
            array_push($products, ['name' => uniqid()]);
        }

        return $this->render('index/index.html.twig', [
            'products' => $products,
        ]);
    }
}
