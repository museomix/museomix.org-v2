
/* Fonction pour disable les facette facetWP lorsqu'elles sont vides */
(function($) {
    $(document).on('facetwp-loaded', function() {
        $.each(FWP.settings.num_choices, function(key, val) {
            var $parent = $('.facetwp-facet-' + key);
            var $select = $($parent).find('select');
            
            if(0 === val) {
                $select.prop('disabled', 'disabled');
                $select.addClass('disabled');
                $select.css('opacity', '0.4');
            } else {
                $select.prop('disabled', false);
                $select.removeClass('disabled');
                $select.css('opacity', '1');
            }
        });
    });
    $('.facet-reset').click(function(e) {
        e.preventDefault();
    })
	
	
})(jQuery);

