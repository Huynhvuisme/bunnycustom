function wcRemoveVideo(i) {
    var inputUrl = "#fifu_video_input_url_" + i;
    var button = "#fifu_video_button_" + i;
    var video = "#fifu_video_" + i;
    var link = "#fifu_video_link_" + i;

    jQuery(video).hide();
    jQuery(link).hide();

    jQuery(inputUrl).val("");

    jQuery(inputUrl).show();
    jQuery(button).show();
}

function wcPreviewVideo(i) {
    var inputUrl = "#fifu_video_input_url_" + i;
    var button = "#fifu_video_button_" + i;
    var video = "#fifu_video_" + i;
    var link = "#fifu_video_link_" + i;
    var iframe = "#fifu_video_iframe_" + i;

    var $url = jQuery(inputUrl).val();

    if ($url) {
        jQuery(inputUrl).hide();
        jQuery(button).hide();

        jQuery(iframe).attr("src", srcVideo($url));

        jQuery(video).show();
        jQuery(link).show();
    }
}
