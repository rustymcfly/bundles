<?php

namespace RustyMcFly\Bundles\Subscriber;

use RustyMcFly\Bundles\Content\BundleMapping\BundleMappingCollection;
use RustyMcFly\Bundles\Content\BundleMapping\BundleMappingEntity;
use Shopware\Core\Content\Product\Cart\ProductGateway;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductLoadedSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EntityRepository $bundleRepository, private readonly ProductGateway $productGateway)
    {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'sales_channel.product.loaded' => 'onSalesChannelProductLoaded',
            'product.loaded' => 'onProductLoaded'
        ];
    }

    public function onSalesChannelProductLoaded(SalesChannelEntityLoadedEvent $event)
    {
        foreach ($event->getEntities() as $product) {
            $bundleContents = $product->getExtension('bundleContents');
            if ($bundleContents && count($bundleContents)) {
                $products = $this->productGateway->get($bundleContents->getProducts()->getIds(), $event->getSalesChannelContext());
                $bundleContents->map(fn(BundleMappingEntity $entity) => $entity->setProduct($products->get($entity->getProductId())));
                $product->addExtension('bundleContents', $bundleContents);
            }
        }
    }

    public function onProductLoaded(EntityLoadedEvent $event)
    {
        foreach ($event->getEntities() as $product) {
            if (!$product->hasExtension('bundleContents')) {
                $criteria = new Criteria();
                $criteria->addAssociation('product');
                $criteria->addFilter(new EqualsFilter('parentId', $product->getId()));
                /**
                 * @var BundleMappingCollection $bundleContents
                 */
                $bundleContents = $this->bundleRepository->search($criteria, $event->getContext())->getEntities();
                $product->addExtension('bundleContents', $bundleContents);
            }
        }
    }
}
