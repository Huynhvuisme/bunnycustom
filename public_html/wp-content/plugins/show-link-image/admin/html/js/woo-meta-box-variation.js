function wcRemoveImageVariation(i) {
    var inputUrl = "#fifu_input_url_var_" + i;
    var button = "#fifu_button_var_" + i;
    var image = "#fifu_image_var_" + i;
    var link = "#fifu_link_var_" + i;

    jQuery(image).hide();
    jQuery(link).hide();

    jQuery(inputUrl).val("");

    jQuery(inputUrl).show();
    jQuery(button).show();
}

function wcPreviewImageVariation(i) {
    var inputUrl = "#fifu_input_url_var_" + i;
    var button = "#fifu_button_var_" + i;
    var image = "#fifu_image_var_" + i;
    var link = "#fifu_link_var_" + i;

    var $url = jQuery(inputUrl).val();
    $url = fifu_convert($url);

    if ($url) {
        jQuery(inputUrl).hide();
        jQuery(button).hide();

        jQuery(image).css('background-image', "url('" + $url + "')");

        jQuery(image).show();
        jQuery(link).show();
    }
}
