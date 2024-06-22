<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Service\BookService;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UpdateBookController extends AbstractController
{
    /**
     * @param BookService $bookService
     * @param NormalizerInterface $normalizer
     */
    public function __construct(
        private readonly BookService         $bookService,
        private readonly NormalizerInterface $normalizer
    ) {}

    /**
     * @param Book $book
     * @param Request $request
     * @return JsonResponse
     * @throws RandomException
     * @throws ExceptionInterface
     */
    public function __invoke(Book $book, Request $request): JsonResponse
    {
        $content = json_decode($request->request->all()['data'], true);
        $files = $request->files->all();

        if (!$content) {
            throw new BadRequestHttpException("Request content is empty or is not valid JSON");
        }

        $this->bookService->updateBook($book, $content, $files['image'] ?? null);

        return new JsonResponse(
            $this->normalizer->normalize(
                $book,
                null,
                ['groups' => 'get:book:item']
            ), Response::HTTP_OK
        );
    }
}