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

declare(strict_types=1);

namespace ViraXpress\Swatches\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Catalog\Model\Product;

class Options extends Action
{

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Configurable $configurable
     * @param ProductRepositoryInterface $productRepository
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        Configurable $configurable,
        ProductRepositoryInterface $productRepository,
        JsonFactory $resultJsonFactory
    ) {
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Execute option action
     *
     * @return void
     */
    public function execute()
    {
        $productId = $this->getRequest()->getParam('id');
        $childIds = $this->getRequest()->getParam('child');
        $attrCode = $this->getRequest()->getParam('code');
        $optionId = $this->getRequest()->getParam('opid');

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId);
        if ($product->getTypeId() != ConfigurableType::TYPE_CODE) {
            return [];
        }
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productTypeInstance */
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($product->getStoreId(), $product);

        $attributes = $productTypeInstance->getConfigurableAttributes($product);
        $superAttributeList = $this->getSuperAttributeList($attributes, $attrCode);

        $options = $this->configurable->getChildrenIds($productId);
        $simpleProductId = array_values($options[0]);

        $result = array_diff($simpleProductId, explode(",", $childIds));
        $response = $this->getResponseData($result, $superAttributeList, $attrCode);

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * Get list of super attributes excluding the specified attribute code
     *
     * @param array $attributes
     * @param string $attrCode
     * @return array
     */
    private function getSuperAttributeList($attributes, string $attrCode): array
    {
        $superAttributeList = [];
        foreach ($attributes as $_attribute) {
            $attributeCode = $_attribute->getProductAttribute()->getAttributeCode();
            if ($attributeCode !== $attrCode) {
                $superAttributeList[] = $attributeCode;
            }
        }
        return $superAttributeList;
    }

    /**
     * Get response data
     *
     * @param array $result
     * @param array $superAttributeList
     * @param string $attrCode
     * @return array
     */
    private function getResponseData(array $result, array $superAttributeList, string $attrCode): array
    {
        $response = [];
        foreach ($result as $res) {
            /** @var Product $childProduct */
            $childProduct = $this->productRepository->getById($res);
            foreach ($superAttributeList as $list) {
                $response[$childProduct->getData($attrCode)] = $childProduct->getData($list);
            }
        }
        return $response;
    }
}
