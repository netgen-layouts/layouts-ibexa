<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\AdminUI;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use JsonException;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\API\Values\Layout\Layout;
use Netgen\Layouts\API\Values\Value;
use Ramsey\Uuid\Uuid;

use function array_key_exists;
use function array_map;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class ComponentLayoutsLoader
{
    /**
     * @var array<int, array<string, array<int, array<string, mixed>>>>
     */
    private array $layoutsData;

    public function __construct(
        private LayoutService $layoutService,
        private Connection $databaseConnection,
    ) {}

    /**
     * Returns all layouts in which the provided content is used as a component, sorted by name.
     *
     * @return iterable<\Netgen\Layouts\API\Values\Layout\Layout>
     */
    public function loadComponentLayouts(ContentInfo $contentInfo): iterable
    {
        $this->layoutsData ??= $this->loadLayoutsData();

        if (!array_key_exists($contentInfo->id, $this->layoutsData)) {
            return [];
        }

        return array_map(
            fn (array $layoutData): Layout => $this->layoutService->loadLayout(Uuid::fromString($layoutData['uuid'])),
            $this->layoutsData[$contentInfo->id]['layouts'],
        );
    }

    /**
     * @return array<int, array<string, array<int, array<string, mixed>>>>
     */
    public function loadLayoutsData(): array
    {
        $query = $this->databaseConnection->createQueryBuilder();

        $query->select('bt.parameters, l.uuid as layout_uuid, l.name as layout_name')
            ->from('nglayouts_block', 'b')
            ->innerJoin(
                'b',
                'nglayouts_block_translation',
                'bt',
                $query->expr()->and(
                    $query->expr()->eq('b.id', 'bt.block_id'),
                    $query->expr()->eq('b.status', 'bt.status'),
                ),
            )
            ->innerJoin(
                'b',
                'nglayouts_layout',
                'l',
                $query->expr()->and(
                    $query->expr()->eq('b.layout_id', 'l.id'),
                    $query->expr()->eq('b.status', 'l.status'),
                ),
            )
            ->where(
                $query->expr()->and(
                    $query->expr()->eq('b.status', ':status'),
                    $query->expr()->like('b.definition_identifier', ':definition_identifier'),
                ),
            )
            ->setParameter('status', Value::STATUS_PUBLISHED, Types::INTEGER)
            ->setParameter('definition_identifier', 'ibexa_component_%', Types::STRING);

        $layoutsData = [];

        foreach ($query->execute()->fetchAllAssociative() as $dataRow) {
            try {
                $decodedParameters = json_decode($dataRow['parameters'], true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                continue;
            }

            $layoutsData[(int) $decodedParameters['content']]['layouts'][] = [
                'uuid' => $dataRow['layout_uuid'],
                'name' => $dataRow['layout_name'],
            ];
        }

        return $layoutsData;
    }
}
