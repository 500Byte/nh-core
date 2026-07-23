jQuery(document).ready(function($) {
    $(document.body).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        var productId = $button.data('product_id');
        var isVariant = $button.hasClass('wvs_ajax_add_to_cart') || 
                        $button.hasClass('product_type_variable') || 
                        !!$button.data('variation_id') || 
                        !!$button.attr('data-variation-id');
        
        window.dataLayer = window.dataLayer || [];
        
        var itemVariant = undefined;
        if (isVariant) {
            var $form = $button.closest('form.variations_form');
            if ($form.length) {
                var selected = [];
                $form.find('select').each(function() {
                    var val = $(this).val();
                    if (val) {
                        var name = $(this).find('option[value="' + val + '"]').text() || val;
                        selected.push(name.trim());
                    }
                });
                if (selected.length > 0) {
                    itemVariant = selected.join(' / ');
                }
            }
            
            if (!itemVariant) {
                var activeSwatches = [];
                $button.closest('.product').find('.variable-item.selected, .color-variable-item.selected, .button-variable-item.selected, .image-variable-item.selected, .radio-variable-item.selected').each(function() {
                    var val = $(this).attr('data-value') || $(this).text();
                    if (val) {
                        activeSwatches.push(val.trim());
                    }
                });
                if (activeSwatches.length > 0) {
                    itemVariant = activeSwatches.join(' / ');
                }
            }
        }
        
        window.dataLayer.push({
            'event': 'agregar_carrito',
            'product_id': productId,
            'item_is_variant': isVariant,
            'item_variant': itemVariant
        });
    });
});
