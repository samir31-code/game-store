<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\AdresseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $numero = null;

    #[ORM\Column(length: 35)]
    private ?string $voie = null;

    #[ORM\Column(length: 35, nullable: true)]
    private ?string $complementAdresse1 = null;

    #[ORM\Column(length: 35, nullable: true)]
    private ?string $complementAdresse2 = null;

    #[ORM\Column(length: 35, nullable: true)]
    private ?string $complementAdresse3 = null;

    #[ORM\Column(length: 10)]
    private ?string $codePostal = null;

    #[ORM\Column(length: 3)]
    private ?string $codePays = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $detailLivraison = null;

    #[ORM\ManyToOne(inversedBy: 'adresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $ville = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getNumero(): ?string
    {
        return $this->numero;
    }

    public function setNumero(?string $numero): static
    {
        $this->numero = $numero;

        return $this;
    }

    public function getVoie(): ?string
    {
        return $this->voie;
    }

    public function setVoie(string $voie): static
    {
        $this->voie = $voie;

        return $this;
    }

    public function getComplementAdresse1(): ?string
    {
        return $this->complementAdresse1;
    }

    public function setComplementAdresse1(?string $complementAdresse1): static
    {
        $this->complementAdresse1 = $complementAdresse1;

        return $this;
    }

    public function getComplementAdresse2(): ?string
    {
        return $this->complementAdresse2;
    }

    public function setComplementAdresse2(?string $complementAdresse2): static
    {
        $this->complementAdresse2 = $complementAdresse2;

        return $this;
    }

    public function getComplementAdresse3(): ?string
    {
        return $this->complementAdresse3;
    }

    public function setComplementAdresse3(?string $complementAdresse3): static
    {
        $this->complementAdresse3 = $complementAdresse3;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getCodePays(): ?string
    {
        return $this->codePays;
    }

    public function setCodePays(string $codePays): static
    {
        $this->codePays = $codePays;

        return $this;
    }

    public function getDetailLivraison(): ?string
    {
        return $this->detailLivraison;
    }

    public function setDetailLivraison(?string $detailLivraison): static
    {
        $this->detailLivraison = $detailLivraison;

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

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

}
