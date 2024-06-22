<?php

declare(strict_types=1);

namespace App\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

final class AuthorsLastnameFilter extends AbstractFilter
{
    private const FILTER_PROPERTY = "authorsLastname";

    /*
     * Filtered properties is accessible through getProperties() method: property => strategy
     */
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        if ($property !== self::FILTER_PROPERTY || empty($value)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $attributeValue = $queryNameGenerator->generateParameterName($property);

        $queryBuilder
            ->innerJoin("$rootAlias.authors", "authors")
            ->andWhere("authors.lastname LIKE :$attributeValue")
            ->setParameter($attributeValue, "%" . $value . "%");
    }

    /*
     * This function is only used to hook in documentation generators (supported by Swagger and Hydra).
     */
    public function getDescription(string $resourceClass): array
    {
        if (!$this->properties) {
            return [];
        }

        $description = [];

        foreach ($this->properties as $property => $strategy) {
            $description[$property] = [
                'property'    => $property,
                'type'        => Type::BUILTIN_TYPE_STRING,
                'required'    => false,
                'description' => 'Filter using an author`s lastname',
            ];
        }
        return $description;
    }
}