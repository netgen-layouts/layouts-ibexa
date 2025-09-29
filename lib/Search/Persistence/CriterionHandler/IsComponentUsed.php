<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Search\Persistence\CriterionHandler;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion as IbexaCriterion;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use Ibexa\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use Netgen\Layouts\API\Values\Value;
use Netgen\Layouts\Ibexa\Search\Contracts\Criterion;

class IsComponentUsed extends CriterionHandler
{
    public function accept(IbexaCriterion $criterion): bool
    {
        return $criterion instanceof Criterion\IsComponentUsed;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        IbexaCriterion $criterion,
        array $languageSettings,
    ): CompositeExpression|string {
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
            ->setParameter('nglayouts_status', Value::STATUS_PUBLISHED, Types::INTEGER)
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
