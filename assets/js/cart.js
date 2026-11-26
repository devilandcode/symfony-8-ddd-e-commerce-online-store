import $ from 'jquery';

const CART_ACTIVE_CLASSES = 'active _active';

function setCartCount(count) {
    $('[data-cart-link]').each(function () {
        const $link = $(this);

        $link.find('.count').text(count > 0 ? count : '');

        if (count > 0) {
            $link.addClass(CART_ACTIVE_CLASSES).attr('href', $link.data('cartUrl'));
        }
    });
}

function markProductInCart($button, cartUrl) {
    $button
        .addClass(CART_ACTIVE_CLASSES)
        .attr('href', cartUrl)
        .attr('title', $button.data('titleInCart'));
}

function addToCart($button) {
    return $.ajax({
        url: $button.data('addUrl'),
        method: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        headers: {
            'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') || '',
        },
        data: JSON.stringify({ productId: Number($button.data('productId')), quantity: 1 }),
    }).done(function (payload) {
        if (!payload || !payload.success) {
            return;
        }

        markProductInCart($button, payload.cartUrl);
        setCartCount(payload.cartCount);
    });
}

$(document).on('click', '[data-cart-add]', function (event) {
    const $button = $(this);

    // A product that is already in the cart behaves as a normal link to the cart page.
    if ($button.hasClass('active')) {
        return;
    }

    event.preventDefault();

    if ($button.data('pending')) {
        return;
    }

    $button.data('pending', true);
    addToCart($button)
        .fail(function (xhr) {
            const message = xhr.responseJSON && xhr.responseJSON.message;
            console.error(message || 'Add to cart failed with status ' + xhr.status);
        })
        .always(function () {
            $button.removeData('pending');
        });
});
