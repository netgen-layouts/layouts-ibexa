<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Validator;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Netgen\Layouts\Ibexa\Validator\Constraint\Content;
use Netgen\Layouts\Ibexa\Validator\ContentValidator;
use Netgen\Layouts\Tests\TestCase\ValidatorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[CoversClass(ContentValidator::class)]
final class ContentValidatorTest extends ValidatorTestCase
{
    private Stub&Repository $repositoryStub;

    private Stub&ContentService $contentServiceStub;

    protected function setUp(): void
    {
        parent::setUp();

        $this->constraint = new Content(allowedTypes: ['user']);
    }

    public function testValidateValid(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->with(self::identicalTo(42))
            ->willReturn(new ContentInfo(['id' => 42, 'contentTypeId' => 24]));

        $this->assertValid(true, 42);
    }

    public function testValidateInvalidWithWrongType(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->with(self::identicalTo(42))
            ->willReturn(new ContentInfo(['id' => 42, 'contentTypeId' => 52]));

        $this->assertValid(false, 42);
    }

    public function testValidateInvalidWithNonExistingContent(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('content', 42));

        $this->assertValid(false, 42);
    }

    public function testValidateNull(): void
    {
        $this->assertValid(true, null);
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "Netgen\Layouts\Ibexa\Validator\Constraint\Content", "Symfony\Component\Validator\Constraints\NotBlank" given');

        $this->constraint = new NotBlank();
        $this->assertValid(true, 'value');
    }

    public function testValidateThrowsUnexpectedTypeExceptionWithInvalidValue(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage('Expected argument of type "scalar", "array" given');

        $this->assertValid(true, []);
    }

    protected function getValidator(): ConstraintValidatorInterface
    {
        $this->contentServiceStub = self::createStub(ContentService::class);

        $contentTypeServiceStub = self::createStub(ContentTypeService::class);
        $contentTypeServiceStub
            ->method('loadContentType')
            ->willReturnCallback(
                static fn (int $type): ContentType => $type === 24 ?
                    new ContentType(['identifier' => 'user']) :
                    new ContentType(['identifier' => 'article']),
            );

        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback) => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getContentService')
            ->willReturn($this->contentServiceStub);

        $this->repositoryStub
            ->method('getContentTypeService')
            ->willReturn($contentTypeServiceStub);

        return new ContentValidator($this->repositoryStub);
    }
}
