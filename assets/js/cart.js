import $ from 'jquery';

$(document).ready(function() {

    let timeout;

    $(document).on('click', '.btn-to-cart', function () {
        window.location = '/cart'
    });


    $(document).on('click', '._js-preview-add-to-cart', function(e) {
        e.preventDefault();

        let button = $(this);
        let productId = button.data('product-id');

        sendToCart('/cart/add', 'POST', {
            productId: productId,
            quantity: 1,
        });

    });

    $(document).on('click', '._js-b-plus', function(e) {
        e.preventDefault();

        let input = $(this).closest('._js-pcscontrolls').find('input');
        let availability = input.data('max');
        let value = parseInt(input.val());

        if (value >= availability) {
            showValidationPopup('Выбрано максимальное количество', 'info');
            setTimeout(function() { $('._js-validation-alert').hide(); }, 3000);
            return;
        }

        value++;
        input.val(value);

        if (window.location.pathname == '/cart') {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                let productId = input.data('product-id');
                let count = value;
                let method = 'PUT';
                let url = "/cart/update";
                let data = {
                    productId: productId,
                    quantity: count
                };

                sendToCart(url, method, data);
            }.bind(this), 1000);
        }

    });

    $(document).on('click', '._js-b-minus', function(e) {
        e.preventDefault();

        let input = $(this).closest('._js-pcscontrolls').find('input');
        let value = parseInt(input.val());

        if (value <= 1) {
            return;
        }

        value--;
        input.val(value);

        if (window.location.pathname == '/cart') {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                let productId = input.data('product-id');
                let count = value;
                let method = 'PUT';
                let url = "/cart/update";
                let data = {
                    productId: productId,
                    quantity: count
                };

                sendToCart(url, method, data);
            }.bind(this), 1000);
        }

    });


    $(document).on('click', '._js-b-remove', function(e) {
        e.preventDefault();

        let input = $(this).closest('._js-cart-item').find('input');
        let productId = input.data('product-id');

        clearTimeout(timeout);

        sendToCart('/cart/remove', 'DELETE', {
            productId: productId,
        });

    });


    function showValidationPopup(message, type) {
        let colors = {info: 'color001', error: 'color002', success: 'color003'};

        $('._js-validation-alert').remove();

        $('body').append(
            '<section class="s-validation _js-validation-alert">' +
                '<div class="container">' +
                    '<div class="w-validation-alert ' + (colors[type] || colors.info) + '">' +
                        '<div class="validation-content">' + $('<div>').text(message).html() + '</div>' +
                    '</div>' +
                '</div>' +
            '</section>'
        );
    }


    function refreshCartPage(response) {
        let totals = response.totals[0];

        if (response.removedProductId) {
            $('._js-cart-item').has('input[data-product-id="' + response.removedProductId + '"]').remove();
        }

        if (response.item) {
            let input = $('input[data-product-id="' + response.item.productId + '"]');
            let controls = input.closest('._js-pcscontrolls');
            let row = input.closest('._js-cart-item');

            input.val(response.item.quantity);
            input.attr('data-count', response.item.quantity);
            input.attr('data-max', response.item.availableUnits);
            input.data('max', response.item.availableUnits);

            controls.find('._js-b-minus').toggleClass('disabled', response.item.quantity <= 1);
            controls.find('._js-b-plus').toggleClass('disabled', !response.item.canIncrease);

            row.find('._js-cart-item-total').text(response.item.total);
            row.find('._js-cart-item-old-total').text(response.item.oldTotal);
        }

        if (totals) {
            $('._js-cart-total-products').text(totals.products);
            $('._js-cart-total-discount').text(totals.discount);
            $('._js-cart-total-sum').text(totals.total);
        }

        if (response.isEmpty) {
            $('._js-cart-page').remove();
            $('._js-cart-empty').removeClass('hidden');
        }
    }


    function sendToCart(url, method, data) {
        $.ajax({
            url: url,
            method: method,
            contentType: 'application/json',
            headers: {
                'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') || '',
            },
            data: JSON.stringify(data),
            success: function (response) {
 
                // if (!response.totalSum) {
                //     window.location = '/'
                // }

                // if (window.location.pathname == '/cart') {
                //     $('._js-cart-form').html(response.cartBlockHtml);
                // }

                $('.cart .count').text(response.cartCount || '');

                if (window.location.pathname == '/cart') {
                    refreshCartPage(response);
                    return;
                }

                if (response.cartUrl) {
                    $('[data-cart-link]').addClass('_active').attr('href', response.cartUrl);

                    $('._js-preview-add-to-cart[data-product-id="' + data.productId + '"]')
                        .removeClass('_js-preview-add-to-cart')
                        .addClass('_active btn-to-cart')
                        .attr('href', response.cartUrl)
                        .attr('title', 'In cart');
                }

            },
            error: function(xhr) {
                let message = xhr.responseJSON && xhr.responseJSON.message;

                showValidationPopup(message || 'Не удалось обновить корзину', 'error');
                setTimeout(function() { $('._js-validation-alert').hide(); }, 3000);

                if (window.location.pathname == '/cart') {
                    let input = $('input[data-product-id="' + data.productId + '"]');

                    input.val(input.attr('data-count'));
                }
            }
        });
    }
});


