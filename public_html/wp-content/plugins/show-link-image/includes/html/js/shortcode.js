jQuery(document).ready(function ($) {
    replaceShortcodeThumb($);
    jQuery(".pswp__counter").bind("DOMSubtreeModified", function ($) {
        replaceShortcodeImageDlg($);
    });
});

jQuery(document).click(function ($) {
    // zoom
    jQuery("a.woocommerce-product-gallery__trigger").on("click", function ($) {
        setTimeout(function () {
            replaceShortcodeImageDlg($);
        }, 100);
    });
    // arrows
    jQuery("button.pswp__button").on("click", function ($) {
        replaceShortcodeImageDlg($);
    });
});

jQuery(document).keydown(function (e) {
    setTimeout(function () {
        switch (e.which) {
            case 37:// left
                replaceShortcodeImageDlg($);
                break;
            case 39:// right
                replaceShortcodeImageDlg($);
                break;
        }
    }, 100);
});

function replaceShortcodeThumb() {
    post_id = '<?php echo get_the_ID() ?>'
    url = '<?php echo fifu_main_image_url(get_the_ID()) ?>';
    jQuery('[src^="' + url + '"]').each(function (index) {
        width = jQuery(this)['0'].width;
        if (width > '<?php echo get_option("fifu_shortcode_min_width") ?>') {
            jQuery(this).attr('id', 'fifu-' + post_id);
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: ajax_object.ajaxurl,
                cache: false,
                data: {action: 'fifu_callback_shortcode', id: post_id},
                success: function (response) {
                    findAndReplace(post_id, response.longcode);
                }
            }).fail(function (data) {
                console.log(data);
            });
        }
    });
}

function findAndReplace(id, longcode) {
    jQuery('#fifu-' + id).each(function (index) {
        jQuery(this).replaceWith(longcode);
        //for all shortcodes on home/shop
        if ('<?php echo fifu_should_crop(); ?>')
            cropShortcode();
    });
}

function cropShortcode() {
    var root = jQuery("a.woocommerce-LoopProduct-link.woocommerce-loop-product__link");
    root.find('iframe').each(function (index) {
        var width = root.parent().css('width').replace('px', '');
        jQuery(this).css('height', width * 3 / 4);
        jQuery(this).css('width', '100%');
        jQuery(this).css('object-fit', 'cover');
    });
}

function replaceShortcodeImageDlg() {
    post_id = '<?php echo get_the_ID() ?>'
    url = '<?php echo fifu_main_image_url(get_the_ID()) ?>';
    jQuery('div.pswp__container > div.pswp__item > div.pswp__zoom-wrap').each(function (index) {
        if (jQuery(this).find('img')['0'] === undefined) {
            jQuery(this).append('<div id="fifu-' + post_id + '"></div>');
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: ajax_object.ajaxurl,
                cache: false,
                data: {action: 'fifu_callback_shortcode', id: post_id},
                success: function (response) {
                    iframe = jQuery(response.longcode).attr('class', 'pswp__video').removeAttr('scrolling').removeAttr('width').removeAttr('height')[2].outerHTML;
                    iframe = '<div class="wrapper"><div class="video-wrapper">' + iframe + '</div></div>';
                    findAndReplace(post_id, iframe);
                }
            }).fail(function (data) {
                console.log(data);
            });
        }
    });
}

