<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\ConditionType;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\ContentType\ContentType as IbexaContentType;
use Netgen\Layouts\Ibexa\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ibexa\Layout\Resolver\ConditionType\ContentType;
use Netgen\Layouts\Ibexa\Tests\Validator\RepositoryValidatorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

#[CoversClass(ContentType::class)]
final class ContentTypeTest extends TestCase
{
    private Stub&Repository $repositoryStub;

    private ContentType $conditionType;

    private Stub&ContentExtractorInterface $contentExtractorStub;

    private Stub&ContentTypeService $contentTypeServiceStub;

    protected function setUp(): void
    {
        $this->contentExtractorStub = self::createStub(ContentExtractorInterface::class);
        $this->contentTypeServiceStub = self::createStub(ContentTypeService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getContentTypeService')
            ->willReturn($this->contentTypeServiceStub);

        $this->conditionType = new ContentType(
            $this->contentExtractorStub,
        );
    }

    public function testGetType(): void
    {
        self::assertSame('ibexa_content_type', $this->conditionType::getType());
    }

    public function testValidation(): void
    {
        $this->contentTypeServiceStub
            ->method('loadContentTypeByIdentifier')
            ->with(self::identicalTo('identifier'))
            ->willReturn(new IbexaContentType());

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryStub))
            ->getValidator();

        $errors = $validator->validate(['identifier'], $this->conditionType->getConstraints());
        self::assertCount(0, $errors);
    }

    public function testValidationWithInvalidValue(): void
    {
        $this->contentTypeServiceStub
            ->method('loadContentTypeByIdentifier')
            ->with(self::identicalTo('unknown'))
            ->willThrowException(new NotFoundException('content type', 'unknown'));

        $validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(new RepositoryValidatorFactory($this->repositoryStub))
            ->getValidator();

        $errors = $validator->validate(['unknown'], $this->conditionType->getConstraints());
        self::assertNotCount(0, $errors);
    }

    #[DataProvider('matchesDataProvider')]
    public function testMatches(mixed $value, bool $matches): void
    {
        $request = Request::create('/');

        $content = new Content(
            [
                'contentType' => new IbexaContentType(
                    [
                        'identifier' => 'article',
                    ],
                ),
            ],
        );

        $this->contentExtractorStub
            ->method('extractContent')
            ->with(self::identicalTo($request))
            ->willReturn($content);

        self::assertSame($matches, $this->conditionType->matches($request, $value));
    }

    public function testMatchesWithNoContent(): void
    {
        $request = Request::create('/');

        $this->contentExtractorStub
            ->method('extractContent')
            ->with(self::identicalTo($request))
            ->willReturn(null);

        self::assertFalse($this->conditionType->matches($request, ['article']));
    }

    public static function matchesDataProvider(): iterable
    {
        return [
            ['not_array', false],
            [[], false],
            [['article'], true],
            [['news'], false],
            [['article', 'news'], true],
            [['news', 'article'], true],
            [['news', 'video'], false],
        ];
    }
}
