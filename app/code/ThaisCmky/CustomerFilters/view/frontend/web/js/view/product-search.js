define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/storage',
    'mage/url'
], function ($, Component, ko, storage, urlBuilder) {
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
            const self = this;
            const query = new URLSearchParams({
                minPrice: self.minPrice(),
                maxPrice: self.maxPrice(),
                offset: self.currentPage()
            });
            const serviceUrl = urlBuilder.build('customerfilters/productlist/result/?' + query , '');
            return storage.post(
                serviceUrl,
                {}
            ).done(
                function (response) {
                    self.productList(response.products);
                    self.currentPage(response.offset);
                    self.totalItems(response.items);
                }
            ).fail(
                function (response) {
                    console.log(response);
                }
            );
        }
    });
});
