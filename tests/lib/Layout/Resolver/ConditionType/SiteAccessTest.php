<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\ConditionType;

use Ibexa\Core\MVC\Symfony\SiteAccess as IbexaSiteAccess;
use Netgen\Layouts\Ibexa\Layout\Resolver\ConditionType\SiteAccess;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SiteAccess::class)]
final class SiteAccessTest extends TestCase
{
    use ValidatorTestCaseTrait;

    private SiteAccess $conditionType;

    protected function setUp(): void
    {
        $this->conditionType = new SiteAccess();
    }

    public function testGetType(): void
    {
        self::assertSame('ibexa_site_access', $this->conditionType::getType());
    }

    #[DataProvider('validationDataProvider')]
    public function testValidation(mixed $value, bool $isValid): void
    {
        $validator = $this->createValidator();

        $errors = $validator->validate($value, $this->conditionType->getConstraints());
        self::assertSame($isValid, $errors->count() === 0);
    }

    #[DataProvider('matchesDataProvider')]
    public function testMatches(mixed $value, bool $matches): void
    {
        $request = Request::create('/');
        $request->attributes->set('siteaccess', new IbexaSiteAccess('eng'));

        self::assertSame($matches, $this->conditionType->matches($request, $value));
    }

    public function testMatchesWithNoSiteAccess(): void
    {
        $request = Request::create('/');

        self::assertFalse($this->conditionType->matches($request, ['eng']));
    }

    /**
     * @return iterable<mixed>
     */
    public static function validationDataProvider(): iterable
    {
        return [
            [['cro'], true],
            [['cro', 'eng'], true],
            [['cro', 'unknown'], false],
            [['unknown'], false],
            [[], false],
            [null, false],
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public static function matchesDataProvider(): iterable
    {
        return [
            ['not_array', false],
            [[], false],
            [['eng'], true],
            [['cro'], false],
            [['eng', 'cro'], true],
            [['cro', 'eng'], true],
            [['cro', 'fre'], false],
        ];
    }
}
