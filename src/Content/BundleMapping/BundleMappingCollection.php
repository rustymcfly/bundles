<?php

namespace RustyMcFly\Bundles\Content\BundleMapping;

use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class BundleMappingCollection extends EntityCollection
{
    public function getProducts(): ProductCollection
    {
        return new ProductCollection(array_values($this->map(fn($map) => $map->getProduct())));
    }

    public function getPrice() {
        $price = new PriceCollection();
        foreach ($this->getProducts() as $product) {
            $price->add($product->calculatedPrice);
        }
        return $price->sum();
    }

}
