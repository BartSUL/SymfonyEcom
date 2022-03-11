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
        // Cette méthode renvoie une page présentant la liste des Products enregistrés dans la BDD
        // Afin de récupérer les instances d'Entity en question, nous avons de l'Entity Manager ainsi que des Repository pertinents
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        // On récupère tous les Products enregistré dans la BDD
        $products = $productRepository->findAll();

        $categoryRepository = $entityManager->getRepository(Category::class);
        // On récupère toutes les Categories enregistré dans la BDD
        $categories = $categoryRepository->findAll();
        $tagRepository = $entityManager->getRepository(Tag::class);
        $tags = $tagRepository->findAll();

        return $this->render('admin/admin_backoffice.html.twig', [
            'categories' => $categories, //On envoie notre tableau de Categories vers Twig
            'products' => $products,
            'tags' => $tags,
        ]);
    }

    #[Route('/product/create', name: 'product_create')]
    public function createProduct(Request $request, ManagerRegistry $managerRegistry): Response
    {
        // Cette méthode a pour objectif de présenter un formulaire de création de Product et d'envoyer le Bulletin créé en conséquence vers la BDD
        $entityManager = $managerRegistry->getManager();
        // Nous avons besoin d'un objet Product vide à lier au futur formulaire
        $categoryRepository = $entityManager->getRepository(Category::class);
        // On récupère toutes les Categories enregistré dans la BDD
        $categories = $categoryRepository->findAll();
        $product = new product; // Le Product reste vide 
        // Nous créons le formulaire que nous lions à notre objet Product
        $productForm = $this->createForm(ProductType::class, $product);
        // Nous transmettons le contenu du formulaire validé à notre Product si présent
        $productForm->handleRequest($request);
        // Si notre Bulletin est validé, nous l'envoyons vers notre BDD
        if($request->isMethod('post') && $productForm->isValid()){
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('admin_backoffice');
        }
        // Nous transmettons le bulletin créé à notre template Twig
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Création de produit',
            'dataForm' => $productForm->createView(), //prépare le formulaire à être affiché
        ]);
        
    }

    #[Route('/product/{productId}', name:'product_update')]
    public function updateProduct(Request $request, ManagerRegistry $managerRegistry,int $productId = 0): Response
    {   
            // Cette méthode propose à l'utilisateur un formulaire dédié à la modification d'un Product récupéré dans notre BDD via son ID que nous renseignons dans l'URL
            // Afin de pouvoir communiquer avec notre BDD et récupérer des éléments à partir de cette dernière, nous allons avoir besoin de l'Entity Manager et du Repository pertinent
            $entityManager = $managerRegistry->getManager();
            $productRepository = $entityManager->getRepository(Product::class);
            $categoryRepository = $entityManager->getRepository(Category::class);
            $categories = $categoryRepository->findAll();
            $product = $productRepository->find($productId);
            if(!$product){
                return $this->redirectToRoute('admin_backoffice');
            }
            $productForm = $this->createForm(ProductType::class, $product);
            $productForm->handleRequest($request);
        // Si notre Bulletin est validé, nous l'envoyons vers notre BDD
        if($request->isMethod('post') && $productForm->isValid()){
            // On vérifie que le prix indiqué pour notre produit est supérieur à 0
            if($product->getPrice() >= 1){
                $entityManager->persist($product);
                $entityManager->flush();
            }
            return $this->redirectToRoute('admin_backoffice');
        }
        // Nous transmettons le bulletin créé à notre template Twig
        return $this->render('index/dataform.html.twig', [
            'categories' => $categories,
            'formName' => 'Modification de produit',
            'dataForm' => $productForm->createView(), //prépare le formulaire à être affiché
        ]);
    }

    #[Route('/product/delete/{productId}', name: 'product_delete')]
    public function deleteProduct(ManagerRegistry $managerRegistry, int $productId = 0): Response
    {   
        // Cette méthode supprimme de la BDD une Entity dont l'IID a été renseigné dans l'URL
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Product::class);
        // Nous récupérons le Product et s'il n'est pas trouvé on retourne a l'index
        $product = $productRepository->find($productId);
        if(!$product){
            return $this->redirectToRoute('admin/backoffice');
        }
        // Si le Product est trouvé, nous procédons à sa suppression avant de retourner sur notre Backoffice Administrateur
        $entityManager->remove($product);
        $entityManager->flush();
        return $this->redirectToRoute('admin_backoffice');
    }

    #[Route('/tag/create', name: 'tag_create')]
    public function createTag(Request $request, ManagerRegistry $managerRegistry): Response
    {
        // Cette méthode permet à l'utilisateur de créer jusqu'à 5 Tags via un formulaire
        // Afin de pouvoir communiquer av la BDD, nous avons besoin de l'Entity Manager ainsi que de Repository de Tag
        $entityManager = $managerRegistry->getManager();
        $productRepository = $entityManager->getRepository(Tag::class);
        // Nous utilisons le Form Builder pour créer notre propre formulaire champ par champ
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
        // On applique l'objet Request sur notre formulaire
        $tagsForm->handleRequest($request);
        // Si le formulaire est validé
        if($request->isMethod('post') && $tagsForm->isvalid()){
            // On récupère les valeurs de notre formulaire
            // La méthode getData() rend un tableau associatif qui possède des valeurs de chaque champ de notre formulaire et les valeurs de nos 5 tags
            $data = $tagsForm->getData();
            for($i=1; $i < 6; $i++){
                if(!empty($data['tag' . $i])){
                    $tagName = $data['tag' . $i]; // On récupère la valeur du champ
                    // On instancie un nouveau Tag à faire persister
                    $tag = new Tag;
                    $tag->setName($tagName);
                    $entityManager->persist($tag);
                }
            }
            $entityManager->flush(); //On applique toutes les demandes de persistance
            // On retoiurne ensuite au backoffice
            return $this->redirectToRoute('admin_backoffice');
        }
        // Si le formulaire n'est pas validé, nous le présentons à l'utilisateur
        return $this->render('index/dataForm.html.twig', [
            'formName' => 'Création de Tags',
            'dataForm' => $tagsForm->createView(),
        ]);
    }

    #[Route('/tag/{tagId}', name: 'tag_update')]
    public function updateTag(Request $request, ManagerRegistry $managerRegistry,int $tagId = 0): Response
    {
        $entityManager = $managerRegistry->getManager();
            $tagRepository = $entityManager->getRepository(Tag::class);
            $categoryRepository = $entityManager->getRepository(Category::class);
            $categories = $categoryRepository->findAll();
            $tag = $tagRepository->findAll();
            $tag = $tagRepository->find($tagId);
            if(!$tag){
                return $this->redirectToRoute('admin_backoffice');
            }
            $tagForm = $this->createForm(TagType::class, $tag);
            $tagForm->handleRequest($request);
        // Si notre Bulletin est validé, nous l'envoyons vers notre BDD
        if($request->isMethod('post') && $tagForm->isValid()){
            // On vérifie que le prix indiqué pour notre produit est supérieur à 0
            
                $entityManager->persist($tag);
                $entityManager->flush();
            return $this->redirectToRoute('admin_backoffice');
        }
        // Nous transmettons le bulletin créé à notre template Twig
        return $this->render('index/dataform.html.twig', [
            'categories' => 'categories',
            'formName' => 'Modification de produit',
            'dataForm' => $tagForm->createView(), //prépare le formulaire à être affiché
        ]);
    }

    #[Route('/tag/delete/{tagId}', name: 'tag_delete')]
    public function deleteTag(ManagerRegistry $managerRegistry, int $tagId = 0): Response
    {   
        // Cette méthode supprimme de la BDD une Entity dont l'IID a été renseigné dans l'URL
        $entityManager = $managerRegistry->getManager();
        $tagRepository = $entityManager->getRepository(Tag::class);
        // Nous récupérons le Product et s'il n'est pas trouvé on retourne a l'index
        $tag = $tagRepository->find($tagId);
        if(!$tag){
            return $this->redirectToRoute('admin/backoffice');
        }
        // Si le Product est trouvé, nous procédons à sa suppression avant de retourner sur notre Backoffice Administrateur
        $entityManager->remove($tag);
        $entityManager->flush();
        return $this->redirectToRoute('admin_backoffice');
    }
    
}
