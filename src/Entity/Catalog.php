<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace BitBag\SyliusCatalogPlugin\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\TranslatableTrait;
use Sylius\Resource\Model\TranslationInterface;

class Catalog implements CatalogInterface
{
    use TranslatableTrait {
        __construct as protected initializeTranslationsCollection;
    }

    protected ?int $id;

    protected ?\DateTime $startDate;

    protected ?\DateTime $endDate;

    /** @var CatalogRuleInterface[]|Collection<int, CatalogRuleInterface> */
    protected $rules;

    protected ?string $code;

    protected ?string $connectingRules;

    /* @phpstan-ignore-next-line Property is a type Collection*/
    protected $productAssociationRules;

    protected string $productAssociationConnectingRules;

    protected ?string $template;

    protected ?int $displayProducts;

    protected ?string $sortBy;

    protected ?string $sortingType;

    public function __construct()
    {
        $this->initializeTranslationsCollection();

        $this->id = null;
        $this->code = null;
        $this->connectingRules = null;
        $this->template = null;
        $this->displayProducts = null;
        $this->sortBy = null;
        $this->sortingType = null;
        $this->rules = new ArrayCollection();
        $this->productAssociationRules = new ArrayCollection();
    }

    public function getConnectingRules(): ?string
    {
        return $this->connectingRules;
    }

    public function setConnectingRules(?string $connectingRules): void
    {
        $this->connectingRules = $connectingRules;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getRules(): Collection
    {
        return $this->rules;
    }

    public function hasRules(): bool
    {
        return !$this->rules->isEmpty();
    }

    public function hasRule(CatalogRuleInterface $rule): bool
    {
        return $this->rules->contains($rule);
    }

    public function addRule(CatalogRuleInterface $rule): void
    {
        if (!$this->hasRule($rule)) {
            $rule->setCatalog($this);
            $this->rules->add($rule);
        }
    }

    public function removeRule(CatalogRuleInterface $rule): void
    {
        $rule->setCatalog(null);
        $this->rules->removeElement($rule);
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        /* @phpstan-ignore-next-line the getName method exists in the CatalogTranslationInterface class*/
        return $this->getCatalogTranslation()->getName();
    }

    public function setName(?string $name): void
    {
        /* @phpstan-ignore-next-line the setName method exists in the CatalogTranslationInterface class*/
        $this->getCatalogTranslation()->setName($name);
    }

    /** @return CatalogTranslationInterface|TranslationInterface */
    protected function getCatalogTranslation(): TranslationInterface
    {
        return  $this->getTranslation();
    }

    protected function createTranslation(): CatalogTranslation
    {
        return new CatalogTranslation();
    }

    public function getProductAssociationRules(): Collection
    {
        return $this->productAssociationRules;
    }

    public function hasProductAssociationRules(): bool
    {
        return !$this->productAssociationRules->isEmpty();
    }

    public function hasProductAssociationRule(ProductAssociationRuleInterface $rule): bool
    {
        return $this->productAssociationRules->contains($rule);
    }

    public function addProductAssociationRule(ProductAssociationRuleInterface $rule): void
    {
        if (!$this->hasProductAssociationRule($rule)) {
            $rule->setCatalog($this);
            $this->productAssociationRules->add($rule);
        }
    }

    public function removeProductAssociationRule(ProductAssociationRuleInterface $rule): void
    {
        if ($this->hasProductAssociationRule($rule)) {
            $rule->setCatalog(null);
            $this->productAssociationRules->removeElement($rule);
        }
    }

    public function getProductAssociationConnectingRules(): ?string
    {
        return $this->productAssociationConnectingRules;
    }

    public function setProductAssociationConnectingRules(string $productAssociationConnectingRules): void
    {
        $this->productAssociationConnectingRules = $productAssociationConnectingRules;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    public function getDisplayProducts(): ?int
    {
        return $this->displayProducts;
    }

    public function setDisplayProducts(?int $displayProducts): void
    {
        $this->displayProducts = $displayProducts;
    }

    public function getSortingType(): ?string
    {
        return $this->sortingType;
    }

    public function setSortingType(?string $sortingType): void
    {
        $this->sortingType = $sortingType;
    }
}
