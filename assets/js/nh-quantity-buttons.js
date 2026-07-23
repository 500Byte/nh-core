jQuery(document).ready(function($) {
    function updateQtyStates() {
        $('div.quantity').each(function() {
            var $container = $(this);
            var $input = $container.find('.qty');
            var $minus = $container.find('.nh-qty-minus');
            var $plus = $container.find('.nh-qty-plus');
            if (!$input.length) return;

            var currentVal = parseFloat($input.val()) || 0;
            var min = parseFloat($input.attr('min')) || 1;
            var max = parseFloat($input.attr('max'));

            if (currentVal <= min) {
                $minus.addClass('nh-qty-disabled');
            } else {
                $minus.removeClass('nh-qty-disabled');
            }

            if (!isNaN(max) && currentVal >= max) {
                $plus.addClass('nh-qty-disabled');
            } else {
                $plus.removeClass('nh-qty-disabled');
            }
        });
    }

    // Initial check for quantity states
    updateQtyStates();

    // Trigger state check on dynamic WooCommerce updates (e.g. cart updates)
    $(document).on('updated_wc_div updated_cart_totals wc_fragments_refreshed wc_fragments_loaded', function() {
        updateQtyStates();
    });

    $(document).on('click', '.nh-qty-btn', function(e) {
        e.preventDefault();
        var $button = $(this);
        if ($button.hasClass('nh-qty-disabled')) return;
        var $qtyInput = $button.siblings('.qty');
        if (!$qtyInput.length) return;
        
        var currentVal = parseFloat($qtyInput.val()) || 0;
        var max = parseFloat($qtyInput.attr('max'));
        var min = parseFloat($qtyInput.attr('min')) || 1;
        var step = parseFloat($qtyInput.attr('step')) || 1;
        
        if ($button.hasClass('nh-qty-plus')) {
            if (!isNaN(max) && currentVal >= max) {
                $qtyInput.val(max);
            } else {
                $qtyInput.val(currentVal + step);
            }
        } else if ($button.hasClass('nh-qty-minus')) {
            if (currentVal <= min) {
                $qtyInput.val(min);
            } else {
                $qtyInput.val(currentVal - step);
            }
        }
        $qtyInput.trigger('change');
        updateQtyStates();
    });
});
