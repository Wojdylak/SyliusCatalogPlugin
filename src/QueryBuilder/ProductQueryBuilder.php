<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusCatalogPlugin\QueryBuilder;

use BitBag\SyliusCatalogPlugin\Checker\Rule\Elasticsearch\RuleInterface;
use BitBag\SyliusCatalogPlugin\Entity\CatalogRuleInterface;
use BitBag\SyliusElasticsearchPlugin\QueryBuilder\IsEnabledQueryBuilder;
use BitBag\SyliusElasticsearchPlugin\QueryBuilder\QueryBuilderInterface;
use Doctrine\Common\Collections\Collection;
use Elastica\Query\BoolQuery;
use Sylius\Component\Registry\ServiceRegistry;

final class ProductQueryBuilder implements ProductQueryBuilderInterface
{
    private ServiceRegistry $serviceRegistry;

    private QueryBuilderInterface $hasChannelQueryBuilder;

    private IsEnabledQueryBuilder $isEnabledQueryBuilder;

    public function __construct(
        ServiceRegistry $serviceRegistry,
        QueryBuilderInterface $hasChannelQueryBuilder,
        IsEnabledQueryBuilder $isEnabledQueryBuilder,
    ) {
        $this->serviceRegistry = $serviceRegistry;
        $this->hasChannelQueryBuilder = $hasChannelQueryBuilder;
        $this->isEnabledQueryBuilder = $isEnabledQueryBuilder;
    }

    /**
     * @param Collection<int, CatalogRuleInterface> $rules
     */
    public function findMatchingProductsQuery(string $connectingRules, Collection $rules): BoolQuery
    {
        $subQueries = $this->getQueries($rules->toArray());

        if (0 === count($subQueries)) {
            return new BoolQuery();
        }

        $query = new BoolQuery();
        $query->addFilter($this->hasChannelQueryBuilder->buildQuery([]));
        $query->addFilter($this->isEnabledQueryBuilder->buildQuery([]));

        switch ($connectingRules) {
            case RuleInterface::AND:
                if ($subQueries[self::MUST] ?? false) {
                    foreach ($subQueries[self::MUST] as $subQuery) {
                        $query->addFilter($subQuery);
                    }
                }

                if ($subQueries[self::MUST_NOT] ?? false) {
                    foreach ($subQueries[self::MUST_NOT] as $subQuery) {
                        $query->addMustNot($subQuery);
                    }
                }

                break;
            case RuleInterface::OR:
                if ($subQueries[self::MUST] ?? false) {
                    foreach ($subQueries[self::MUST] as $subQuery) {
                        $query->addShould($subQuery);
                        $query->setMinimumShouldMatch(1);
                    }
                }

                if ($subQueries[self::MUST_NOT] ?? false) {
                    $mustNotQuery = new BoolQuery();

                    foreach ($subQueries[self::MUST] as $subQuery) {
                        $mustNotQuery->addMustNot($subQuery);
                    }

                    $query->addShould($mustNotQuery);
                    $query->setMinimumShouldMatch(1);
                }

                break;
            default:
                throw new \InvalidArgumentException('Invalid connecting rule');
        }

        return $query;
    }

    /** @param CatalogRuleInterface[] $rules */
    private function getQueries(array $rules): array
    {
        $queries = [];
        foreach ($rules as $rule) {
            $type = (string) $rule->getType();

            /** @var RuleInterface $ruleChecker */
            $ruleChecker = $this->serviceRegistry->get($type);

            $ruleConfiguration = $rule->getConfiguration();

            $queries[$rule->isNegation() ? self::MUST_NOT : self::MUST][] = $ruleChecker->createSubquery($ruleConfiguration);
        }

        return $queries;
    }
}
