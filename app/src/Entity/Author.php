<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Nonstandard\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(
            openapiContext: [
                'summary' => 'Ендпоінт перегляду одного автора',
            ],
            normalizationContext: ['groups' => ['get:author:item']]
        ),
        new GetCollection(
            openapiContext: [
                'summary' => 'Ендпоінт перегляду списку авторів',
            ],
            normalizationContext: ['groups' => ['get:author:collection']]
        ),
        new Post(
            openapiContext: [
                'summary' => 'Ендпоінт створення авторів',
            ],
            denormalizationContext: ['groups' => ['post:author']]
        ),
    ],
    paginationClientItemsPerPage: true,
)]
class Author
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[Groups([
        'get:author:item',
        'get:author:collection',
        'get:book:item',
        'get:book:collection',
    ])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Mykhailo')]
    #[Groups([
        'get:author:item',
        'get:author:collection',
        'get:book:item',
        'get:book:collection',
        'post:author',
    ])]
    private string $firstname;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(min: 3, max: 255)]
    #[ApiProperty(example: 'Kotsiubynsky')]
    #[Groups([
        'get:author:item',
        'get:author:collection',
        'get:book:item',
        'get:book:collection',
        'post:author',
    ])]
    private string $lastname;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[ApiProperty(example: 'Mykhailovych')]
    #[Groups([
        'get:author:item',
        'get:author:collection',
        'get:book:item',
        'get:book:collection',
        'post:author',
    ])]
    private ?string $surname;

    #[ORM\ManyToMany(targetEntity: Book::class, mappedBy: 'authors')]
    #[Groups([
        'get:author:item',
        'get:author:collection',
    ])]
    private Collection $books;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->books = new ArrayCollection();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @param UuidInterface $id
     * @return $this
     */
    public function setId(UuidInterface $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param string|null $surname
     * @return $this
     */
    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    /**
     * @param Book $book
     * @return $this
     */
    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            $book->addAuthor($this);
        }

        return $this;
    }

    /**
     * @param Book $book
     * @return $this
     */
    public function removeBook(Book $book): self
    {
        if ($this->books->contains($book)) {
            $this->books->removeElement($book);
            $book->removeAuthor($this);
        }

        return $this;
    }
}