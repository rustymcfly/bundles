<?php declare(strict_types=1);

namespace RustyMcFly\Bundles\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1720336123CreateBundleEntities extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1720336123;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<SQL
create table if not exists rustymcfly_bundles_mapping (
    id binary(16) not null,
    parent_product_id binary(16) not null,
    product_id binary(16) not null,
    quantity int,
    created_at datetime(3) not null,
    updated_at datetime(3),
    primary key (id),
    CONSTRAINT `fk.rustymcfly_bundles_mapping.parent` FOREIGN KEY (parent_product_id)
        REFERENCES product (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT `fk.rustymcfly_bundles_mapping.product` FOREIGN KEY (product_id)
        REFERENCES product (`id`) ON DELETE CASCADE ON UPDATE CASCADE
)
SQL
        );

    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
