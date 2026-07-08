<?php

namespace App\Entity;

use App\Entity\Adresse;
use App\Entity\Commande;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\DemandeAssistance;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    /**
     * @var Collection<int, Adresse>
     */
    #[ORM\OneToMany(targetEntity: Adresse::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $adresses;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $telephone = null;

    /**
     * @var Collection<int, DemandeAssistance>
     */
    #[ORM\OneToMany(mappedBy: 'client', targetEntity: DemandeAssistance::class, orphanRemoval: true)]
    private Collection $demandesAssistances;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Commande::class, orphanRemoval: true)]
    private Collection $commandes;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $civilite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateNaissance = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $typeCompte = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accepteOffresEmail = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accepteOffresSms = null;

    #[ORM\Column(nullable: true)]
    private ?bool $accepteOffresPartenaires = null;

    public function __construct()
    {
        $this->adresses = new ArrayCollection();
        $this->demandesAssistances = new ArrayCollection();
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * @return Collection<int, Adresse>
     */
    public function getAdresses(): Collection
    {
        return $this->adresses;
    }

    public function addAdress(Adresse $adress): static
    {
        if (!$this->adresses->contains($adress)) {
            $this->adresses->add($adress);
            $adress->setUser($this);
        }

        return $this;
    }

    public function removeAdress(Adresse $adress): static
    {
        if ($this->adresses->removeElement($adress)) {
            // set the owning side to null (unless already changed)
            if ($adress->getUser() === $this) {
                $adress->setUser(null);
            }
        }

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, DemandeAssistance>
     */
    public function getDemandesAssistances(): Collection
    {
        return $this->demandesAssistances;
    }

    public function addDemandesAssistance(DemandeAssistance $demandesAssistance): static
    {
        if (!$this->demandesAssistances->contains($demandesAssistance)) {
            $this->demandesAssistances->add($demandesAssistance);
            $demandesAssistance->setClient($this); // Assure-toi que la méthode s'appelle bien setClient() dans DemandeAssistance
        }

        return $this;
    }

    public function removeDemandesAssistance(DemandeAssistance $demandesAssistance): static
    {
        if ($this->demandesAssistances->removeElement($demandesAssistance)) {
            // set the owning side to null (unless already changed)
            if ($demandesAssistance->getClient() === $this) {
                $demandesAssistance->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setUser($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getUser() === $this) {
                $commande->setUser(null);
            }
        }

        return $this;
    }

    public function getCivilite(): ?string
    {
        return $this->civilite;
    }

    public function setCivilite(?string $civilite): static
    {
        $this->civilite = $civilite;

        return $this;
    }

    public function getDateNaissance(): ?\DateTime
    {
        return $this->dateNaissance;
    }

    public function setDateNaissance(?\DateTime $dateNaissance): static
    {
        $this->dateNaissance = $dateNaissance;

        return $this;
    }

    public function getTypeCompte(): ?string
    {
        return $this->typeCompte;
    }

    public function setTypeCompte(?string $typeCompte): static
    {
        $this->typeCompte = $typeCompte;

        return $this;
    }

    public function isAccepteOffresEmail(): ?bool
    {
        return $this->accepteOffresEmail;
    }

    public function setAccepteOffresEmail(?bool $accepteOffresEmail): static
    {
        $this->accepteOffresEmail = $accepteOffresEmail;

        return $this;
    }

    public function isAccepteOffresSms(): ?bool
    {
        return $this->accepteOffresSms;
    }

    public function setAccepteOffresSms(?bool $accepteOffresSms): static
    {
        $this->accepteOffresSms = $accepteOffresSms;

        return $this;
    }

    public function isAccepteOffresPartenaires(): ?bool
    {
        return $this->accepteOffresPartenaires;
    }

    public function setAccepteOffresPartenaires(?bool $accepteOffresPartenaires): static
    {
        $this->accepteOffresPartenaires = $accepteOffresPartenaires;

        return $this;
    }

    // ── GETTERS POUR LE RGPD ──

    public function getAccepteOffresEmail(): ?bool
    {
        return $this->accepteOffresEmail; // ✨ Avec un "s" pour correspondre à votre propriété privée
    }

    public function getAccepteOffresSms(): ?bool
    {
        return $this->accepteOffresSms;
    }

    public function getAccepteOffresPartenaires(): ?bool
    {
        return $this->accepteOffresPartenaires;
    }
}
