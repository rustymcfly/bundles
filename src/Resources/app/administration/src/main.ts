const { Component} = Shopware

Component.override('sw-product-detail-base', () => import ('./module/sw-product/views/sw-product-detail-base'))
Component.override('sw-product-detail', () => import ('./module/sw-product/page/sw-product-detail'))
