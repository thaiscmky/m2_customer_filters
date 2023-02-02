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
    'mage/translate'
], function($, $t) {
    'use strict';

    return function(targetWidget) {
        $.validator.addMethod(
            'validate-five-times-or-less',
            function(value, el, compare_el) {
                console.log(value, $(compare_el).val(), parseFloat($(compare_el).val().trim()), (minPrice > 0 ? minPrice : 1) * 5 <= parseFloat(value));
                var minPrice = parseFloat($(compare_el).val().trim());
                return parseFloat(value) <= (minPrice > 0 ? minPrice : 1) * 5;
            },
            $.mage.__('Please enter an amount lower than five times the minimum price')
        )
        return targetWidget;
    }
});