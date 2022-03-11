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
        //On récupère nos Catégories que nous allons transférer
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        //On récupère tous les éléments Product
        $products = $productRepository->findAll();
        //On crée une "selectedCatgory" sous la forme d'un tableau associatif
        $selectedCategory = [
            'name' => 'Symfony eCommerce',
            'description' => 'Bienvenue sur la page d\'accueil de notre magasin de mobilier. Pellentesque sed viverra arcu, in tristique leo. Ut dui mauris, ullamcorper ut enim sit amet, vehicula gravida eros. Maecenas quis sapien a lorem tempor semper. Donec tempor mollis vestibulum. Integer a posuere eros. Aenean feugiat ut velit non tincidunt. Vivamus egestas nisi sit amet magna pharetra facilisis. Nam finibus dictum turpis, vel feugiat orci rhoncus pulvinar.',
        ];
        
        return $this->render('index/index.html.twig', [
            'categories' => $categories, //On envoie notre tableau de Categories vers Twig
            'selectedCategory' => $selectedCategory,
            'products' => $products,
        ]);
    }

    #[Route('/category/{categoryName}', name: 'index_category')]
    public function indexCategory(string $categoryName = '', ManagerRegistry $managerRegistry): Response
    {
        //Cette méthode renvoie uniquement le catalogue de la Catégorie récupérée via les renseignements placés dans notre URL
        //Nous avons besoin de l'Entity Manager et du Repository pertinent:
        $entityManager = $managerRegistry->getManager();
        $categoryRepository = $entityManager->getRepository(Category::class);
        //Liste des Catégories
        $categories = $categoryRepository->findAll();
        //Nous récupérons la Category qui nous intéresse. Si elle n'est pas trouvée, nous retournons tout simplement à l'index
        $category = $categoryRepository->findOneBy(
            ['name' => $categoryName], 
        );
        //Si la recherche n'aboutit pas, la valeur de Category équivaut à null, et la condition suivante !$category est validée
        if(!$category){
            return $this->redirectToRoute('app_index');
        }
        //Si nous avons retrouvé notre Category, nous récupérons la liste de ses Products grâce à sa méthode getProducts()
        //Nous allons transformer la Collection $products en Array classique PHP afin d'utiliser la méthode array_reverse et obtenir les éléments les plus récents en premier
        $products = $category->getProducts()->toArray();
        $products = array_reverse($products);
        //Nous envoyons la liste des Products liés à la Category à notre page d'index Twig
        return $this->render('index/index.html.twig', [
            'categories' => $categories,
            'selectedCategory' => $category,
            'products' => $products,
        ]);
    }

    #[Route('/product/display/{productId}', name: 'product_display')]
    public function displayProduct(int $productId, ManagerRegistry $managerRegistry): Response
    {
        //Cette méthode affiche les informations relatives à une instance d'Entity de type Product dont l'ID correspond à la valeur du paramètre de route indiquée dans notre URL
        //Nous récupérons l'Entity Manager et le Repository pertinent afin de pouvoir dialoguer avec notre base de données
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        //On récupère les Catégories à afficher
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        //Nous utilisons la méthode find() du Repository afin de pouvoir retrouver le Product qui nous intéresse. Si le résultat est null, nous retournons à notre page d'index
        $product = $productRepository->find($productId);
        if(!$product){
            return $this->redirectToRoute('app_index');
        }
        //Si nous avons récupéré notre product avec succès de notre base de données, nous pouvons l'afficher via notre page Twig:
        return $this->render('index/product_display.html.twig', [
            'categories' => $categories,
            'product' => $product,
        ]);
    }

    #[Route('/product/buy/{productId}', name: 'product_buy')]
    public function buyProduct(int $productId = 0, ManagerRegistry $managerRegistry): Response
    {
        //Cette méthode décremente notre Product désigné vie l'ID renseigné dans notre base de donnée de 1.
        //Afin de pouvoir dialoguer avec notre base de donnée et récoupérer le Product dont nous désirons décrémenter le stock, nous avons besoin de l'etinity Manager ainsi que du Reposiroty pertinent:
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        //Nous récouperons le Product dont l'ID est spécifié dans l'URL. Si ce Product n'est pas trouvé, nous retournons à l'index
        $product = $productRepository->find($productId);
        if(!$product ){
            return $this->redirectToRoute('app_index');
        }
        //Si nous avons récoupérer notre Product, nous décrementons son stock de 1, A CONDITION que ce dernier soit supérieur à 0
        $productStock = $product->getStock();
        if($productStock > 0){
            $productStock -= 1;
            $product->setStock($productStock);
        }
        //Nous persistons notre Product, avant de retourner sur la fiche Product pertinente
        $entityManager->persist($product);
        $entityManager->flush();
        return $this->redirectToRoute('product_display', [
            'productId' => $product->getId(),
        ]);
    }
}
