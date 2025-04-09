<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */


namespace Amasty\StoreCredit\Model\Total\CreditMemo;

use Amasty\StoreCredit\Api\Data\SalesFieldInterface;
use Amasty\StoreCredit\Model\ConfigProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class StoreCredit extends AbstractTotal
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(
        ConfigProvider $configProvider,
        PriceCurrencyInterface $priceCurrency,
        RequestInterface $request,
        array $data = []
    ) {
        parent::__construct($data);
        $this->configProvider = $configProvider;
        $this->request = $request;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param Creditmemo $creditmemo
     *
     * @return $this
     * @throws LocalizedException
     */
    public function collect(Creditmemo $creditmemo)
    {
        if (!$creditmemo->getOrder()->getCustomerId()) {
            return $this;
        }
        $baseAmountEntered = $this->request->getParam('store_credit_return_amount');
        if ($baseAmountEntered < 0) {
            throw new LocalizedException(__('Store Credit Refund couldn\'t be less than zero.'));
        }
        $order = $creditmemo->getOrder();
        $availableMoneyForRefund = $order->getGrandTotal() - $order->getTotalRefunded();
        $baseAvailableMoneyForRefund = $order->getBaseGrandTotal() - $order->getBaseTotalRefunded();

        $storeCreditAmount = $baseStoreCreditAmount = 0;

        $returnToStoreCredit = $this->getIsStoreCreditUse($baseAmountEntered);
        $creditmemo->setData(SalesFieldInterface::AMSC_USE, $returnToStoreCredit);

        if ($creditmemo->getData(SalesFieldInterface::AMSC_USE)) {
            if ($baseAmountEntered !== null && $this->request->getParam('amstore_credit_new')) {
                $baseAmountEntered = (float)$baseAmountEntered;
                $storeCreditAmount = $this->priceCurrency->round(
                    $baseAmountEntered * $this->getCurrencyRate($creditmemo)
                );
                $baseStoreCreditAmount = $baseAmountEntered;

                // if order not fully covered by store credit and entered amount not fully covered grand total
                // we cannot return to credit cart (or other) more than customer paid from it
                // $availableMoney + $storeCredit must be equal or greater than $grandTotal
                $isOrderFullyCoveredByStoreCredit = $this->isFloatEmpty($order->getBaseGrandTotal());
                if ($baseStoreCreditAmount < $creditmemo->getBaseGrandTotal()
                    && !$isOrderFullyCoveredByStoreCredit
                    && $baseAvailableMoneyForRefund + $baseStoreCreditAmount < $creditmemo->getBaseGrandTotal()
                ) {
                    $storeCreditAmount = $creditmemo->getGrandTotal() - $availableMoneyForRefund;
                    $baseStoreCreditAmount = $creditmemo->getBaseGrandTotal() - $baseAvailableMoneyForRefund;
                }
            } else {
                $storeCreditAmount = $creditmemo->getGrandTotal();
                $baseStoreCreditAmount = $creditmemo->getBaseGrandTotal();
            }
        } else {
            $leftStoreCreditAmount = $order->getAmstorecreditAmount() - $order->getAmstorecreditRefundedAmount();
            $leftBaseStoreCreditAmount = $order->getAmstorecreditBaseAmount()
                - $order->getAmstorecreditRefundedBaseAmount();
            if ($this->isFloatEmpty($leftBaseStoreCreditAmount)) {
                $leftStoreCreditAmount = $leftBaseStoreCreditAmount = 0;
            }
            if ($creditmemo->getBaseGrandTotal() > $baseAvailableMoneyForRefund) {
                $storeCreditAmount = $creditmemo->getGrandTotal() - $availableMoneyForRefund;
                $baseStoreCreditAmount = $creditmemo->getBaseGrandTotal() - $baseAvailableMoneyForRefund;
            } elseif ($leftBaseStoreCreditAmount < $creditmemo->getBaseGrandTotal()) {
                $storeCreditAmount = $leftStoreCreditAmount;
                $baseStoreCreditAmount = $leftBaseStoreCreditAmount;
            }
        }

        $this->setTotalsToCreditmemo($creditmemo, $baseStoreCreditAmount, $storeCreditAmount);

        return $this;
    }

    /**
     * @param Creditmemo $creditmemo
     * @param mixed $baseAmountEntered
     * @return bool
     */
    private function getIsStoreCreditUse($baseAmountEntered)
    {
        $returnToStoreCredit = $this->request->getParam('return_to_store_credit');
        if ($returnToStoreCredit === null) {
            $returnToStoreCredit = $this->configProvider->isRefundAutomatically();
        }

        if ($baseAmountEntered !== null && $this->isFloatEmpty((float)$baseAmountEntered)) {
            $returnToStoreCredit = false;
        }

        return (bool)$returnToStoreCredit;
    }

    /**
     * @param Creditmemo $creditmemo
     * @return float
     */
    private function getCurrencyRate(Creditmemo $creditmemo)
    {
        $currencyRate = 1.00;
        if (!$this->isFloatEmpty($creditmemo->getBaseGrandTotal())) {
            $currencyRate = $creditmemo->getGrandTotal() / $creditmemo->getBaseGrandTotal();
        }

        return $currencyRate;
    }

    /**
     * @param Creditmemo $creditmemo
     * @param float $baseStoreCreditAmount
     * @param float $storeCreditAmount
     */
    private function setTotalsToCreditmemo(Creditmemo $creditmemo, $baseStoreCreditAmount, $storeCreditAmount)
    {
        $creditmemo->setAmstorecreditAmount($storeCreditAmount);
        $creditmemo->setAmstorecreditBaseAmount($baseStoreCreditAmount);

        $grandTotal = $creditmemo->getGrandTotal();
        $baseGrandTotal = $creditmemo->getBaseGrandTotal();
        $grandTotal -= $storeCreditAmount;
        $baseGrandTotal -= $baseStoreCreditAmount;

        $order = $creditmemo->getOrder();

        $isOrderFullyCoveredByStoreCredit = $this->isFloatEmpty($order->getBaseGrandTotal());

        if ($this->isFloatEmpty($baseGrandTotal) || $isOrderFullyCoveredByStoreCredit) {
            $grandTotal = $baseGrandTotal = 0;
            $creditmemo->setAllowZeroGrandTotal(true);
        }

        $creditmemo->setGrandTotal($grandTotal);
        $creditmemo->setBaseGrandTotal($baseGrandTotal);
    }

    /**
     * @param float $value
     * @return bool
     */
    private function isFloatEmpty($value)
    {
        return $value < 0.0001;
    }
}
