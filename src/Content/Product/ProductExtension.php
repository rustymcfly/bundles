<?php

namespace RustyMcFly\Bundles\Content\Product;

use RustyMcFly\Bundles\Content\BundleMapping\BundleMappingDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{

    /**
     * @inheritDoc
     */
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {

        $collection->add(
            (new ManyToManyAssociationField('bundleContents', ProductDefinition::class, BundleMappingDefinition::class, 'parent_product_id', 'product_id'))
        );
    }

}
