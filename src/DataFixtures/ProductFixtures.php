<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        
        //On créer des products, une Category, lier les Products à la Category, et persister le tout
        $category = new Category;
        $category->setName('Meuble');
        $category->setDescription("Description");
        //Demande de persistance de la Catégorie
        $manager->persist($category);
        
        for($i=0;$i<5;$i++){
            $product = new Product;
            $product->setName(uniqid());
            $product->setDescription("Lorem ipsum etc.");
            $product->setPrice(rand(0,50));
            $product->setStock(rand(0,50));
            //Demande de persist du Product
            $manager->persist($product);

        }

        $manager->flush();
    }
}
