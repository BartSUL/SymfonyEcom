<?php

namespace App\Entity;

use App\Entity\Tag;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'text')]
    private $description;

    #[ORM\Column(type: 'float')]
    private $price;

    #[ORM\Column(type: 'integer')]
    private $stock;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Category', inversedBy:'products')]
    #[ORM\JoinColumn(nullable:true)]
    private $category;

    #[ORM\ManyToMany(targetEntity: 'App\Entity\Tag', inversedBy:'products')]
    #[ORM\JoinColumn(nullable:true)]
    private $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }
    
    #[ORM\Column(type: 'integer')]
    public function getThumbnail(){
        //Cette méthode rend une vignette différente selon la Catégorie (ou l'absence) du Product
        if($this->getCategory()){
            switch($this->getCategory()->getName()){
                case "Chaise":
                    return "placeholder_chaise.jpg";
                case "Bureau":
                    return "placeholder_bureau.jpg";
                case "Lit":
                    return "placeholder_lit.jpg";
                case "Canape":
                    return "placeholder_canape.jpg";
                case "Armoire":
                    return "placeholder_armoire.jpg";
                default;
                    return "placeholder_aucun.jpg";
            }
        }else return  "placeholder_aucun.jpg";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
