<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/products', name: 'admin_backoffice')]
    public function adminBackoffice(ManagerRegistry $managerRegistry): Response
    {
        //Cette méthode nous renvoie vers une page nous présentant la liste de tous les Products enregistrés dans notre base de données à des fins de consultation, modification ou suppression
        //Afin de récupérer les instances d'Entity en question, nous avons de l'Entity Manager ainsi que des Repository pertinents
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        $tagRepository = $entityManager->getRepository(Tag::class);
        //On récupère nos Catégories pour notre header
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        //Nous récupérons tous les Products et Tags enregistrés dans notre BDD
        $products = $productRepository->findAll();
        $tags = $tagRepository->findAll();

        return $this->render('admin/admin_backoffice.html.twig', [
            'categories' => $categories,
            'products' => $products,
            'tags' => $tags,
        ]);
    }

    #[Route('/product/create', name: 'product_create')]
    public function createProduct(Request $request, ManagerRegistry $managerRegistry){
        //Cette méthode dirige l'utilisateur vers un formulaire de création de Product et transfère le Product dans la base de données une fois celui-ci renseigné
        //Nous avons besoin de dialoguer avec la base de données, donc nous faisons appel à l'Entity Manager
        $entityManager = $managerRegistry->getManager();
        //On récupère nos Catégories pour notre header
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        //Nous créons une nouvelle instance d'Entity Product que nous lions à notre formulaire
        $product = new Product;
        $productForm = $this->createForm(ProductType::class, $product);
        //Nous appliquons les valeurs de notre Request, et si le formulaire est valide, nous l'envoyons vers notre base de données
        $productForm->handleRequest($request);
        if($request->isMethod('post') && $productForm->isValid()){
            //On vérifie que le prix indiqué pour notre produit est supérieur à 0
            if($product->getPrice() >= 1){
                $entityManager->persist($product);
                $entityManager->flush();
                $request->getSession()->set('message_title', 'Création de Produit');
                $this->addFlash('info', 'Le Produit a été créé avec succès.');
                $request->getSession()->set('status', 'green');
                return $this->redirectToRoute('admin_backoffice');
            } else {
                $request->getSession()->set('message_title', 'Achat');
                $this->addFlash('info', 'Veuillez indiquer un prix supérieur à 1€.');
                $request->getSession()->set('status', 'red');
            }
        }
        //Si le formulaire n'est pas rempli, nous le présentons à l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Création de Produit',
            'dataForm' => $productForm->createView(),
        ]);
    }

    #[Route('/product/{productId}', name:'product_update')]
    public function updateProduct(Request $request, ManagerRegistry $managerRegistry, int $productId = 0): Response
    {
        //Cette méthode a pour objectif de proposer à l'utilisateur un formulaire dédié à la modification d'un Product récupéré de notre base de données via son ID, que nous renseignons dans l'URL
        //Afin de pouvoir communiquer avec notre base de données et récupérer des éléments à partir de cette dernière, nous allons avoir besoin de l'Entity Manager et du Repository pertinent
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        //On récupère nos Catégories pour notre header
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        //Nous recherchons le Product désiré via une recherche avec l'ID noté dans l'URL, si la recherche n'aboutit pas, nous revenons au backoffice administrateur
        $product = $productRepository->find($productId);
        if(!$product){
            $request->getSession()->set('message_title', 'Modification de Produit');
            $this->addFlash('info', 'Le Produit indiqué n\'existe pas.');
            $request->getSession()->set('status', 'red');
            return $this->redirectToRoute('admin_backoffice');
        }
        $productForm = $this->createForm(ProductType::class, $product);
        //Nous appliquons les valeurs de notre Request, et si le formulaire est valide, nous l'envoyons vers notre base de données
        $productForm->handleRequest($request);
        if($request->isMethod('post') && $productForm->isValid()){
            //On vérifie que le prix indiqué pour notre produit est égal ou supérieur à 1
            if($product->getPrice() >= 1){
                $entityManager->persist($product);
                $entityManager->flush();
                $request->getSession()->set('message_title', 'Modification de Produit');
                $this->addFlash('info', 'Le Produit a été modifié avec succès.');
                $request->getSession()->set('status', 'green');
                return $this->redirectToRoute('admin_backoffice');
            } else {
                $request->getSession()->set('message_title', 'Modification de Produit');
                $this->addFlash('info', 'Veuillez indiquer un prix supérieur à 1€.');
                $request->getSession()->set('status', 'red');
            }
        }
        //Si le formulaire n'est pas rempli, nous le présentons à l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Modification de Produit',
            'dataForm' => $productForm->createView(),
        ]);
    }

    #[Route('/product/delete/{productId}', name: 'product_delete')]
    public function deleteProduct(Request $request, ManagerRegistry $managerRegistry, int $productId = 0): Response
    {
        //Cette méthode supprime de la base de données une Entity dont l'ID a été renseigné dans notre URL
        //Afin de pouvoir récupérer le Product en question de notre base de données, nous avons besoin de l'Entity Manager et du Repository pertinent
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        //Nous récupérons le Product en question, s'il n'est pas trouvé nous retournons à l'index
        $product = $productRepository->find($productId);
        if(!$product){
            $request->getSession()->set('message_title', 'Suppression de Produit');
            $this->addFlash('info', 'Le Produit indiqué n\'existe pas.');
            $request->getSession()->set('status', 'red');
            return $this->redirectToRoute('admin_backoffice');
        }
        //Si le Product est trouvé, nous procédons à sa suppression avant de retourner sur notre Backoffice Administrateur:
        $entityManager->remove($product);
        $entityManager->flush();
        $request->getSession()->set('message_title', 'Suppression de Produit');
        $this->addFlash('info', 'Le Produit a été supprimé avec succès.');
        $request->getSession()->set('status', 'green');
        return $this->redirectToRoute('admin_backoffice');
    }

    #[Route('/tag/create', name:'tag_create')]
    public function createTags(Request $request, ManagerRegistry $managerRegistry): Response
    {
        //Cette méthode permet à l'Utilisateur de créer jusqu'à cinq Tags via un formulaire
        //Afin de pouvoir communiquer avec notre base de données, nous avons besoin de l'Entity Manager ainsi que du Repository de Tag
        $entityManager = $managerRegistry->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        //Nous utilisons le Form Builder pour créer notre propre formulaire champ par champ
        $tagsForm = $this->createFormBuilder()
            ->add('tag1', TextType::class, [
                'label' => 'Tag #1',
                'required' => false, //Remplir le champ n'est pas nécessaire
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey',
                ]
            ])
            ->add('tag2', TextType::class, [
                'label' => 'Tag #2',
                'required' => false, //Remplir le champ n'est pas nécessaire
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey',
                ]
            ])
            ->add('tag3', TextType::class, [
                'label' => 'Tag #3',
                'required' => false, //Remplir le champ n'est pas nécessaire
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey',
                ]
            ])
            ->add('tag4', TextType::class, [
                'label' => 'Tag #4',
                'required' => false, //Remplir le champ n'est pas nécessaire
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey',
                ]
            ])
            ->add('tag5', TextType::class, [
                'label' => 'Tag #5',
                'required' => false, //Remplir le champ n'est pas nécessaire
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'w3-button w3-black w3-margin-bottom',
                    'style' => 'margin-top: 10px'
                ]
            ])
            ->getForm();
        //Nous appliquons l'objet Request sur notre formulaire
        $tagsForm->handleRequest($request);
        //Si le formulaire est validé
        if($request->isMethod('post') && $tagsForm->isValid()){
            //On récupère les valeurs de notre formulaire
            //La méthode getData() rend un tableau associatif qui possède les valeurs de chaque champ de notre formulaire, et ainsi les valeurs de nos cinq tags
            $data = $tagsForm->getData();
            for($i=1; $i < 6; $i++){ //On itère à travers chaque champ de notre formulaire
                if(!empty($data['tag' . $i])){
                    $tagName = $data['tag' . $i]; //On récupère la valeur du Champ
                    //On instancie un nouveau Tag à faire persister
                    $tag = new Tag;
                    $tag->setName($tagName);
                    $entityManager->persist($tag);
                }
            }
            $request->getSession()->set('message_title', 'Création de Tags');
            $this->addFlash('info', 'Les Tags renseignés ont été créés avec succès.');
            $request->getSession()->set('status', 'green');
            $entityManager->flush(); //On applique toutes les demandes de persistance
            //On retourne ensuite au backoffice
            return $this->redirectToRoute('admin_backoffice');
        }
        //Si le formulaire n'est pas validé, nous le présentons à l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'formName' => 'Création de Tags',
            'dataForm' => $tagsForm->createView(),
        ]);
    }

    #[Route('/tag/{tagId}', name:'tag_update')]
    public function updateTag(Request $request, ManagerRegistry $managerRegistry, int $tagId = 0): Response
    {
        //Cette méthode a pour objectif de proposer à l'utilisateur un formulaire dédié à la modification d'un Tag récupéré de notre base de données via son ID, que nous renseignons dans l'URL
        //Afin de pouvoir communiquer avec notre base de données et récupérer des éléments à partir de cette dernière, nous allons avoir besoin de l'Entity Manager et du Repository pertinent
        $entityManager = $managerRegistry->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        //On récupère nos Catégories pour notre header
        $categoryRepository = $entityManager->getRepository(Category::class);
        $categories = $categoryRepository->findAll();
        //Nous recherchons le Tag désiré via une recherche avec l'ID noté dans l'URL, si la recherche n'aboutit pas, nous revenons au backoffice administrateur
        $tag = $tagRepository->find($tagId);
        if(!$tag){
            $request->getSession()->set('message_title', 'Modification de Tag');
            $this->addFlash('info', 'Le Tag indiqué n\'existe pas.');
            $request->getSession()->set('status', 'red');
            return $this->redirectToRoute('admin_backoffice');
        }
        $tagForm = $this->createForm(TagType::class, $tag);
        //Nous appliquons les valeurs de notre Request, et si le formulaire est valide, nous l'envoyons vers notre base de données
        $tagForm->handleRequest($request);
        if($request->isMethod('post') && $tagForm->isValid()){
            $entityManager->persist($tag);
            $entityManager->flush();
            $request->getSession()->set('message_title', 'Modification de Tag');
            $this->addFlash('info', 'Le Tag a été modifié avec succès.');
            $request->getSession()->set('status', 'green');
            return $this->redirectToRoute('admin_backoffice');
        }
        //Si le formulaire n'est pas rempli, nous le présentons à l'utilisateur
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Modification de Produit',
            'dataForm' => $tagForm->createView(),
        ]);
    }

    #[Route('/tag/delete/{tagId}', name: 'tag_delete')]
    public function deleteTag(Request $request, ManagerRegistry $managerRegistry, int $tagId = 0): Response
    {
        //Cette méthode supprime de la base de données une Entity dont l'ID a été renseigné dans notre URL
        //Afin de pouvoir récupérer le Tag en question de notre base de données, nous avons besoin de l'Entity Manager et du Repository pertinent
        $entityManager = $managerRegistry->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        //Nous récupérons le Tag en question, s'il n'est pas trouvé nous retournons à l'index
        $tag = $tagRepository->find($tagId);
        if(!$tag){
            $request->getSession()->set('message_title', 'Suppression de Tag');
            $this->addFlash('info', 'Le Tag indiqué n\'existe pas.');
            $request->getSession()->set('status', 'red');
            return $this->redirectToRoute('admin_backoffice');
        }
        //Si le Tag est trouvé, nous procédons à sa suppression avant de retourner sur notre Backoffice Administrateur:
        $entityManager->remove($tag);
        $entityManager->flush();
        $request->getSession()->set('message_title', 'Suppression de Tag');
        $this->addFlash('info', 'Le Tag a été supprimé avec succès.');
        $request->getSession()->set('status', 'green');
        return $this->redirectToRoute('admin_backoffice');
    }

}

