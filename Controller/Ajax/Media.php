<?php
/**
 * ViraXpress - https://www.viraxpress.com
 *
 * LICENSE AGREEMENT
 *
 * This file is part of the ViraXpress package and is licensed under the ViraXpress license agreement.
 * You can view the full license at:
 * https://www.viraxpress.com/license
 *
 * By utilizing this file, you agree to comply with the terms outlined in the ViraXpress license.
 *
 * DISCLAIMER
 *
 * Modifications to this file are discouraged to ensure seamless upgrades and compatibility with future releases.
 *
 * @category    ViraXpress
 * @package     ViraXpress_Swatches
 * @author      ViraXpress
 * @copyright   © 2024 ViraXpress (https://www.viraxpress.com/)
 * @license     https://www.viraxpress.com/license
 */

namespace ViraXpress\Swatches\Controller\Ajax;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Swatches\Helper\Data as SwatchHelper;
use ViraXpress\Store\Helper\Cssconfig;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Json as ResultJson;

/**
 * Provide product media data.
 */
class Media extends \Magento\Swatches\Controller\Ajax\Media
{

    /**
     * @var Cssconfig
     */
    protected $cssConfig;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var SwatchHelper
     */
    private $swatchHelper;

    /**
     * @var PageCacheConfig
     */
    protected $pageCacheConfig;

    /**
     * @param Cssconfig $cssConfig
     * @param Image $imageHelper
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param SwatchHelper $swatchHelper
     * @param PageCacheConfig $pageCacheConfig
     */
    public function __construct(
        Cssconfig $cssConfig,
        ImageHelper $imageHelper,
        Context $context,
        ProductFactory $productFactory,
        SwatchHelper $swatchHelper,
        PageCacheConfig $pageCacheConfig
    ) {
        $this->cssConfig = $cssConfig;
        $this->imageHelper = $imageHelper;
        $this->productFactory = $productFactory;
        $this->swatchHelper = $swatchHelper;
        $this->pageCacheConfig = $pageCacheConfig;
        parent::__construct($context, $productFactory, $swatchHelper, $pageCacheConfig);
    }

    /**
     * Get product media for specified configurable product variation
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $productMedia = [];

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        /** @var \Magento\Framework\App\ResponseInterface $response */
        $response = $this->getResponse();

        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $this->productFactory->create()->load($productId);
            $layout = $this->getRequest()->getParam('layout');

            $imageDisplayArea = $this->getImageDisplayArea($layout);
            $imageConfig = $this->getImageConfig($layout);

            if ($product->getId() && $product->getStatus() == Status::STATUS_ENABLED) {
                $productMedia = $this->swatchHelper->getProductMediaGallery($product);
                $imageWidth = ($imageConfig['width']) ? $imageConfig['width'] : 240;
                $imageHeight = ($imageConfig['height']) ? $imageConfig['height'] : 300;
                $productImageConfig = $this->imageHelper->init($product, $imageDisplayArea)->resize($imageWidth, $imageHeight);
                $productImageUrl = $productImageConfig->getUrl();
                $productMedia['medium'] = $productImageUrl;
            }
            $resultJson->setHeader('X-Magento-Tags', implode(',', $product->getIdentities()));
            $response->setPublicHeaders($this->pageCacheConfig->getTtl());
        }
        $resultJson->setData($productMedia);
        return $resultJson;
    }

    /**
     * Get image display area based on layout
     *
     * @param string $layout
     * @return string
     */
    private function getImageDisplayArea(string $layout): string
    {
        switch ($layout) {
            case 'catalog_category_view':
                return 'category_page_list';
            case 'catalog_product_view':
            case 'checkout_cart_index':
                return 'related_products_list';
            default:
                return 'new_products_content_widget_grid';
        }
    }

    /**
     * Get image configuration based on layout
     *
     * @param string $layout
     * @return array
     */
    private function getImageConfig(string $layout): array
    {
        switch ($layout) {
            case 'catalog_category_view':
                return $this->cssConfig->getConfig('viraxpress_config/image_resize/product_list_image_resize');
            case 'catalog_product_view':
            case 'checkout_cart_index':
                return $this->cssConfig->getConfig('viraxpress_config/image_resize/product_related_cross_up_image_resize');
            default:
                return $this->cssConfig->getConfig('viraxpress_config/image_resize/product_carousel_image_resize');
        }
    }
}
