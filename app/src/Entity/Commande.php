<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Adresse;
use App\Entity\Facture;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\DetailCommande;
use Doctrine\DBAL\Types\Types;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $panier = [];

    #[ORM\Column(length: 20)]
    private ?string $numeroCommande = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $coutTotalHt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $coutTotalTtc = null;

    #[ORM\Column(length: 30)]
    private ?string $typePaiement = null;

    #[ORM\Column]
    private ?bool $paiementValide = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCommande = null;

    #[ORM\Column]
    private ?bool $panierSauvegarde = null;

    #[ORM\ManyToOne]
    private ?Adresse $adresseLivraison = null;

    /**
     * @var Collection<int, DetailCommande>
     */
    #[ORM\OneToMany(targetEntity: DetailCommande::class, mappedBy: 'commande', cascade: ['persist', 'remove'])]
    private Collection $detailCommandes;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $statutExpedition = null;

    #[ORM\OneToOne(mappedBy: 'commande', targetEntity: Facture::class, cascade: ['remove'])]
    private ?Facture $facture = null;

    public function __construct()
{
    $this->detailCommandes = new ArrayCollection();
    $this->dateCommande = new \DateTimeImmutable();
    $this->paiementValide = false;
    $this->panierSauvegarde = false;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCommande(): ?string
    {
        return $this->numeroCommande;
    }

    public function setNumeroCommande(string $numeroCommande): static
    {
        $this->numeroCommande = $numeroCommande;

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

    public function getCoutTotalTtc(): ?string
    {
        return $this->coutTotalTtc;
    }

    public function setCoutTotalTtc(string $coutTotalTtc): static
    {
        $this->coutTotalTtc = $coutTotalTtc;

        return $this;
    }

    public function getTypePaiement(): ?string
    {
        return $this->typePaiement;
    }

    public function setTypePaiement(string $typePaiement): static
    {
        $this->typePaiement = $typePaiement;

        return $this;
    }

    public function isPaiementValide(): ?bool
    {
        return $this->paiementValide;
    }

    public function setPaiementValide(bool $paiementValide): static
    {
        $this->paiementValide = $paiementValide;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeImmutable
    {
        return $this->dateCommande;
    }

    public function setDateCommande(\DateTimeImmutable $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    public function isPanierSauvegarde(): ?bool
    {
        return $this->panierSauvegarde;
    }

    public function setPanierSauvegarde(bool $panierSauvegarde): static
    {
        $this->panierSauvegarde = $panierSauvegarde;

        return $this;
    }

    public function getAdresseLivraison(): ?Adresse
    {
        return $this->adresseLivraison;
    }

    public function setAdresseLivraison(?Adresse $adresseLivraison): static
    {
        $this->adresseLivraison = $adresseLivraison;

        return $this;
    }

    /**
     * @return Collection<int, DetailCommande>
     */
    public function getDetailCommandes(): Collection
    {
        return $this->detailCommandes;
    }

    public function addDetailCommande(DetailCommande $detailCommande): static
    {
        if (!$this->detailCommandes->contains($detailCommande)) {
            $this->detailCommandes->add($detailCommande);
            $detailCommande->setCommande($this);
        }

        return $this;
    }

    public function removeDetailCommande(DetailCommande $detailCommande): static
    {
        if ($this->detailCommandes->removeElement($detailCommande)) {
            // set the owning side to null (unless already changed)
            if ($detailCommande->getCommande() === $this) {
                $detailCommande->setCommande(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatutExpedition(): ?string
    {
        return $this->statutExpedition;
    }

    public function setStatutExpedition(?string $statutExpedition): static
    {
        $this->statutExpedition = $statutExpedition;

        return $this;
    }

    public function getFacture(): ?Facture
    {
        return $this->facture;
    }

    public function setFacture(?Facture $facture): static
    {
        // On s'assure que la relation reste synchrone des deux côtés
        if ($facture === null && $this->facture !== null) {
            $this->facture->setCommande(null);
        }

        if ($facture !== null && $facture->getCommande() !== $this) {
            $facture->setCommande($this);
        }

        $this->facture = $facture;

        return $this;
    }

    public function getPanier(): ?array
    {
        return $this->panier;
    }

    public function setPanier(?array $panier): static
    {
        $this->panier = $panier;
        return $this;
    }

    public function getContenuJson(): ?array
    {
        return $this->getPanier();
    }
}
