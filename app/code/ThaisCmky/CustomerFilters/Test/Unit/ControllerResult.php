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
namespace ThaisCmky\CustomerFilters\Test\Unit;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\TestCase;
use ThaisCmky\CustomerFilters\Controller\Productlist\Result as Result;

class ControllerResult extends TestCase {

    protected $controller;

    /**
     * Mocks
     */
    protected $productRepositoryMock;
    protected $imageHelperMock;
    protected $storeManagerMock;
    protected $stockInfoMock;
    protected $searchCriteriaBuilderMock;
    protected $searchFilterMock;
    protected $filterGroupMock;
    protected $requestMock;
    protected $responseMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    public function setUp(): void
    {
        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class);
        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class);
        $this->imageHelperMock = $this->getMockBuilder(Image::class);
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class);
        $this->searchFilterMock = $this->getMockBuilder(FilterBuilder::class);
        $this->filterGroupMock = $this->getMockBuilder(FilterGroupBuilder::class);
        $this->stockInfoMock = $this->getMockBuilder(GetSalableQuantityDataBySku::class);
        $this->requestMock = $this->getMockBuilder(RequestHttp::class);
        $this->responseMock = $this->createMock(ResponseHttp::class);

        $this->controller = new ControllerResult(
            $this->productRepositoryMock,
            $this->storeManagerMock,
            $this->imageHelperMock,
            $this->searchCriteriaBuilderMock,
            $this->searchFilterMock,
            $this->filterGroupMock,
            $this->stockInfoMock,
            $this->requestMock,
            $this->responseMock
        );
    }

    public function testCheckInvalidWithNullMin()
    {
        $result= $this->controller->checkInvalid(null,null);
        $message = __('A minimum price is required');
        $this->assertContains($message, $result, "checkInvalid did not return the expected min error");
    }

    public function testCheckInvalidWithNullMax()
    {
        $result= $this->controller->checkInvalid(null,null);
        $message = __('A maximum price is required');
        $this->assertContains($message, $result, "checkInvalid did not return the expected max error");
    }

    public function testCheckInvalidWithMinAsString()
    {
        $result= $this->controller->checkInvalid('test',30);
        $message = __('Only numbers are accepted');
        $this->assertContains($message, $result, "checkInvalid did not return the expected NaN error");
    }

    public function testCheckInvalidWithMaxAsString()
    {
        $result= $this->controller->checkInvalid(30,'test');
        $message = __('Only numbers are accepted');
        $this->assertContains($message, $result, "checkInvalid did not return the expected NaN error");
    }

    public function testCheckInvalidWithMinAsNegativeNumber()
    {
        $result= $this->controller->checkInvalid(-30,40);
        $message = __('Only numbers are accepted');
        $this->assertContains($message, $result, "checkInvalid did not return the expected NaN error");
    }

    public function testCheckInvalidWithMinGreaterThanMax()
    {
        $result= $this->controller->checkInvalid(40,30);
        $message = __('Minimum price cannot exceed maximum price');
        $this->assertContains($message, $result, "checkInvalid did not return the expected minimum shouldn't be greater than maximum price error");
    }

    public function testCheckInvalidWithMinIsntLessThanFiveTimesMax()
    {
        $result= $this->controller->checkInvalid(5,30);
        $message = __('Maximum price cannot exceed five times the minimum price');
        $this->assertContains($message, $result, "checkInvalid did not return the expected min over 5 times higher than max error");
    }

    public function testCheckInvalidWithValidInput()
    {
        $result = $this->controller->checkInvalid(5,10);
        $this->assertEquals([], $result, "checkInvalid returned errors on valid input");
    }
}