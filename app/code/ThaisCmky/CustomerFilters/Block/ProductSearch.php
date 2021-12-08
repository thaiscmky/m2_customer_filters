<?php
/**
 * @author      Thais Cailet <thaiscmky@users.noreply.github.com>
 * @package     ThaisCmky_CustomerFilters
 * @copyright   Copyright (c) 2021 Thais Cailet (https://thaiscmky.github.io/)
 */

namespace ThaisCmky\CustomerFilters\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ProductSearch extends Template
{

    /**
     * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface[]
     */
    protected $layoutProcessors;
    protected $currency;
    protected $storeManager;

    /**
     * Form constructor.
     * @param Template\Context $context
     * @param PriceCurrencyInterface $currency
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PriceCurrencyInterface $currency,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct(
             $context,
             $data
        );
        $this->currency = $currency;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->layoutProcessors = $layoutProcessors;
    }

    public function getCurrencySymbol(){
        return $this->currency->getCurrencySymbol();
    }

    public function getCurrency(){
        return $this->currency->getCurrency()->getCode();
    }

    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return \Zend_Json::encode($this->jsLayout);
    }
}
