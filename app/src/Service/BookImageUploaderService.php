<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Book;
use Ramsey\Uuid\Nonstandard\Uuid;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

readonly class BookImageUploaderService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
    ];
    private const MAX_FILE_SIZE      = 2097152; // 2MB in bytes

    /**
     * @param string $targetDirectory
     */
    public function __construct(private string $targetDirectory) {}

    /**
     * @param UploadedFile $file
     * @return string
     * @throws RandomException
     */
    public function upload(UploadedFile $file): string
    {
        $this->validateImage($file);

        $randomFolder = bin2hex(random_bytes(3));

        $fileName = Uuid::uuid4() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory() . '/' . $randomFolder, $fileName);
        } catch (FileException $e) {
            throw new BadRequestException('Unable to upload file: ' . $e->getMessage());
        }

        return $randomFolder . '/' . $fileName;
    }

    /**
     * @param Book $book
     * @param UploadedFile $file
     * @return void
     * @throws RandomException
     */
    public function updateExistingImage(Book $book, UploadedFile $file): void
    {
        $oldFilePath = $book->getImage();

        $imageFileName = $this->upload($file);

        $book->setImage($imageFileName);

        [$dir] = explode('/', $oldFilePath);

        $dir = $this->getTargetDirectory() . '/' . $dir;

        array_map('unlink', glob("$dir/*.*"));

        rmdir($dir);
    }

    /**
     * @param UploadedFile $file
     * @throws FileException
     */
    public function validateImage(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new BadRequestHttpException('Invalid file type. Only JPG and PNG are allowed.');
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new BadRequestHttpException('File is too large. Maximum size is 2MB.');
        }
    }

    /**
     * @return string
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}