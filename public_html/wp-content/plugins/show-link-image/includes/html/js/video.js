jQuery(document).ready(function ($) {
    replaceVideoThumb($);
    jQuery(".pswp__counter").bind("DOMSubtreeModified", function ($) {
        replaceImageDlg($);
    });
});

jQuery(document).click(function ($) {
    // zoom
    jQuery("a.woocommerce-product-gallery__trigger").on("click", function ($) {
        setTimeout(function () {
            replaceImageDlg($);
        }, 100);
    });
    // arrows
    jQuery("button.pswp__button").on("click", function ($) {
        replaceImageDlg($);
    });
});

jQuery(document).on("mouseover", function ($) {
    jQuery("#site-header-cart").on("mouseenter", function ($) {
        jQuery(".fifu-video").css("display", "inline");
        jQuery(".fifu-video").css("opacity", "100");
    });
})

jQuery(document).keydown(function (e) {
    setTimeout(function () {
        switch (e.which) {
            case 37:// left
                replaceImageDlg($);
                break;
            case 39:// right
                replaceImageDlg($);
                break;
        }
    }, 100);
});

function replaceVideoThumb($) {
    var height;
    var width;

    $("img").each(function (index) {
        src = $(this).attr('src');
        if (!is_video_img(src))
            return;

        // the second condition if for related products
        if ('<?php echo fifu_video_thumb_enabled() ?>' || $(this).parent().parent().parent().attr('class') == 'products') {
            $(this).css('opacity', 100);
            return;
        }

        // minimum video width
        if ('<?php echo !fifu_is_home_or_shop()?>' && jQuery(this).attr('width') < Number('<?php echo get_option("fifu_video_min_width") ?>')) {
            $(this).css('opacity', 100);
            return;
        }

        if (!height && !width)
            height = width = '100%';

        offsetParent = jQuery(this).parent()[0].offsetParent;
        if (offsetParent) {
            // quote page
            if (offsetParent.localName == 'td') {
                $(this).css('display', 'block')
                $(this).css('width', '100%');
                return;
            }
            width = offsetParent.clientWidth;
            ratio = '<?php echo fifu_video_ratio() ?>';
            if (width != 0 && ratio != 0) {
                height = width * ratio + 'px';
                width += 'px';
            }
        }

        src = jQuery(this).attr('src');
        if (is_video_img(src)) {
            url = video_url(src);
            clazz = jQuery(this).parent().attr('class');
            if (clazz == 'site-main') {
                max_width = '<?php echo fifu_video_size_ctgr("width") ?>';
                max_height = '<?php echo fifu_video_size_ctgr("height") ?>';
            } else {
                max_width = '<?php echo fifu_video_size("width") ?>';
                max_height = '<?php echo fifu_video_size("height") ?>';
            }
            if (clazz == 'woocommerce-LoopProduct-link woocommerce-loop-product__link') {
                margin = '<?php echo fifu_video_margin_bottom_prod() ?>';
            } else {
                margin = '<?php echo fifu_video_margin_bottom() ?>';
            }
            vertical_margin = '<?php echo fifu_video_vertical_margin() ?>';
            img_width = $($(this)[0]).attr('width') + 'px';
            img_height = $($(this)[0]).attr('height') + 'px';

            if (max_height)
                height = img_height = max_height;
            else
                max_width = max_height = '100%';

            $video = '<div style="margin-bottom:' + margin + '"><iframe class="fifu_iframe" ' + '<?php echo fifu_lazy_src_type() ?>' + '"' + url + '" allowfullscreen frameborder="0" style="vertical-align:middle;padding:1px;margin:' + vertical_margin + 'px 0 ' + vertical_margin + 'px 0;width:100%;max-width:' + max_width + ';height:' + height + ';max-height:' + max_height + '"></iframe></div>';
            if ('<?php echo is_singular("product")?>')
                jQuery(this).replaceWith('<img style="display:none;background-color:black;max-width:' + img_width + ';max-height:' + img_height + '">' + $video);
            else
                jQuery(this).replaceWith($video);
        }
    });
}

function replaceImageDlg($) {
    jQuery('div.pswp__zoom-wrap').each(function () {
        index = jQuery('.pswp__counter').html().split(' ')[0] - 1;
        element = jQuery('.woocommerce-product-gallery__image')[index];
        dataThumb = jQuery(element).attr('data-thumb');
        if (!is_video_img(dataThumb))
            return;
        url = video_url(dataThumb)
        img = jQuery(this).find('img.pswp__img');
        w = jQuery(window).width() * 0.62;
        if (!img[0])
            jQuery(this).replaceWith('<div class="pswp__zoom-wrap" style="transform: translate3d(0px, 0px, 0px) scale(1);">' + '<div class="wrapper"><div class="video-wrapper">' + '<iframe class="pswp__video" src="' + url + '" frameborder="0" allowfullscreen="" width="' + w + '" height="' + w * 9 / 16 + '"></iframe>' + '</div></div></div>');
    });
}

jQuery(document).ajaxComplete(function ($) {
    jQuery('.fifu-video').each(function (index) {
        jQuery(this).css('opacity', '100');
    });
});

function is_video_img($src) {
    return !$src ? null : is_youtube_img($src) || is_vimeo_img($src) || is_cloudinary_video_img($src) || is_tumblr_video_img($src) || is_imgur_video_img($src) || is_facebook_video_img($src) || is_instagram_video_img($src) || is_gag_video_img($src);
}

function is_youtube_img($src) {
    return $src.includes('img.youtube.com');
}

function is_vimeo_img($src) {
    return $src.includes('i.vimeocdn.com');
}

function is_cloudinary_video_img($src) {
    return $src.includes('res.cloudinary.com') && $src.includes('/video/');
}

function is_tumblr_video_img($src) {
    return $src.includes('tumblr.com');
}

function is_imgur_video_img($src) {
    return $src.includes('imgur.com');
}

function is_facebook_video_img($src) {
    return $src.includes('facebook.com');
}

function is_instagram_video_img($src) {
    return $src.includes('instagram.com');
}

function is_gag_video_img($src) {
    return $src.includes('9cache.com');
}

function video_id($src) {
    if (is_youtube_img($src))
        return youtube_id($src);
    if (is_vimeo_img($src))
        return vimeo_id($src);
    if (is_facebook_img($src))
        return facebook_id($src);
    return null;
}

function youtube_parameter($src) {
    return $src.split('?')[1];
}

function youtube_id($src) {
    return $src.split('/')[4];
}

function vimeo_id($src) {
    return $src.split('?')[1];
}

function facebook_id($src) {
    return $src.split('/')[3];
}

function video_url($src) {
    if (is_youtube_img($src))
        return youtube_url($src);
    if (is_vimeo_img($src))
        return vimeo_url($src);
    if (is_cloudinary_video_img($src))
        return cloudinary_url($src);
    if (is_tumblr_video_img($src))
        return tumblr_url($src);
    if (is_imgur_video_img($src))
        return imgur_url($src);
    if (is_facebook_video_img($src))
        return facebook_url($src);
    if (is_instagram_video_img($src))
        return instagram_url($src);
    if (is_gag_video_img($src))
        return gag_url($src);
    return null;
}

function youtube_url($src) {
    return 'https://www.youtube.com/embed/' + youtube_id($src) + '?' + youtube_parameter($src) + '&enablejsapi=1';
}

function vimeo_url($src) {
    return 'https://player.vimeo.com/video/' + vimeo_id($src);
}

function cloudinary_url($src) {
    return $src.replace('jpg', 'mp4');
}

function tumblr_url($src) {
    $tmp = $src.replace('https://78.media.tumblr.com', 'https://vt.media.tumblr.com');
    return $tmp.replace('_smart1.jpg', '.mp4');
}

function imgur_url($src) {
    return $src.replace('jpg', 'mp4');
}

function facebook_url($src) {
    return 'https://www.facebook.com/video/embed?video_id=' + facebook_id($src);
}

function instagram_url($src) {
    return $src.replace('media/?size=l', 'embed/');
}

function gag_url($src) {
    return $src.split('_')[0] + '_460svvp9.webm';
}

jQuery(document).ready(function ($) {
    enabled = '<?php echo fifu_mouse_vimeo_enabled() ?>';
    if (!enabled)
        return;

    jQuery('iframe').each(function (index) {
        if (this.src.includes("vimeo.com")) {
            jQuery(this).on("mouseover", function () {
                $f(this).api("play");
            }).mouseout(function () {
                $f(this).api("pause");
            });
        }
    });
});

function onYouTubeIframeAPIReady() {
    enabled = '<?php echo fifu_mouse_youtube_enabled() ?>';
    if (!enabled)
        return;

    jQuery('iframe').each(function (index) {
        if (this.src.includes("youtu")) {
            var x = new YT.Player(this);
            jQuery(this).on("mouseover", function () {
                x.playVideo();
            }).mouseout(function () {
                x.pauseVideo();
            });
        }
    });
}
