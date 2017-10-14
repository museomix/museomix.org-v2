(function($) {

  /* -------------------------
    Closing admin notice
  ------------------------- */

    $(document).on('click', '.notice[data-notice=acf-better-search] .notice-dismiss', function() {

      $.ajax(ajaxurl, {
        type: 'POST',
        data: {
          action: 'acf_better_search_notice'
        }
      });

    });

})(jQuery);