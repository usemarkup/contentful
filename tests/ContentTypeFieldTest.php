<?php
declare(strict_types=1);

namespace Markup\Contentful\Tests;

use Markup\Contentful\ContentTypeField;
use Markup\Contentful\ContentTypeFieldInterface;
use PHPUnit\Framework\TestCase;

class ContentTypeFieldTest extends TestCase
{
    public function testIsContentTypeField()
    {
        $this->assertTrue(
            (new \ReflectionClass(ContentTypeField::class))
                ->implementsInterface(ContentTypeFieldInterface::class)
        );
    }
}
