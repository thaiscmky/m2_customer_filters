<?php
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
    protected $stockQty;
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
     * @param GetSalableQuantityDataBySku $stockQty
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
        GetSalableQuantityDataBySku $stockQty,
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
        $this->stockQty = $stockQty;
        $this->request = $request;
        $this->response = $response;
    }

    public function execute()
    {
        $minPrice = $this->request->get('minPrice');
        $maxPrice = $this->request->get('maxPrice');
        $offset = $this->request->get('offset');

        $minfilter = $this->searchFilter
            ->setField(ProductInterface::PRICE)
            ->setConditionType('gte')
            ->setValue($minPrice)
            ->create();

        $maxfilter = $this->searchFilter
            ->setField(ProductInterface::PRICE)
            ->setConditionType('lte')
            ->setValue($maxPrice)
            ->create();

        $filter_group = $this->filterGroup
            ->addFilter($minfilter)
            ->addFilter($maxfilter)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->setFilterGroups([$filter_group])
            ->setPageSize(10)
            ->setCurrentPage(intval($offset) == 0 ? 0 : $offset + 1)
            ->create();

        $result = $this->productRepository->getList($searchCriteria);
        $products =  $result->getItems();

        $productList = [
            'offset' => $result->getSearchCriteria()->getCurrentPage() ?? 0,
            'items' => $result->getTotalCount(),
            'products' => []
        ];

        foreach ($products as $product) {
            $stock_info = $this->stockQty->execute($product->getSku());
            array_push($productList['products'], [
                'entity_id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'qty' => array_reduce($stock_info, fn($qty, $stock) => $qty + $stock['qty'], 0),
                'price' => $product->getPrice(),
                'src' => $this->imageHelper->init($product, 'product_listing_thumbnail')->getUrl(),
                'href' => $product->getProductUrl()
            ]);
        }
        return $this->response->representJson(json_encode($productList));
    }


}
