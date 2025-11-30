<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\AdminUI;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use JsonException;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\Persistence\Values\Status;
use Ramsey\Uuid\Uuid;

use function array_column;
use function array_key_exists;
use function array_merge;
use function count;
use function json_decode;

use const JSON_THROW_ON_ERROR;

final class ComponentLayoutsLoader
{
    public function __construct(
        private LayoutService $layoutService,
        private Connection $databaseConnection,
    ) {}

    /**
     * Returns all layouts in which the provided content is used as a component, sorted by name.
     *
     * @return array<string, array{
     *     layout?: \Netgen\Layouts\API\Values\Layout\Layout,
     *     uuid: string,
     *     name: string,
     *     view_types: string[]
     * }>
     */
    public function loadComponentLayouts(ContentInfo $contentInfo): iterable
    {
        $layoutsData = $this->loadLayoutsData();

        if (!array_key_exists($contentInfo->id, $layoutsData)) {
            return [];
        }

        $componentData = $layoutsData[$contentInfo->id];

        foreach ($componentData['layouts'] as $uuid => $layoutData) {
            $componentData['layouts'][$uuid]['layout'] = $this->layoutService->loadLayout(Uuid::fromString($uuid));
        }

        return $componentData['layouts'];
    }

    /**
     * @return array<int, array{
     *     layouts: array<string, array{
     *         uuid: string,
     *         name: string,
     *         view_types: string[]
     *     }>,
     *     count: int
     * }>
     */
    public function loadLayoutsData(): array
    {
        $query = $this->databaseConnection->createQueryBuilder();

        $query->select('b.view_type, bt.parameters, l.uuid as layout_uuid, l.name as layout_name')
            ->from('nglayouts_block', 'b')
            ->innerJoin(
                'b',
                'nglayouts_block_translation',
                'bt',
                (string) $query->expr()->and(
                    $query->expr()->eq('b.id', 'bt.block_id'),
                    $query->expr()->eq('b.status', 'bt.status'),
                ),
            )
            ->innerJoin(
                'b',
                'nglayouts_layout',
                'l',
                (string) $query->expr()->and(
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
            ->setParameter('status', Status::Published->value, Types::INTEGER)
            ->setParameter('definition_identifier', 'ibexa_component_%', Types::STRING);

        $layoutsData = [];

        foreach ($query->fetchAllAssociative() as $dataRow) {
            try {
                $decodedParameters = json_decode($dataRow['parameters'], true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                continue;
            }

            $layoutsData[(int) $decodedParameters['content']]['layouts'][$dataRow['layout_uuid']] ??= [
                'uuid' => $dataRow['layout_uuid'],
                'name' => $dataRow['layout_name'],
                'view_types' => [],
            ];

            $layoutsData[(int) $decodedParameters['content']]['layouts'][$dataRow['layout_uuid']]['view_types'][] = $dataRow['view_type'];
        }

        foreach ($layoutsData as $componentId => $componentData) {
            $layoutsData[$componentId]['count'] = count(array_merge(...array_column($componentData['layouts'], 'view_types')));
        }

        return $layoutsData;
    }
}
