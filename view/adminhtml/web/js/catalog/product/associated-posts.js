/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global $, $H */

define([
    'mage/adminhtml/grid'
], function () {
    'use strict';

    return function (config) {
        var selectedPosts = config.selectedPosts,
            productPosts = $H(selectedPosts),
            gridJsObject = window[config.gridJsObjectName],
            tabIndex = 1000;

        $('in_product_posts').value = Object.toJSON(productPosts);

        /**
         * Register Product Blog Post
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerProductPost(grid, element, checked) {
            if (checked) {
                if (element.positionElement) {
                    element.positionElement.disabled = false;
                }
                productPosts.set(element.value, 1);
            } else {
                if (element.positionElement) {
                    element.positionElement.disabled = true;
                }
                productPosts.unset(element.value);
            }
            $('in_product_posts').value = Object.toJSON(productPosts);
            grid.reloadParams = {
                'selected_posts[]': productPosts.keys()
            };
        }

        /**
         * Click on post row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function productPostRowClick(grid, event) {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');
                console.log(checkbox);
                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Change post position
         *
         * @param {String} event
         */
        function positionChange(event) {
            var element = Event.element(event);

            if (element && element.checkboxElement && element.checkboxElement.checked) {
                productPosts.set(element.checkboxElement.value, element.value);
                $('in_product_posts').value = Object.toJSON(productPosts);
            }
        }

        /**
         * Initialize Product Post row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function productPostRowInit(grid, row) {
            var checkbox = $(row).getElementsByClassName('checkbox')[0],
                position = $(row).getElementsByClassName('input-text')[0];

            if (checkbox && position) {
                checkbox.positionElement = position;
                position.checkboxElement = checkbox;
                position.disabled = !checkbox.checked;
                position.tabIndex = tabIndex++;
                Event.observe(position, 'keyup', positionChange);
            }
        }

        gridJsObject.rowClickCallback = productPostRowClick;
        gridJsObject.initRowCallback = productPostRowInit;
        gridJsObject.checkboxCheckCallback = registerProductPost;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                productPostRowInit(gridJsObject, row);
            });
        }
    };
});
