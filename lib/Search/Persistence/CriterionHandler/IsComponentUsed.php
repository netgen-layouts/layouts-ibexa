<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Search\Persistence\CriterionHandler;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\Layouts\Ibexa\Search\Contracts\Criterion;
use Netgen\Layouts\Persistence\Values\Status;

final class IsComponentUsed extends CriterionHandler
{
    public function accept(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\IsComponentUsed;
    }

    /**
     * @param mixed[] $languageSettings
     */
    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        CriterionInterface $criterion,
        array $languageSettings,
    ): string {
        $subSelect = $this->connection->createQueryBuilder();
        $subSelect
            ->select('DISTINCT JSON_UNQUOTE(JSON_EXTRACT(parameters, "$.content"))')
            ->from('nglayouts_block', 'b')
            ->innerJoin(
                'b',
                'nglayouts_block_translation',
                'bt',
                $subSelect->expr()->and(
                    $subSelect->expr()->eq('b.id', 'bt.block_id'),
                    $subSelect->expr()->eq('b.status', 'bt.status'),
                ),
            )
            ->where(
                $subSelect->expr()->and(
                    $subSelect->expr()->eq('b.status', ':nglayouts_status'),
                    $subSelect->expr()->like('b.definition_identifier', ':nglayouts_definition_identifier'),
                    $subSelect->expr()->isNotNull('JSON_EXTRACT(parameters, "$.content")'),
                ),
            );

        $queryBuilder
            ->setParameter('nglayouts_status', Status::Published->value, Types::INTEGER)
            ->setParameter('nglayouts_definition_identifier', 'ibexa_component_%', Types::STRING);

        if ((bool) ($criterion->value[0] ?? true)) {
            return $queryBuilder->expr()->in(
                'c.id',
                $subSelect->getSQL(),
            );
        }

        return $queryBuilder->expr()->notIn(
            'c.id',
            $subSelect->getSQL(),
        );
    }
}
