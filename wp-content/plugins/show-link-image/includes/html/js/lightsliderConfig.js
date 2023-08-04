(function ($) {
    $(document).ready(function () {
        $(".gallery.list-unstyled").lightSlider({
            gallery: true,
            mode: "<?php echo fifu_is_on('fifu_slider_fade') ?'fade':'slide' ?>",
            item: 1,
            thumbItem: 5,
            slideMargin: 0,
            adaptiveHeight: false,
            speed: "<?php echo get_option('fifu_slider_speed') ?>",
            auto: "<?php echo fifu_is_on('fifu_slider_auto') ?>",
            loop: true,
            freeMove: true,
            enableDrag: false,
            enableTouch: true,
            pager: false,
            vertical: false,
            verticalHeight: 300,
            vThumbWidth: 100,
            slideEndAnimation: false,
            pause: "<?php echo get_option('fifu_slider_pause') ?>",
            controls: "<?php echo fifu_is_on('fifu_slider_ctrl') ?>",
            pauseOnHover: "<?php echo fifu_is_on('fifu_slider_stop') ?>",
            onSliderLoad: function (el) {
                $lg = el.lightGallery({
                    selector: '.gallery.list-unstyled <?php echo fifu_is_on("fifu_slider_gallery") ? ".lslide": "" ?>'
                });

                $lg.on('onAfterOpen.lg', function (event) {
                    fifu_resize_gallery_flickr();
                    fifu_resize_gallery_speedup();
                });

                $lg.on('onBeforeSlide.lg', function (event) {
                    fifu_resize_gallery_flickr();
                    fifu_resize_gallery_speedup();
                });

                $(".gallery.list-unstyled").removeClass("cS-hidden");
            },
            onBeforeStart: function (el) {
            },
        });
    });
})(jQuery);

function fifu_resize_gallery_flickr() {
    jQuery('img.lg-object[src*="staticflickr.com"]').each(function (index) {
        img = jQuery(this);
        url = img.attr('src');
        if (!url)
            return;
        width = lightbox_size_auto(window.innerWidth, window.innerHeight, true);
        url = url.replace(/(_.)*[.]jpg/, width + '.jpg');
        jQuery(this).attr('src', url);
        jQuery(this).removeAttr('srcset');
    });
}

function fifu_resize_gallery_speedup() {
    jQuery('img.lg-object[src*="storage.googleapis.com/fifu"]').each(function (index) {
        img = jQuery(this);
        url = img.attr('src');
        if (!url)
            return;
        width = lightbox_size_auto(window.innerWidth, window.innerHeight, false);
        url = url.replace(/img.*/, 'img-' + width + '.webp');
        jQuery(this).attr('src', url);
        jQuery(this).removeAttr('srcset');
    });
}
