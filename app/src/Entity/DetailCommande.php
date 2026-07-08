<?php

namespace App\Entity;

use App\Entity\Produit;
use App\Entity\Commande;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Repository\DetailCommandeRepository;

#[ORM\Entity(repositoryClass: DetailCommandeRepository::class)]
class DetailCommande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $coutUnitaireHt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $coutTotalHt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private ?string $tvaProduit = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $coutUnitaireTtc = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $coutTotalTtc = null;

    #[ORM\ManyToOne(inversedBy: 'detailCommandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commande $commande = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoutUnitaireHt(): ?string
    {
        return $this->coutUnitaireHt;
    }

    public function setCoutUnitaireHt(string $coutUnitaireHt): static
    {
        $this->coutUnitaireHt = $coutUnitaireHt;

        return $this;
    }

    public function getCoutTotalHt(): ?string
    {
        return $this->coutTotalHt;
    }

    public function setCoutTotalHt(string $coutTotalHt): static
    {
        $this->coutTotalHt = $coutTotalHt;

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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getCoutUnitaireTtc(): ?string
    {
        return $this->coutUnitaireTtc;
    }

    public function setCoutUnitaireTtc(string $coutUnitaireTtc): static
    {
        $this->coutUnitaireTtc = $coutUnitaireTtc;

        return $this;
    }

    public function getCoutTotalTtc(): ?string
    {
        return $this->coutTotalTtc;
    }

    public function setCoutTotalTtc(string $coutTotalTtc): static
    {
        $this->coutTotalTtc = $coutTotalTtc;

        return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): static
    {
        $this->commande = $commande;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }
}
