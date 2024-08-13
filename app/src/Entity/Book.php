<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\CreateBookController;
use App\Controller\UpdateBookController;
use App\Filter\AuthorsLastnameFilter;
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
                'summary' => 'Ендпоінт перегляду однієї книги',
            ],
            normalizationContext: ['groups' => ['get:book:item']]
        ),
        new GetCollection(
            openapiContext: [
                'summary' => 'Ендпоінт перегляду списку книжок. Фільтр authorsLastname дозволяє шукати за прізвищем автора.',
            ],
            normalizationContext: ['groups' => ['get:book:collection']]
        ),
        new Post(
            controller: CreateBookController::class,
            openapiContext: [
                'summary'     => 'Ендпоінт створення книг',
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'data'  => [
                                        'type'       => 'object',
                                        'properties' => [
                                            'title'       => [
                                                'type'    => 'string',
                                                'example' => 'The Divine Comedy of Dante Alighieri',
                                            ],
                                            'description' => [
                                                'type'    => 'string',
                                                'example' => 'The Divine Comedy begins in a shadowed forest on Good Friday in the year 1300',
                                            ],
                                            "authors"     => [
                                                'type'  => 'array',
                                                'items' => [
                                                    'type'    => 'string',
                                                    'example' => '15a32602-3f68-4916-b2c8-10854c287383',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'image' => [
                                        'type'   => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            normalizationContext: ['groups' => ['get:book:item']],
            deserialize: false,
        ),
        new Post(
            uriTemplate: '/books/update/{id}',
            controller: UpdateBookController::class,
            openapiContext: [
                'summary'     => 'Ендпоінт редагування книг. Було використано метод POST тому що метод PUT бібліотеки Apiplatform не працює з форматом multipart/form-data',
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'data'  => [
                                        'type'       => 'object',
                                        'properties' => [
                                            'title'       => [
                                                'type' => 'string',
                                                'example' => 'The Divine Comedy of Dante Alighieri',
                                            ],
                                            'description' => [
                                                'type' => 'string',
                                                'example' => 'The Divine Comedy begins in a shadowed forest on Good Friday in the year 1300',
                                            ],
                                            "authors"     => [
                                                'type'  => 'array',
                                                'items' => [
                                                    'type' => 'string',
                                                    'example' => '15a32602-3f68-4916-b2c8-10854c287383',
                                                ],
                                            ],
                                        ],
                                    ],
                                    'image' => [
                                        'type'   => 'string',
                                        'format' => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            deserialize: false,
        ),
    ],
    paginationClientItemsPerPage: true
)]
#[ApiFilter(AuthorsLastnameFilter::class, properties: ['authorsLastname'])]
class Book
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[Groups([
        'get:book:item',
        'get:book:collection',
        'get:author:item',
        'get:author:collection',
    ])]
    private UuidInterface $id;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    #[Assert\Length(max: 255)]
    #[Groups([
        'get:book:item',
        'get:book:collection',
        'get:author:item',
        'get:author:collection',
    ])]
    private string $title;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Assert\Length(max: 1000)]
    #[Groups([
        'get:book:item',
        'get:book:collection',
        'get:author:item',
        'get:author:collection',
    ])]
    private ?string $description;

    #[ORM\Column(length: 255)]
    #[Groups([
        'get:book:item',
        'get:book:collection',
        'get:author:item',
        'get:author:collection',
    ])]
    private string $image;

    #[ORM\Column]
    #[Groups([
        'get:book:item',
        'get:book:collection',
        'get:author:item',
        'get:author:collection',
    ])]
    private int $creationDate;

    #[ORM\ManyToMany(targetEntity: Author::class, inversedBy: 'books')]
    #[ORM\JoinTable(name: 'books_authors')]
    #[Groups([
        'get:book:item',
        'get:book:collection',
    ])]
    private Collection $authors;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->creationDate = time();
        $this->authors = new ArrayCollection();
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return $this
     */
    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreationDate(): int
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     * @return $this
     */
    public function setCreationDate(int $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    /**
     * @param Author $author
     * @return $this
     */
    public function addAuthor(Author $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
            $author->addBook($this);
        }

        return $this;
    }

    /**
     * @param Author $author
     * @return $this
     */
    public function removeAuthor(Author $author): self
    {
        if ($this->authors->contains($author)) {
            $this->authors->removeElement($author);
            $author->removeBook($this);
        }

        return $this;
    }
}