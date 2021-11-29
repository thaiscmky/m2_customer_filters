<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ThaisCmky\CustomerFilters\Block;


class Form extends \Magento\CatalogSearch\Block\Advanced\Form
{
    /**
     * Retrieve search form action url
     *
     * @return string
     */
    public function getSearchPostUrl()
    {
        return $this->getUrl('/*/result');
    }
}
