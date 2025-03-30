<?php

namespace RustyMcFly\Bundles\Content\Product;

use RustyMcFly\Bundles\Content\BundleMapping\BundleMappingCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductPriceCalculator extends AbstractProductPriceCalculator
{


    public function __construct(private readonly AbstractProductPriceCalculator $decorated)
    {
    }

    public function getDecorated(): AbstractProductPriceCalculator
    {
        return $this->decorated;
    }

    /**
     * @inheritDoc
     */
    public function calculate(iterable $products, SalesChannelContext $context): void
    {
        $this->decorated->calculate($products, $context);
        foreach ($products as $product) {
            if($product->getCustomFieldsValue('calculatePriceByChildren') && $product->hasExtension('bundleContents')) {
                $bundles = $product->getExtension('bundleContents');
                foreach ($bundles->getProducts() as $bundleProduct) {
                    $bundleProduct->cheapestPrice = null;
                }
                $this->decorated->calculate($bundles->getProducts(), $context);
                foreach ($bundles as $bundle) {
                    /**
                     * @var $oldPrice CalculatedPrice
                     */
                    $oldPrice = $bundle->getProduct()->calculatedPrice;

                    $price = new CalculatedPrice(
                        $oldPrice->getUnitPrice() * $bundle->getQuantity(),
                        $oldPrice->getTotalPrice() * $bundle->getQuantity(),
                        $oldPrice->getCalculatedTaxes(),
                        $oldPrice->getTaxRules(),
                         $bundle->getQuantity() ?? 1
                    );
                    $bundle->getProduct()->calculatedPrice = $price;
                    $bundle->getProduct()->calculatedPrices = new PriceCollection();
                }

                $product->calculatedPrice = $bundles->getPrice();
                $product->calculatedPrices = new PriceCollection();
            }
        }
    }
}
