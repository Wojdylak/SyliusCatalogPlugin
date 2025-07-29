<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusCatalogPlugin\Checker\Rule\Elasticsearch;

use BitBag\SyliusElasticsearchPlugin\Formatter\StringFormatterInterface;
use BitBag\SyliusElasticsearchPlugin\PropertyBuilder\AttributeBuilder;
use BitBag\SyliusElasticsearchPlugin\PropertyNameResolver\ConcatedNameResolverInterface;
use Elastica\Query\AbstractQuery;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use Sylius\Component\Attribute\AttributeType\DateAttributeType;
use Sylius\Component\Attribute\AttributeType\DatetimeAttributeType;
use Sylius\Component\Attribute\Model\AttributeInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

final class AttributeRule implements RuleInterface
{
    private StringFormatterInterface $stringFormatter;

    private LocaleContextInterface $localeContext;

    private ConcatedNameResolverInterface $attributeNameResolver;

    public function __construct(
        StringFormatterInterface $stringFormatter,
        LocaleContextInterface $localeContext,
        ConcatedNameResolverInterface $attributeNameResolver,
    ) {
        $this->stringFormatter = $stringFormatter;
        $this->localeContext = $localeContext;
        $this->attributeNameResolver = $attributeNameResolver;
    }

    public function createSubquery(array $configuration): AbstractQuery
    {
        $attribute = $configuration['attribute'];

        if (!$attribute instanceof AttributeInterface) {
            return new BoolQuery();
        }

        $paramName = \sprintf(
            '%s_%s.keyword',
            $this->attributeNameResolver->resolvePropertyName($attribute->getCode()),
            $this->localeContext->getLocaleCode(),
        );

        if (in_array($attribute->getStorageType(), [DateAttributeType::TYPE, DatetimeAttributeType::TYPE], true)) {
            $value = $this->getDateAttributeValue($configuration);
        } else {
            $value = $this->stringFormatter->formatToLowercaseWithoutSpaces($configuration['value']);
        }

        $term = new Term();
        $term->setTerm($paramName, $value);

        /* @phpstan-ignore-next-line Elastica\Query\Term Class extended by Elastica\Query\AbstractQuery*/
        return $term;
    }

    private function getDateAttributeValue(array $configuration): string
    {
        $storageType = $configuration['attribute']->getStorageType();
        $format = $configuration['attribute']->getConfiguration()['format'] ??
            (
                DateAttributeType::TYPE === $storageType ?
                AttributeBuilder::DEFAULT_DATE_FORMAT :
                AttributeBuilder::DEFAULT_DATE_TIME_FORMAT
            )
        ;

        return (new \DateTime($configuration['value'] ?? 'now'))->format($format);
    }
}
