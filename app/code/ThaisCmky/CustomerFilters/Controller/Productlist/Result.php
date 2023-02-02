<?php
/**
 * @author      Thais Cailet <thaiscmky@users.noreply.github.com>
 * @package     ThaisCmky_CustomerFilters
 * @copyright   Copyright (c) 2023 Thais Cailet (https://thaiscmky.github.io/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
namespace ThaisCmky\CustomerFilters\Controller\Productlist;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Store\Model\StoreManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;

class Result implements HttpGetActionInterface, HttpPostActionInterface
{
    protected $productRepository;
    protected $imageHelper;
    protected $storeManager;
    protected $stockInfo;
    protected $searchCriteriaBuilder;
    protected $searchFilter;
    protected $filterGroup;
    protected $request;
    protected $response;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManager $storeManager
     * @param Image $imageHelper
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $searchFilter
     * @param FilterGroupBuilder $filterGroup
     * @param GetSalableQuantityDataBySku $stockInfo
     * @param RequestHttp $request
     * @param ResponseHttp $response
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManager $storeManager,
        Image $imageHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $searchFilter,
        FilterGroupBuilder $filterGroup,
        GetSalableQuantityDataBySku $stockInfo,
        RequestHttp $request,
        ResponseHttp $response
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchFilter = $searchFilter;
        $this->filterGroup = $filterGroup;
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->storeManager = $storeManager;
        $this->stockInfo = $stockInfo;
        $this->request = $request;
        $this->response = $response;
    }

    public function execute()
    {
        $offset = $this->request->get('offset');
        $minPrice = $this->request->get('minPrice');
        $maxPrice = $this->request->get('maxPrice');
        if(empty($error = $this->checkInvalid($minPrice, $maxPrice))){
            return $this->response->setStatusCode(400)->representJson(
                json_encode([
                    'error' => $error.join(', ')
                ])
            );
        }

        $result = $this->productRepository->getList($this->setSearchCriteria(
            [$minPrice, $maxPrice]
            , 10
            , $offset
            , $this->request->get('offset')
        ));
        $products =  $result->getItems();

        $productList = [
            'offset' => $result->getSearchCriteria()->getCurrentPage() ?? 0,
            'items' => $result->getTotalCount(),
            'limit' => $result->getSearchCriteria()->getPageSize(),
            'products' => []
        ];

        foreach ($products as $product) {
            $productList['products'][] = [
                'entity_id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'qty' => $this->getProductQuantity($product),
                'price' => number_format($product->getPrice(), 2, '.', ''),
                'src' => $this->imageHelper->init($product, 'product_small_image')->getUrl(),
                'href' => $product->getProductUrl()
            ];
        }
        return $this->response->representJson(json_encode($productList));
    }

    protected function getProductQuantity($product)
    {
        return array_reduce($this->stockInfo->execute($product->getSku()), fn($qty, $stock) => $qty + $stock['qty'], 0);
    }

    protected function setPriceRange($min, $max)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProductInterface::PRICE, $max, 'lteq')
            ->addFilter(ProductInterface::PRICE, $min, 'gteq')
            ->addFilter(ProductInterface::TYPE_ID, ['configurable', 'grouped'], 'nin');
    }

    protected function setSearchCriteria($range, $limit, $offset)
    {
        $this->setPriceRange($range[0], $range[1]);
        return $this->searchCriteriaBuilder
            ->setPageSize($limit)
            ->setCurrentPage(intval($offset) == 0 ? 0 : $offset + 1)
            ->create();
    }

    public function checkInvalid($min, $max) {
        $err = [];
        if(empty($min))
            $err[] = __('A minimum price is required');
        if(empty($max))
            $err[] = __('A maximum price is required');
        if(!is_numeric($max) || !is_numeric($min))
            $err[] = __('Only numbers are accepted');
        if($min < 0 || $max < 0)
            $err[] = __('Only positive numbers are accepted');
        if( $min > $max )
            $err[] = __('Minimum price cannot exceed maximum price');
        if( $max > ($min > 0 ? $min : 1) * 5)
            $err[] = __('Maximum price cannot exceed five times the minimum price');
        return $err;
    }

    protected function setSortOrder()
    {
        //TODO order by price asc or desc
    }

}
