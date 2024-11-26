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

namespace ViraXpress\Swatches\Block;

use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Swatch extends Template
{
    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var SwatchHelper
     */
    protected $swatchHelper;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param EavConfig $eavConfig
     * @param SwatchHelper $swatchHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Http $request
     * @param array $data
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        EavConfig $eavConfig,
        SwatchHelper $swatchHelper,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Http $request,
        array $data = []
    ) {
        $this->request = $request;
        $this->eavConfig = $eavConfig;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->swatchHelper = $swatchHelper;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    /**
     * Check if the provided attribute is a swatch attribute.
     *
     * @param string $attr The attribute code.
     * @return bool Returns true if the attribute is a swatch attribute, false otherwise.
     */
    public function isSwatchAttr($attr)
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', $attr);
        return $this->swatchHelper->isSwatchAttribute($attribute);
    }

    /**
     * Get the locale code of the current store.
     *
     * @return string The locale code.
     */
    public function getStoreLocale()
    {
        $storeId =  $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Get the currency code of the current store.
     *
     * @return string The currency code.
     */
    public function getCurrentCurrencyCode(): string
    {
        return $this->priceCurrency->getCurrency()->getCurrencyCode();
    }

    /**
     * Get the full action name of the current request.
     *
     * @return string The full action name.
     */
    public function getFullActionName(): string
    {
        return $this->request->getFullActionName();
    }

    /**
     * Retrieve configuration value from admin settings
     *
     * @param string $fieldPath
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigValue($fieldPath)
    {
        $storeId =  $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(
            $fieldPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
