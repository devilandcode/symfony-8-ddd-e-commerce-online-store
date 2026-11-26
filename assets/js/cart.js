$(document).ready(function() {
    let timeout = null;

    $(document).on('click', '.btn-to-cart', function () {
        window.location = '/cart'
    });

    $(document).on('click', '._js-add-to-cart', function(e) {
        e.preventDefault();

        let button = $(this);
        let productId = button.data('product-id');
        let count = button.closest('.w-product-page-to-cart-group').find('._js-product-count').val();

        $.ajax({
            url: '/cart/add',
            method: 'POST',
            data: {
                product_id: productId,
                count: count,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('.cart .count').text(response.cart_count);

                button.addClass('btn-to-cart _active').removeClass('_js-add-to-cart');
                button.text('В КОРЗИНЕ');
            },
            error: function(xhr) {
                console.error('Ошибка при добавлении в корзину');
            }
        });
    });

});