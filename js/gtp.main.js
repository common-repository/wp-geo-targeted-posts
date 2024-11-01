jQuery(function() {
    jQuery(".gtp-select").select2();
    jQuery("#gtp-countries").on('change',function(e) {
        if (e.added !== undefined) {
            jQuery("#gtp-country-all").remove();
            jQuery("#gtp-country-display").append('<span id="gtp-country-' + e.added.id +'">' + e.added.text +'</span>');
        }
        if (e.removed !== undefined) {
            jQuery("#gtp-country-" + e.removed.id).remove();
            if (jQuery("#gtp-country-display > span").length === 0) {
                jQuery("#gtp-country-display").append('<span id="gtp-country-all">All</span>');
            }
        }
    });
    jQuery("#save-countries-list").click(function(e) {
        jQuery("#gtp-country-select").slideToggle();
        jQuery("#gtp-edit").show();
    });
    jQuery("#gtp-edit").click(function(e) {
        jQuery(this).hide();
        jQuery("#gtp-country-select").slideToggle();
    });
});