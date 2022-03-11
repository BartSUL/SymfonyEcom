<?php

namespace App\Form;

use App\Entity\Tag;
use App\Entity\Product;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' =>  'Nom du produit',
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey'
                ]
            ])
            ->add('category', EntityType::class, [
                'label' => 'Catégorie',
                'class' => Category::class, //Entity utilisée par notre champ
                'choice_label' => 'name', //Attribut utilisé pour représenter l'Entity
                'expanded' => false, //Affichage menu déroulant
                'multiple' => false, //On ne peut sélectionner qu'UNE SEULE Category
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey'
                ]

            ])
            
            ->add('description', TextareaType::class, [
                'label' => 'description',
            ])
            ->add('price', NumberType::class, [
                'label' => 'prix du produit',
                'scale' => 2,
                'attr' => [
                    'min' => 1, //Valeur minimale
                    'class' => 'w3-input w3-border w3-round w3-light-grey'
                ]
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'stock du produit',
                'attr' => [
                    'min' => 1,
                    'class' => 'w3-input w3-border w3-round w3-light-grey'
                ]
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Tags',
                'class' => Tag::class, //Entity utilisée par notre champ
                'choice_label' => 'name', //Attribut utilisé pour représenter l'Entity
                'expanded' => true, 
                'multiple' => true, 
                'attr' => [
                    'class' => 'w3-input w3-border w3-round w3-light-grey'
                ]

            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider', //Détermine le nom du champ/bouton
                'attr' => [
                    'class' => 'w3-btn w3-ripple w3-green', //Nous permet de choisir une class CSS
                ]
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
