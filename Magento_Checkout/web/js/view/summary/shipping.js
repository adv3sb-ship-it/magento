/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/view/summary/discount',
    'mage/translate'
], function ($, _, Component, quote, discountView) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/shipping'
        },
        quoteIsVirtual: quote.isVirtual(),
        totals: quote.getTotals(),

        /**
         * @return {*}
         */
        getShippingMethodTitle: function () {
            var shippingMethod,
                shippingMethodTitle = '';

            if (!this.isCalculated()) {
                return '';
            }
            shippingMethod = quote.shippingMethod();

            if (!_.isArray(shippingMethod) && !_.isObject(shippingMethod)) {
                return '';
            }

            if (typeof shippingMethod['method_title'] !== 'undefined') {
                shippingMethodTitle = ' - ' + shippingMethod['method_title'];
            }

            return shippingMethodTitle ?
                shippingMethod['carrier_title'] + shippingMethodTitle :
                shippingMethod['carrier_title'];
        },

        /**
         * @return {*|Boolean}
         */
        isCalculated: function () {
            return this.totals() && this.isFullMode() && quote.shippingMethod() != null; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*|string}
         */
        getPrintedValue: function () {
            let totals = this.totals(),
                method = quote.shippingMethod(),
                shipping_method = this.selectedMethodCode();
            return totals.shipping_amount === 0 ? (
                (shipping_method === 'novaposhta_to_warehouse' ||
                shipping_method === 'novaposhta_to_door') ?
                $.mage.__(method.extension_attributes.description) : $.mage.__('Shipment free')
            ) : this.getValue();
        },

        selectedMethodCode: function () {
            let method = quote.shippingMethod();
            return method != null ? method.method_code : false;
        },

        /**
         * @return {*}
         */
        getValue: function () {
            var price;

            if (!this.isCalculated()) {
                return this.notCalculatedMessage;
            }
            price = this.totals()['shipping_amount'];

            return this.getFormattedPrice(price);
        },

        /**
         * If is set coupon code, but there wasn't displayed discount view.
         *
         * @return {Boolean}
         */
        haveToShowCoupon: function () {
            var couponCode = this.totals()['coupon_code'];

            if (typeof couponCode === 'undefined') {
                couponCode = false;
            }

            return couponCode && !discountView().isDisplayed();
        },

        /**
         * Returns coupon code description.
         *
         * @return {String}
         */
        getCouponDescription: function () {
            if (!this.haveToShowCoupon()) {
                return '';
            }

            return '(' + this.totals()['coupon_code'] + ')';
        }
    });
});
