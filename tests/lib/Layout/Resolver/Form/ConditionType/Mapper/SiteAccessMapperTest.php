<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\ConditionType\Mapper;

use Netgen\Layouts\Ibexa\Layout\Resolver\Form\ConditionType\Mapper\SiteAccessMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[CoversClass(SiteAccessMapper::class)]
final class SiteAccessMapperTest extends TestCase
{
    private SiteAccessMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SiteAccessMapper(['cro', 'eng']);
    }

    public function testGetFormType(): void
    {
        self::assertSame(ChoiceType::class, $this->mapper->getFormType());
    }

    public function testGetFormOptions(): void
    {
        self::assertSame(
            [
                'choices' => ['cro' => 'cro', 'eng' => 'eng'],
                'choice_translation_domain' => false,
                'multiple' => true,
                'expanded' => true,
            ],
            $this->mapper->getFormOptions(),
        );
    }
}
