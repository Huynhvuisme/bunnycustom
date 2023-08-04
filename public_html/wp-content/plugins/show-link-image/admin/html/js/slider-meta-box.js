function sliderRemoveImage(i) {
    var inputUrl = "#fifu_slider_input_url_" + i;
    var button = "#fifu_slider_button_" + i;
    var image = "#fifu_slider_image_" + i;
    var inputAlt = "#fifu_slider_input_alt_" + i;
    var link = "#fifu_slider_link_" + i;

    jQuery(inputAlt).hide();
    jQuery(image).hide();
    jQuery(link).hide();

    jQuery(inputAlt).val("");
    jQuery(inputUrl).val("");

    jQuery(inputUrl).show();
    jQuery(button).show();
}

function sliderPreviewImage(i) {
    var inputUrl = "#fifu_slider_input_url_" + i;
    var button = "#fifu_slider_button_" + i;
    var image = "#fifu_slider_image_" + i;
    var inputAlt = "#fifu_slider_input_alt_" + i;
    var link = "#fifu_slider_link_" + i;

    var $url = jQuery(inputUrl).val();
    $url = fifu_convert($url);

    if ($url) {
        jQuery(inputUrl).hide();
        jQuery(button).hide();

        jQuery(image).css('background-image', "url('" + $url + "')");

        jQuery(inputAlt).show();
        jQuery(image).show();
        jQuery(link).show();
    }
}
