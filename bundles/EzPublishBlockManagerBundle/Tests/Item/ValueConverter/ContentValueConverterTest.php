<?php

namespace Netgen\Bundle\EzPublishBlockManagerBundle\Tests\Item\ValueConverter;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\Core\Helper\TranslationHelper;
use Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter;
use PHPUnit\Framework\TestCase;

class ContentValueConverterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $locationServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $translationHelperMock;

    /**
     * @var \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter
     */
    protected $valueConverter;

    public function setUp()
    {
        $this->locationServiceMock = $this->createMock(LocationService::class);

        $this->locationServiceMock
            ->expects($this->any())
            ->method('loadLocation')
            ->with($this->isType('int'))
            ->will($this->returnCallback(
                function ($id) { return new Location(array('id' => $id, 'invisible' => false)); })
            );

        $this->translationHelperMock = $this->createMock(TranslationHelper::class);

        $this->translationHelperMock
            ->expects($this->any())
            ->method('getTranslatedContentNameByContentInfo')
            ->with($this->isInstanceOf(ContentInfo::class))
            ->will($this->returnValue('Cool name'));

        $this->valueConverter = new ContentValueConverter(
            $this->locationServiceMock,
            $this->translationHelperMock
        );
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter::__construct
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter::supports
     */
    public function testSupports()
    {
        self::assertTrue($this->valueConverter->supports(new ContentInfo()));
        self::assertFalse($this->valueConverter->supports(new Location()));
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter::getValueType
     */
    public function testGetValueType()
    {
        self::assertEquals(
            'ezcontent',
            $this->valueConverter->getValueType(
                new ContentInfo()
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter::getId
     */
    public function testGetId()
    {
        self::assertEquals(
            24,
            $this->valueConverter->getId(
                new ContentInfo(array('id' => 24, 'mainLocationId' => 42))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter::getName
     */
    public function testGetName()
    {
        self::assertEquals(
            'Cool name',
            $this->valueConverter->getName(
                new ContentInfo(array('mainLocationId' => 42))
            )
        );
    }

    /**
     * @covers \Netgen\Bundle\EzPublishBlockManagerBundle\Item\ValueConverter\ContentValueConverter::getIsVisible
     */
    public function testGetIsVisible()
    {
        self::assertTrue(
            $this->valueConverter->getIsVisible(
                new ContentInfo(array('mainLocationId' => 42))
            )
        );
    }
}