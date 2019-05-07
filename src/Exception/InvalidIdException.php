<?php
declare(strict_types=1);

namespace Markup\Contentful\Exception;

class InvalidIdException extends \InvalidArgumentException implements ExceptionInterface
{
    /**
     * @var string
     */
    private $id;

    public function __construct(string $id, string $message = '', ...$args)
    {
        $this->id = $id;
        parent::__construct(
            $message ?: sprintf('The provided Contentful ID "%s" is illegal - see https://www.contentful.com/developers/docs/references/content-management-api/#/introduction/resource-ids', $id),
            ...$args
        );
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
