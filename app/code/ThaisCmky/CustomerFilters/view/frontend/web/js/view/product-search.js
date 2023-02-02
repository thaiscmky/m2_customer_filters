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
 **/
define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/storage',
    'mage/url',
    'mage/translate',
    'mage/validation'
], function ($, Component, ko, storage, urlBuilder, $t) {
    console.log('this is the product search call');
    return Component.extend({
        currency: window.storeCurrencySymbol,
        currencyCode: window.storeCurrency,
        currentPage: ko.observable(0),
        totalItems: ko.observable(0),
        productList: ko.observableArray([]),
        minPrice: ko.observable(),
        maxPrice: ko.observable(),
        initialize: function () {
            this._super();
            return this;
        },
        getProducts: function () {
            var self = this;
            if(!this.validate()) return;
            var query = new URLSearchParams({
                minPrice: self.minPrice(),
                maxPrice: self.maxPrice(),
                offset: self.currentPage(),
                sortOrder: $('#sort_by_price').val()
            });
            var serviceUrl = urlBuilder.build('customerfilters/productlist/result/?' + query , '');
            $('#search_status').text($t('Loading search results, please wait') + '...');
            return storage.post(
                serviceUrl,
                {}
            ).done(
                function (response) {
                    $('#search_status').text('');
                    self.productList(response.products);
                    self.currentPage(response.offset);
                    self.totalItems(response.items);
                }
            ).fail(
                function (response) {
                    console.log(response);
                }
            );
        },
        validate: function () {
            var form = '#form-price-search';
            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
