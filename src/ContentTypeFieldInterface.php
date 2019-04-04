<?php
declare(strict_types=1);

namespace Markup\Contentful;

interface ContentTypeFieldInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getType(): string;

    public function getItems(): array;

    public function isLocalized(): bool;

    public function isRequired(): bool;
}
