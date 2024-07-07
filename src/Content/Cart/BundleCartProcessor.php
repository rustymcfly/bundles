<?php

namespace RustyMcFly\Bundles\Content\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\ReferencePriceDefinition;
use Shopware\Core\Content\Product\Cart\ProductGateway;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class BundleCartProcessor implements CartProcessorInterface, CartDataCollectorInterface
{
    public function __construct(private readonly ProductGateway $productGateway, private readonly QuantityPriceCalculator $calculator)
    {
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $items = new LineItemCollection($original->getLineItems()->filterFlatByType(BundleLineItemFactoryHandler::TYPE));

        foreach ($items as $item) {
            /**
             * @var $product SalesChannelProductEntity
             */
            $product = $data->get($this->buildKey($item));

            $item->setLabel($product->getTranslation('name'));
            $item->setCover($product->getCover()->getMedia());

            if ($product->getCustomFieldsValue('calculatePriceByChildren')) {
                $childPrices = $item->getChildren()->getPrices()->sum();
                $item->setPriceDefinition(new QuantityPriceDefinition($childPrices->getUnitPrice(), $childPrices->getTaxRules(), $item->getQuantity()));
                $item->setPrice($this->calculator->calculate($item->getPriceDefinition(), $context));
            } else {
                $item->setPriceDefinition($this->getPriceDefinition($product, $context, $item->getQuantity()));
                $item->setPrice($this->calculator->calculate($item->getPriceDefinition(), $context));
            }

            $toCalculate->add($item);
        }
    }

    private function getPriceDefinition(SalesChannelProductEntity $product, SalesChannelContext $context, int $quantity): QuantityPriceDefinition
    {
        if ($product->getCalculatedPrices()->count() === 0) {
            return $this->buildPriceDefinition($product->getCalculatedPrice(), $quantity);
        }

        // keep loop reference to $price variable to get last quantity price in case of "null"
        $price = $product->getCalculatedPrice();
        foreach ($product->getCalculatedPrices() as $price) {
            if ($quantity <= $price->getQuantity()) {
                break;
            }
        }

        return $this->buildPriceDefinition($price, $quantity);
    }

    private function buildPriceDefinition(CalculatedPrice $price, int $quantity): QuantityPriceDefinition
    {
        $definition = new QuantityPriceDefinition($price->getUnitPrice(), $price->getTaxRules(), $quantity);
        if ($price->getListPrice() !== null) {
            $definition->setListPrice($price->getListPrice()->getPrice());
        }

        if ($price->getReferencePrice() !== null) {
            $definition->setReferencePriceDefinition(
                new ReferencePriceDefinition(
                    $price->getReferencePrice()->getPurchaseUnit(),
                    $price->getReferencePrice()->getReferenceUnit(),
                    $price->getReferencePrice()->getUnitName()
                )
            );
        }

        return $definition;
    }


    private function buildKey(LineItem $lineItem)
    {
        return 'bundle-' . $lineItem->getId();
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $items = new LineItemCollection($original->getLineItems()->filterFlatByType(BundleLineItemFactoryHandler::TYPE));

        $toComplete = $items->filter(fn(LineItem $lineItem) => !$data->has($this->buildKey($lineItem)));
        if ($toComplete->count()) {
            $products = $this->productGateway->get($toComplete->getReferenceIds(), $context);

            foreach ($toComplete as $item) {
                $data->set($this->buildKey($item), $products->get($item->getReferencedId()));
            }
        }
    }
}
