<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Service\BookService;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CreateBookController extends AbstractController
{
    /**
     * @param BookService $bookService
     */
    public function __construct(
        private readonly BookService $bookService
    ) {}

    /**
     * @param Request $request
     * @return Book
     * @throws RandomException
     */
    public function __invoke(Request $request): Book
    {
        $content = json_decode($request->request->all()['data'], true);
        $files = $request->files->all();

        if (!isset($content['title'], $content['description'], $content['authors'], $files['image'])) {
            throw new BadRequestHttpException("Missing request parameters");
        }

        return $this->bookService->createBook($content, $files['image']);
    }
}