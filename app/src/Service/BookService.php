<?php

declare(strict_types=1);

namespace App\Service;

use ApiPlatform\Validator\ValidatorInterface as ApiPlatformValidator;
use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraint;

readonly class BookService
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param BookImageUploaderService $bookImageUploaderService
     * @param ApiPlatformValidator $apiPlatformValidator
     */
    public function __construct(
        private EntityManagerInterface   $entityManager,
        private BookImageUploaderService $bookImageUploaderService,
        private ApiPlatformValidator     $apiPlatformValidator,
    ) {}

    /**
     * @param array $content
     * @param UploadedFile $image
     * @return Book
     * @throws RandomException
     */
    public function createBook(array $content, UploadedFile $image): Book
    {
        $book = (new Book())
            ->setDescription($content['description'])
            ->setTitle($content['title']);

        if (!count($content['authors'])) {
            throw new BadRequestHttpException("You must add at least one author");
        }

        $this->addAuthors($book, $content['authors']);

        $this->apiPlatformValidator->validate(
            $book,
            [
                'groups' => [
                    Constraint::DEFAULT_GROUP,
                ],
            ]
        );

        $imageFileName = $this->bookImageUploaderService->upload($image);

        $book->setImage($imageFileName);

        return $book;
    }

    /**
     * @param Book $book
     * @param array $content
     * @param UploadedFile|null $image
     * @return Book
     * @throws RandomException
     */
    public function updateBook(Book $book, array $content, ?UploadedFile $image): Book
    {
        foreach ($content as $key => $value) {
            switch ($key) {
                case 'title':
                    $book->setTitle($content['title']);
                    break;
                case 'description':
                    $book->setDescription($content['description']);
                    break;
                case 'authors':
                    if (!count($content['authors'])) {
                        throw new BadRequestHttpException("You must add at least one author");
                    }

                    /** @var Author $author */
                    foreach ($book->getAuthors() as $author) {
                        $key = $this->findAuthorInRequestParams($author, $content['authors']);

                        if ($key !== null) {
                            unset($content['authors'][$key]);

                            continue;
                        }

                        $book->removeAuthor($author);
                    }

                    $this->addAuthors($book, $content['authors']);

                    break;
                default:
                    break;
            }
        }

        $this->apiPlatformValidator->validate(
            $book,
            [
                'groups' => [
                    Constraint::DEFAULT_GROUP,
                ],
            ]
        );

        if (!$image) {
            return $book;
        }

        $this->bookImageUploaderService->updateExistingImage($book, $image);

        return $book;
    }

    /**
     * @param Book $book
     * @param array $authors
     * @return void
     */
    public function addAuthors(Book $book, array $authors): void
    {
        foreach ($authors as $authorId) {
            $author = $this->entityManager->getRepository(Author::class)->findOneBy([
                'id' => $authorId,
            ]);

            if (!$author) {
                throw new BadRequestHttpException("Author with id {$authorId} not found");
            }

            $book->addAuthor($author);
        }
    }

    /**
     * @param Author $author
     * @param array $authors
     * @return int|null
     */
    protected function findAuthorInRequestParams(Author $author, array $authors): int|null
    {
        foreach ($authors as $key => $id) {
            if ($id === $author->getId()->toString()) {
                return $key;
            }
        }

        return null;
    }
}