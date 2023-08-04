jQuery(document).ready(function ($) {
    // lazy load
    if ('<?php echo fifu_is_on("fifu_lazy"); ?>') {
        jQuery.extend(jQuery.lazyLoadXT, {
            srcAttr: 'data-src',
            visibleOnly: false,
            updateEvent: 'load orientationchange resize scroll touchmove focus hover'
        });
    }

    // for all images on home/shop
    if ('<?php echo fifu_should_crop(); ?>') {
        setTimeout(function () {
            cropImage();
        }, 0);
    }

    if ('<?php echo fifu_is_on("fifu_slider"); ?>')
        cropImage('.fifu-slider');

    // for all images at single product page
    setTimeout(function () {
        resizeImg($);
        jQuery('a.woocommerce-product-gallery__trigger').css('visibility', 'visible');
    }, 2500);

    // hover effects
    if ('<?php echo fifu_hover_selected(); ?>')
        addHoverEffect($);

    // woocommerce lightbox/zoom
    disableClick($);
    disableLink($);

    // lightbox
    jQuery('div.woocommerce-product-gallery').on('mouseover', function () {
        replace_lightbox_image_size_speedup();
        replace_lightbox_image_size_flickr();
    });
});

jQuery(document).ajaxComplete(function ($) {
    addHoverEffect($);
});

jQuery(window).on('ajaxComplete', function () {
    if ('<?php echo fifu_is_on("fifu_lazy"); ?>') {
        setTimeout(function () {
            jQuery(window).lazyLoadXT();
        }, 300);
    }
});

jQuery(window).on('load', function () {
    jQuery('.flex-viewport').css('height', '100%');
});

function addHoverEffect($) {
    jQuery('.post-thumbnail, .featured-image > a > img').each(function (index) {
        if ("<?php echo is_home() ?>")
            jQuery(this).replaceWith('<div id="hover" class="<?php echo get_option("fifu_hover");?>"><div><figure>'.concat(jQuery(this).parent().html()).concat('</figure></div></div>'));
    });

    jQuery('img.attachment-woocommerce_thumbnail').each(function (index) {
        if (jQuery(this).parent().parent().html().search('woocommerce-LoopProduct-link') < 0)
            return;
        if ("<?php echo class_exists('WooCommerce') && is_shop()?>")
            jQuery(this).replaceWith('<div id="hover" class="<?php echo get_option("fifu_hover");?>"><div><figure>'.concat(jQuery(this).context.outerHTML).concat('</figure></div></div>'));
    });
}

function resizeImg($) {
    var imgSelector = ".post img, .page img, .widget-content img, .product img, .wp-admin img, .tax-product_cat img, .fifu img";
    var resizeImage = function (sSel) {
        jQuery(sSel).each(function () {
            //original size
            var width = $(this)['0'].naturalWidth;
            var height = $(this)['0'].naturalHeight;

            //100%
            var src = jQuery(this)['0'].src;
            if (src.includes('staticflickr.com') || src.includes('storage.googleapis.com/fifu')) {
                var ratio = width / height;
                jQuery(this).attr('data-large_image_width', jQuery(window).width() * ratio);
                jQuery(this).attr('data-large_image_height', jQuery(window).width());
            } else {
                jQuery(this).attr('data-large_image_width', width);
                jQuery(this).attr('data-large_image_height', height);
            }
        });
    };
    resizeImage(imgSelector);
}

function cropImage(selector) {
    if (!selector)
        selector = "a.woocommerce-LoopProduct-link.woocommerce-loop-product__link, div[id^='post'] <?php echo fifu_crop_selectors(); ?>, .fifu-slider";

    ratio = "<?php echo get_option('fifu_crop_ratio'); ?>";
    ratio_w = ratio.split(':')[0];
    ratio_h = ratio.split(':')[1];
    // div.g1-frame-inner is for bimber theme
    jQuery(selector).find('img, div.g1-frame-inner').each(function (index) {
        var width = jQuery(this).parent().css('width').replace('px', '');
        width = width != 0 ? width : jQuery(this).parent().parent().css('width').replace('px', '');
        width = width != 0 ? width : jQuery(this).parent().parent().parent().css('width').replace('px', '');
        jQuery(this).attr('style', 'height: ' + (width * ratio_h / ratio_w) + 'px !important');
        jQuery(this).css('width', '100%');
        jQuery(this).css('object-fit', 'cover');
    });
}

function disableClick($) {
    if ('<?php echo !fifu_woo_lbox(); ?>') {
        jQuery('.woocommerce-product-gallery__image').each(function (index) {
            jQuery(this).children().click(function () {
                return false;
            });
            jQuery(this).children().children().css("cursor", "default");
        });
    }
}

function disableLink($) {
    if ('<?php echo fifu_woo_zoom() == "none"; ?>') {
        jQuery('.woocommerce-product-gallery__image').each(function (index) {
            jQuery(this).children().attr("href", "");
        });
    }
}

jQuery(document).ajaxSuccess(function () {
    if ('<?php echo fifu_should_crop(); ?>')
        cropImage();
});

// variable product
jQuery(document).ready(function ($) {
    jQuery('.variations select').click(function () {
        jQuery('a.woocommerce-product-gallery__trigger').css('visibility', 'hidden');
        setTimeout(function () {
            resizeImg($);
            jQuery('a.woocommerce-product-gallery__trigger').css('visibility', 'visible');
        }, 500);
    });

    if ("<?php echo fifu_is_off('fifu_variation_gallery') ?>")
        return;

    jQuery('.variations select').change(function () {
        attribute = jQuery(this).attr('name');
        value = jQuery(this).children("option:selected").val();
        newUrl = '?';
        var i = 0;
        jQuery('.variations td.value select').each(function (index) {
            if (jQuery(this)[0].value)
                newUrl += jQuery(this)[0].name + "=" + jQuery(this)[0].value + "&";
            i++;
        });

        var count = (newUrl.match(/attribute_/g) || []).length;
        if (count == i) {
            jQuery('div.woocommerce-product-gallery').css('opacity', 0).css('transition', 'width 0s');
            //jQuery.ajax({url: newUrl, async: true, success: function (result) {jQuery('body').html(result);}});
            window.location.replace(newUrl);
        } else {
            jQuery('ol.flex-control-nav').css('opacity', 0).css('transition', 'width 0s');
        }
    });

    jQuery('.reset_variations').click(function () {
        arr = window.location.href.split("?");
        if (arr.length > 1)
            window.location.replace(arr[0] + "#");
    });
});

//function fifu_get_image_html_api($post_id) {
//    var html = null;
//    var href = window.location.href;
//    var index = href.indexOf('/wp-admin');
//    var homeUrl = href.substring(0, index);
//    jQuery.ajax({
//        url: homeUrl + "/wp-json/fifu-premium/v1/url/" + $post_id,
//        async: false,
//        success: function (data) {
//            html = data;
//        },
//        error: function (jqXHR, textStatus, errorThrown) {
//            console.log(jqXHR);
//            console.log(textStatus);
//            console.log(errorThrown);
//        }
//    });
//    return html;
//}

function lightbox_size_auto(width, height, flickr) {
    longest = width;

    if (width == height) {
        if (longest <= 75)
            return flickr ? '_s' : '75';
        if (longest <= 150)
            return flickr ? '_q' : '150';
    }
    if (longest <= 100)
        return flickr ? '_t' : '100';
    if (longest <= 240)
        return flickr ? '_m' : '240';

    longest *= 0.9;

    if (longest <= 320)
        return flickr ? '_n' : '320';
    if (longest <= 500)
        return flickr ? '' : '500';
    if (longest <= 640)
        return flickr ? '_z' : '640';
    if (longest <= 800)
        return flickr ? '_c' : '800';
    return flickr ? '_b' : '1024';
}

function replace_lightbox_image_size_speedup() {
    selector = 'img[data-large_image*="storage.googleapis.com/fifu"]';
    jQuery(selector).each(function (index) {
        large_img = jQuery(this);
        url = large_img.attr('data-large_image');
        if (!url)
            return;
        width = lightbox_size_auto(window.innerWidth, window.innerHeight, false);
        url = url.replace(/img.*/, 'img-' + width + '.webp');
        jQuery(this).attr('data-large_image', url);
    });
}

function replace_lightbox_image_size_flickr() {
    selector = 'img[data-large_image*="staticflickr.com"]';
    jQuery(selector).each(function (index) {
        large_img = jQuery(this);
        url = large_img.attr('data-large_image');
        if (!url)
            return;
        width = lightbox_size_auto(window.innerWidth, window.innerHeight, true);
        url = url.replace(/(_.)*[.]jpg/, width + '.jpg');
        jQuery(this).attr('data-large_image', url);
    });
}
