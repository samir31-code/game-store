<?php

namespace App\Entity;

use App\Entity\Etiquette;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\CategorieProduit;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $referenceProduit = null;

    #[ORM\Column(length: 150)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $marque = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $dimensionL = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $dimensionH = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $dimensionP = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 3, nullable: true)]
    private ?string $poids = null;

    #[ORM\Column(length: 20)]
    private ?string $etatStock = null;

    #[ORM\Column]
    private ?int $quantiteStock = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixHt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $tvaProduit = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixTtc = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    private ?CategorieProduit $categorie = null;

    /**
     * @var Collection<int, Etiquette>
     */
    #[ORM\ManyToMany(targetEntity: Etiquette::class, inversedBy: 'produits')]
    private Collection $etiquettes;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModification = null;

    public function __construct()
    {
        $this->etiquettes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReferenceProduit(): ?string
    {
        return $this->referenceProduit;
    }

    public function setReferenceProduit(string $referenceProduit): static
    {
        $this->referenceProduit = $referenceProduit;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }

    public function getDimensionL(): ?string
    {
        return $this->dimensionL;
    }

    public function setDimensionL(?string $dimensionL): static
    {
        $this->dimensionL = $dimensionL;

        return $this;
    }

    public function getDimensionH(): ?string
    {
        return $this->dimensionH;
    }

    public function setDimensionH(?string $dimensionH): static
    {
        $this->dimensionH = $dimensionH;

        return $this;
    }

    public function getDimensionP(): ?string
    {
        return $this->dimensionP;
    }

    public function setDimensionP(?string $dimensionP): static
    {
        $this->dimensionP = $dimensionP;

        return $this;
    }

    public function getPoids(): ?string
    {
        return $this->poids;
    }

    public function setPoids(?string $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function getEtatStock(): ?string
    {
        return $this->etatStock;
    }

    public function setEtatStock(string $etatStock): static
    {
        $this->etatStock = $etatStock;

        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): static
    {
        $this->quantiteStock = $quantiteStock;

        return $this;
    }

    public function getPrixHt(): ?string
    {
        return $this->prixHt;
    }

    public function setPrixHt(string $prixHt): static
    {
        $this->prixHt = $prixHt;

        return $this;
    }

    public function getTvaProduit(): ?string
    {
        return $this->tvaProduit;
    }

    public function setTvaProduit(string $tvaProduit): static
    {
        $this->tvaProduit = $tvaProduit;

        return $this;
    }

    public function getPrixTtc(): ?string
    {
        return $this->prixTtc;
    }

    public function setPrixTtc(string $prixTtc): static
    {
        $this->prixTtc = $prixTtc;

        return $this;
    }

    public function getCategorie(): ?CategorieProduit
    {
        return $this->categorie;
    }

    public function setCategorie(?CategorieProduit $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection<int, Etiquette>
     */
    public function getEtiquettes(): Collection
    {
        return $this->etiquettes;
    }

    public function addEtiquette(Etiquette $etiquette): static
    {
        if (!$this->etiquettes->contains($etiquette)) {
            $this->etiquettes->add($etiquette);
        }

        return $this;
    }

    public function removeEtiquette(Etiquette $etiquette): static
    {
        $this->etiquettes->removeElement($etiquette);

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeImmutable $dateModification): static
    {
        $this->dateModification = $dateModification;

        return $this;
    }

    #[ORM\PrePersist]
    public function setDateCreationValue(): void
    {
        $this->dateCreation = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setDateModificationValue(): void
    {
        $this->dateModification = new \DateTimeImmutable();
    }

}
