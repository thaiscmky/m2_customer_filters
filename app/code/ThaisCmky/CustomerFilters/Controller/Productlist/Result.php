<?php
namespace ThaisCmky\CustomerFilters\Controller\Productlist;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Type;
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
        $result = $this->productRepository->getList($this->setSearchCriteria(
            [$this->request->get('minPrice'), $this->request->get('maxPrice')]
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
            array_push($productList['products'], [
                'entity_id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'qty' => $this->getProductQuantity($product),
                'price' => $product->getPrice(),
                'src' => $this->imageHelper->init($product, 'product_small_image')->getUrl(),
                'href' => $product->getProductUrl()
            ]);
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

    protected function setSortOrder()
    {
        //TODO order by price asc or desc
    }

}
