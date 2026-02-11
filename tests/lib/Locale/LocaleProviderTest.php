<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Locale;

use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Netgen\Layouts\Ibexa\Locale\LocaleProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

use function array_keys;
use function array_values;

#[CoversClass(LocaleProvider::class)]
final class LocaleProviderTest extends TestCase
{
    private Stub&LanguageService $languageServiceStub;

    private Stub&LocaleConverterInterface $localeConverterStub;

    private Stub&ConfigResolverInterface $configResolverStub;

    private LocaleProvider $localeProvider;

    protected function setUp(): void
    {
        $this->languageServiceStub = self::createStub(LanguageService::class);
        $this->localeConverterStub = self::createStub(LocaleConverterInterface::class);
        $this->configResolverStub = self::createStub(ConfigResolverInterface::class);

        $this->localeProvider = new LocaleProvider(
            $this->languageServiceStub,
            $this->localeConverterStub,
            $this->configResolverStub,
        );
    }

    public function testGetAvailableLocales(): void
    {
        $this->languageServiceStub
            ->method('loadLanguages')
            ->willReturn(
                [
                    new Language(['languageCode' => 'eng-GB', 'enabled' => true]),
                    new Language(['languageCode' => 'ger-DE', 'enabled' => false]),
                    new Language(['languageCode' => 'cro-HR', 'enabled' => true]),
                ],
            );

        $this->localeConverterStub
            ->method('convertToPOSIX')
            ->willReturnMap(
                [
                    ['eng-GB', 'en'],
                    ['cro-HR', 'hr'],
                ],
            );

        $availableLocales = $this->localeProvider->getAvailableLocales();

        self::assertSame(['hr', 'en'], array_keys($availableLocales));
        self::assertSame(['Croatian', 'English'], array_values($availableLocales));
    }

    public function testGetAvailableLocalesWithInvalidPosixLocale(): void
    {
        $this->languageServiceStub
            ->method('loadLanguages')
            ->willReturn(
                [
                    new Language(['languageCode' => 'unknown', 'enabled' => true]),
                ],
            );

        $this->localeConverterStub
            ->method('convertToPOSIX')
            ->willReturn(null);

        $availableLocales = $this->localeProvider->getAvailableLocales();

        self::assertSame([], $availableLocales);
    }

    public function testGetRequestLocales(): void
    {
        $this->configResolverStub
            ->method('getParameter')
            ->willReturn(['eng-GB', 'ger-DE', 'unknown', 'cro-HR']);

        $this->languageServiceStub
            ->method('loadLanguageListByCode')
            ->willReturn(
                [
                    new Language(['languageCode' => 'eng-GB', 'enabled' => true]),
                    new Language(['languageCode' => 'ger-DE', 'enabled' => false]),
                    new Language(['languageCode' => 'cro-HR', 'enabled' => true]),
                ],
            );

        $this->localeConverterStub
            ->method('convertToPOSIX')
            ->willReturnMap(
                [
                    ['eng-GB', 'en'],
                    ['cro-HR', 'hr'],
                ],
            );

        $requestLocales = $this->localeProvider->getRequestLocales(Request::create(''));

        self::assertSame(['en', 'hr'], $requestLocales);
    }

    public function testGetRequestLocalesWithInvalidPosixLocale(): void
    {
        $this->configResolverStub
            ->method('getParameter')
            ->willReturn(['eng-GB']);

        $this->languageServiceStub
            ->method('loadLanguageListByCode')
            ->willReturn(
                [
                    new Language(['languageCode' => 'eng-GB', 'enabled' => true]),
                ],
            );

        $this->localeConverterStub
            ->method('convertToPOSIX')
            ->willReturn(null);

        $requestLocales = $this->localeProvider->getRequestLocales(Request::create(''));

        self::assertSame([], $requestLocales);
    }

    public function testGetRequestLocalesWithNonExistingPosixLocale(): void
    {
        $this->configResolverStub
            ->method('getParameter')
            ->willReturn(['eng-GB']);

        $this->languageServiceStub
            ->method('loadLanguageListByCode')
            ->willReturn(
                [
                    new Language(['languageCode' => 'eng-GB', 'enabled' => true]),
                ],
            );

        $this->localeConverterStub
            ->method('convertToPOSIX')
            ->willReturn('unknown');

        $requestLocales = $this->localeProvider->getRequestLocales(Request::create(''));

        self::assertSame([], $requestLocales);
    }
}
