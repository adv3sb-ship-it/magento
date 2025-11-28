/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'underscore',
    'sidebar',
    'mage/translate'
], function (Component, customerData, $, ko, _, sid, trans) {
    'use strict';

    var sidebarInitialized = false,
        showPopup = false,
        addToCartCalls = 0,
        miniCart;

    miniCart = $(`[data-block='minicart']`);
    miniCart.on('dropdowndialogopen', function () {
        initSidebar();
    });

    /**
     * @return {Boolean}
     */
    function initSidebar() {
        if (miniCart.data('mageSidebar')) {
            miniCart.sidebar('update');
        }

        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniCart.trigger('contentUpdated');

        if (sidebarInitialized) {
            return false;
        }
        sidebarInitialized = true;
        miniCart.sidebar({
            'targetElement': 'div.block.block-minicart',
            'url': {
                'checkout': window.checkout.checkoutUrl,
                'update': window.checkout.updateItemQtyUrl,
                'remove': window.checkout.removeItemUrl,
                'loginUrl': window.checkout.customerLoginUrl,
                'isRedirectRequired': window.checkout.isRedirectRequired
            },
            'button': {
                'checkout': '#top-cart-btn-checkout',
                'remove': '#mini-cart a.action.delete',
                'close': '#btn-minicart-close'
            },
            'showcart': {
                'parent': 'span.counter',
                'qty': 'span.counter-number',
                'label': 'span.counter-label'
            },
            'minicart': {
                'list': '#mini-cart',
                'content': '#minicart-content-wrapper',
                'qty': 'div.items-total',
                'subtotal': 'div.subtotal span.price',
                'maxItemsVisible': window.checkout.minicartMaxItemsVisible
            },
            'item': {
                'qty': ':input.cart-item-qty',
                'button': ':button.update-cart-item'
            },
            'confirmMessage': trans('Are you sure you would like to remove this item from the shopping cart?')
        });
    }

    return Component.extend({
        shoppingCartUrl: window.checkout.shoppingCartUrl,
        cart: {},

        /**
         * @override
         */
        initialize: function () {
            var self = this,
                cartData = customerData.get('cart');

            this.update(cartData());
            cartData.subscribe(function (updatedCart) {
                addToCartCalls--;
                this.isLoading(addToCartCalls > 0);
                sidebarInitialized = false;
                this.update(updatedCart);
                initSidebar();
                if (showPopup) {
                    $('#cart-btn').trigger('click');
                    showPopup = false;
                }
            }, this);
            $('[data-block="minicart"]').on('needShowPopup', function (event) {
                showPopup = true;
            });
            $('[data-block="minicart"]').on('contentLoading', function (event) {
                addToCartCalls++;
                self.isLoading(true);
            });

            return this._super();
        },
        isLoading: ko.observable(false),
        initSidebar: initSidebar,

        /**
         * @return {Boolean}
         */
        closeSidebar: function () {
            var minicart = $('[data-block="minicart"]');
            minicart.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                minicart.find('[data-role="dropdownDialog"]').dropdownDialog('close');
            });

            return true;
        },

        /**
         * @param {String} productType
         * @return {*|String}
         */
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        },

        /**
         * Update mini shopping cart content.
         *
         * @param {Object} updatedCart
         * @returns void
         */
        update: function (updatedCart) {
            _.each(updatedCart, function (value, key) {
                if (!this.cart.hasOwnProperty(key)) {
                    this.cart[key] = ko.observable();
                }
                this.cart[key](value);
            }, this);
        },

        /**
         * Get cart param by name.
         * @param {String} name
         * @returns {*}
         */
        getCartParam: function (name) {
            if (!_.isUndefined(name)) {
                if (!this.cart.hasOwnProperty(name)) {
                    this.cart[name] = ko.observable();
                }
            }

            return this.cart[name]();
        },

        updateList: function () {
            $(`.js_add_to_cart.active`).removeClass('active').removeClass('loading');
            $('.card-page #about-product-row .js_add_to_cart, .card-page .product-fixed .js_add_to_cart').each(function () {
                let __this = $(this),
                    text = __this.hasClass('pre_order_button') ? trans('Pre order button') : trans('Add to Cart');
                __this.text(text);
            });

            let cartItems = this.getCartParam('items');
            if (cartItems) {
                for (let value of cartItems) {
                    let addButton = $(`.js_add_to_cart[data-id=${value.product_id}]:not(.active)`);
                    addButton.addClass('active');
                    if (addButton.parents('.status_row').length > 0) {
                        addButton.html(`<span class="in__busket">${trans('Already in basket')}</span>`);
                    }
                }
            }
        }
    });
});
