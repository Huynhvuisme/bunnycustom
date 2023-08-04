function homeUrl() {
    var href = window.location.href;
    var index = href.indexOf('/wp-admin');
    var homeUrl = href.substring(0, index);
    return homeUrl;
}

function removeImage() {
    jQuery("#fifu_input_alt").hide();
    jQuery("#fifu_image").hide();
    jQuery("#fifu_link").hide();
    jQuery("#fifu_thumb_button").hide();

    jQuery("#fifu_input_alt").val("");
    jQuery("#fifu_input_url").val("");

    jQuery("#fifu_button").show();
}

function previewImage() {
    var $url = jQuery("#fifu_input_url").val();
    $url = fifu_convert($url);

    if ($url) {
        jQuery("#fifu_button").hide();

        jQuery("#fifu_image").css('background-image', "url('" + $url + "')");

        jQuery("#fifu_input_alt").show();
        jQuery("#fifu_image").show();
        jQuery("#fifu_link").show();
        jQuery("#fifu_thumb_button").show();
        jQuery("#sliderImageUrlMetaBox").show();
    }
}

function getMeta(url) {
    jQuery("<img/>", {
        load: function () {
            jQuery("#fifu_input_image_width").val(this.width);
            jQuery("#fifu_input_image_height").val(this.height);
        },
        src: url
    });
}

function createThumbnails() {
    jQuery("#fifu_image").css('background-image', "url('" + 'https://c2.staticflickr.com/8/7862/46621758651_4ef7d57f47_o.gif' + "')");
    var url = jQuery("#fifu_input_url").val();
    var post_id = '<?php echo get_the_ID()?>';
    fifu_create_thumbnails_api(url, post_id);
}

function fifu_create_thumbnails_api(url, post_id) {
    if (!url || !post_id)
        return;

    var output = null;

    jQuery.ajax({
        method: "POST",
        url: homeUrl() + "/wp-json/fifu-premium/v2/create_thumbnails/",
        data: {
            "url": url,
            "post_id": post_id,
        },
        async: true,
        success: function (data) {
            output = data;
            jQuery("#fifu_image").css('background-image', "url('" + output + "?" + Math.random() + "')");
            jQuery("#fifu_input_url").val(output);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
    return output;
}
