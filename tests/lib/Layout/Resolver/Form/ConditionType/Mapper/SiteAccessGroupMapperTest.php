<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\ConditionType\Mapper;

use Netgen\Layouts\Ibexa\Layout\Resolver\Form\ConditionType\Mapper\SiteAccessGroupMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[CoversClass(SiteAccessGroupMapper::class)]
final class SiteAccessGroupMapperTest extends TestCase
{
    private SiteAccessGroupMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SiteAccessGroupMapper(
            [
                'frontend' => ['eng'],
                'backend' => ['admin'],
            ],
        );
    }

    public function testGetFormType(): void
    {
        self::assertSame(ChoiceType::class, $this->mapper->getFormType());
    }

    public function testGetFormOptions(): void
    {
        self::assertSame(
            [
                'choices' => ['frontend' => 'frontend', 'backend' => 'backend'],
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
            ],
            $this->mapper->getFormOptions(),
        );
    }
}
