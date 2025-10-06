<?php
/*
 * Copyright (C) Philipp Breitsprecher, Inc - All Rights Reserved
 * @project Mage2 GD
 * @file Msrp.php
 * @author Philipp Breitsprecher
 * @date 02.10.25, 09:08
 * @email philippbreitsprecher@gmail.com
 */

namespace Sickdaflip\Theme\Block\Price;

use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Msrp\Pricing\Price\MsrpPrice;

class Msrp extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Pricing\SaleableInterface
     */
    private $saleableItem;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    private $taxCalculation;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxHelper;

    /**
     * Details constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param GroupRepository $groupRepository
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        GroupRepository $groupRepository,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Helper\Data $taxHelper,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->taxCalculation = $taxCalculation;
        $this->taxHelper = $taxHelper;

        parent::__construct($context, $data);
    }

    /**
     * Se saleable item
     *
     * @param \Magento\Framework\Pricing\SaleableInterface $saleableItem
     */
    public function setSaleableItem(\Magento\Framework\Pricing\SaleableInterface $saleableItem)
    {
        $this->saleableItem = $saleableItem;
        $this->unsetData('tax_rate');
    }

    /**
     * Get formatted tax rate
     *
     * @return string
     */
    public function getTaxRate()
    {
        if (!$this->hasData('tax_rate')) {
            $this->setData('tax_rate', $this->getTaxPercentBySaleableItem());
        }

        return $this->getData('tax_rate');
    }

    /**
     * Get Msrp Excl Tax
     *
     * @return string
     */
    public function getMsrpPriceExclTax()
    {
        $MsrpPrice = $this->saleableItem->getMsrp();
        $finalPrice = $this->saleableItem->getFinalPrice();
        if ($MsrpPrice > $finalPrice) {
            return $MsrpPrice;
        } else {
            return null;
        }
    }

    /**
     * Get Msrp Incl Tax
     *
     * @return string
     */
    public function getMsrpPriceInclTax()
    {
        $MsrpPrice = $this->saleableItem->getMsrp()* ($this->getTaxRate() / 100 + 1);
        $finalPrice = $this->saleableItem->getFinalPrice()* ($this->getTaxRate() / 100 + 1);
        if ($MsrpPrice > $finalPrice) {
            return $MsrpPrice;
        } else {
            return null;
        }
    }

    /**
     * Get price display type
     *
     * @return int
     */
    public function getPriceDisplayType()
    {
        return $this->taxHelper->getPriceDisplayType();
    }

    /**
     * Get tax percent by saleable item
     *
     * @return float|int
     */
    private function getTaxPercentBySaleableItem()
    {
        $taxPercent = $this->saleableItem->getTaxPercent();
        if ($taxPercent === null) {
            $productTaxClassId = $this->saleableItem->getTaxClassId();
            if ($productTaxClassId) {
                $store = $this->storeManager->getStore();
                $groupId = $this->customerSession->getCustomerGroupId();
                $group = $this->groupRepository->getById($groupId);
                $customerTaxClassId = $group->getTaxClassId();

                $request = $this->taxCalculation->getRateRequest(null, null, $customerTaxClassId, $store);
                $request->setData('product_class_id', $productTaxClassId);

                $taxPercent = $this->taxCalculation->getRate($request);
            }
        }
        if ($taxPercent) {
            return $taxPercent;
        }

        return 0;
    }
}
