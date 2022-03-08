<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ManagerRegistry $managerRegistry): Response
    {
        //Page d'accueil
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        //on récoup§re nos Categories que nous allons transférer
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        //On récoupère tous les éléments Product
        $products = $productRepository->findAll();

        return $this->render('index/index.html.twig', [
            'categories' => $categories, //On envoie notre tableau de Categories vers Twig
            'products' => $products,
        ]);
    }

    #[Route('/category/{categoryName}', name: 'index_category')]
    public function indexCategory(string $categoryName = '', ManagerRegistry $managerRegistry): Response 
    {
        //Cette méthode renvoie uniquement la Catégorie récoupérée via les renseignements placés dans notre URL
        //Nous avons besoin de l'Entity Manager et du Repository pertinent:
        $entityManager = $managerRegistry->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        //Liste de Catégories
        $categories = $categoryRepository->findAll();
        //Nous récoupérons la Category qui nous intéresse. Si elle n'est pas trouvée, nous retournons tout simplement à l'index
        $category = $categoryRepository->findOneBy(
            ['name' => $categoryName], 
        );
        //Si la recherche n'aboutis pas, la valeur de Category équivalent à null, et la condition suivante !$category est validée
        if(!$category){
            return $this->redirectToRoute('app_index');
        }
        //Si nous avons retrouvé notre Category, nous récoupérons la liste de ses Products grâce à sa méthode gatProducts()
        //Nous allons transformer la Collection $products en Array classique PHP afin d'utiliser la méthode array_reverse et obtenir les éléments les plus récent en premiere
        $products = $category->getProducts()->toArray();
        $products = array_reverse($products);
        //Nous envoyons la liste des Products liés à la Category à notre page d'index Twig
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
