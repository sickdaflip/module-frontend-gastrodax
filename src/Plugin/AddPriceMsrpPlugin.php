<?php
/*
 * Copyright (C) Philipp Breitsprecher, Inc - All Rights Reserved
 * @project Mage2 GD
 * @file AddPriceMsrpPlugin.php
 * @author Philipp Breitsprecher
 * @date 02.10.25, 09:08
 * @email philippbreitsprecher@gmail.com
 */

declare(strict_types=1);

namespace Sickdaflip\Theme\Plugin;

use Sickdaflip\Theme\Block\Price\Msrp;
use Hyva\Theme\Service\CurrentTheme;
use Magento\Catalog\Block\Product\Price;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Registry;

class AddPriceMsrpPlugin
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var CurrentTheme
     */
    private $currentTheme;

    public function __construct(Registry $registry, CurrentTheme $currentTheme)
    {
        $this->registry = $registry;
        $this->currentTheme = $currentTheme;
    }

    public function afterToHtml(Price $priceBlock, string $result): string
    {
        if (!$this->currentTheme->isHyva()) {
            return $result;
        }

        $saleableItem = $this->getSaleableItem();
        if (!$saleableItem || !$this->shouldAddPriceMsrp($result, $saleableItem)) {
            return $result;
        }

        $block = $priceBlock->getLayout()->getBlock('product.price.msrp');
        if ($block instanceof Msrp) {
            $block->setSaleableItem($saleableItem);
            $result .= $block->toHtml();
        }

        return $result;
    }

    private function getSaleableItem(): ?SaleableInterface
    {
        $product = $this->registry->registry('current_product');
        if ($product instanceof SaleableInterface) {
            return $product;
        }

        return null;
    }

    private function shouldAddPriceMsrp(string $result, SaleableInterface $saleableItem): bool
    {
        if (trim($result) === '') {
            return false;
        }

        if ($saleableItem->getTypeId() === 'grouped') {
            return false;
        }

        return true;
    }
}