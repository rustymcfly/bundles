<?php

namespace RustyMcFly\Bundles\Content\BundleMapping;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class BundleMappingDefinition extends EntityDefinition
{

    public function getEntityName(): string
    {
        return 'rustymcfly_bundles_mapping';
    }

    public function getCollectionClass(): string
    {
        return BundleMappingCollection::class;
    }

    public function getEntityClass(): string
    {
        return BundleMappingEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class)),
            (new ManyToOneAssociationField('parent', 'parent_product_id', ProductDefinition::class)),
            (new IntField('quantity', 'quantity', 1)),
            (new FkField('product_id', 'productId', ProductDefinition::class, 'id'))->addFlags(new Required()),
            (new FkField('parent_product_id', 'parentId', ProductDefinition::class, 'id'))->addFlags( new Required()),
        ]);
    }
}
