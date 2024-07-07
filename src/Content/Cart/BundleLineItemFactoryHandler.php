<?php

namespace RustyMcFly\Bundles\Content\Cart;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryHandler\LineItemFactoryInterface;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class BundleLineItemFactoryHandler implements LineItemFactoryInterface
{

    const TYPE = 'bundle';

    public function __construct(private readonly LineItemFactoryRegistry $registry)
    {
    }

    public function supports(string $type): bool
    {
        return self::TYPE === $type;
    }

    public function create(array $data, SalesChannelContext $context): LineItem
    {
        $lineItem = new LineItem($data["id"], self::TYPE, $data["referencedId"], (int)$data["quantity"]);
        $lineItem->setRemovable(true);
        $lineItem->setStackable(true);
        foreach ($data['children'] as $child) {
            $child["quantity"] = (int)$child["quantity"];
            $child["stackable"] = $child["stackable"] === "1";
            $child["removable"] = $child["removable"] === "1";
            $childItem = $this->registry->create($child, $context);
            $childItem->setLabel($child["label"]);
            $childItem->setRemovable(false);
            $childItem->setStackable(true);
            $childItem->setPayloadValue("productNumber", $child["productNumber"]);
            $childItem->addPayloadProtection(['productNumber' => true]);
            $lineItem->addChild($childItem);
        }

        return $lineItem;
    }

    public function update(LineItem $lineItem, array $data, SalesChannelContext $context): void
    {
        // TODO: Implement update() method.
    }

}
