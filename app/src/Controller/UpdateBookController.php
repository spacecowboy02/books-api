<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Service\BookService;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class UpdateBookController extends AbstractController
{
    /**
     * @param BookService $bookService
     */
    public function __construct(
        private readonly BookService $bookService
    ) {}

    /**
     * @param Book $book
     * @param Request $request
     * @return Book
     * @throws RandomException
     */
    public function __invoke(Book $book, Request $request): Book
    {
        $content = json_decode($request->request->all()['data'], true);
        $files = $request->files->all();

        return $this->bookService->updateBook($book, $content, $files['image'] ?? null);
    }
}