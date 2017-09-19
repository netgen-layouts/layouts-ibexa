<?php

namespace Netgen\BlockManager\Ez\Layout\Resolver\TargetType;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use Netgen\BlockManager\Ez\ContentProvider\ContentExtractorInterface;
use Netgen\BlockManager\Ez\Validator\Constraint as EzConstraints;
use Netgen\BlockManager\Layout\Resolver\TargetTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

class Content implements TargetTypeInterface
{
    /**
     * @var \Netgen\BlockManager\Ez\ContentProvider\ContentExtractorInterface
     */
    protected $contentExtractor;

    public function __construct(ContentExtractorInterface $contentExtractor)
    {
        $this->contentExtractor = $contentExtractor;
    }

    public function getType()
    {
        return 'ezcontent';
    }

    public function getConstraints()
    {
        return array(
            new Constraints\NotBlank(),
            new Constraints\Type(array('type' => 'numeric')),
            new Constraints\GreaterThan(array('value' => 0)),
            new EzConstraints\Content(),
        );
    }

    public function provideValue(Request $request)
    {
        $content = $this->contentExtractor->extractContent($request);

        return $content instanceof APIContent ? $content->id : null;
    }
}
