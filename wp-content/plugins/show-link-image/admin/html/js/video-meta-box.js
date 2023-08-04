function removeVideo() {
    jQuery("#fifu_video").hide();
    jQuery("#fifu_video_link").hide();

    jQuery("#fifu_video_input_url").val("");

    jQuery("#fifu_video_button").show();
}

function previewVideo() {
    var $url = jQuery("#fifu_video_input_url").val();

    if ($url) {
        jQuery("#fifu_video_button").hide();

        jQuery("#fifu_video_iframe").attr("src", srcVideo($url));

        jQuery("#fifu_video").show();
        jQuery("#fifu_video_link").show();
    }

}

function isYoutubeUrl($url) {
    return $url.includes("youtu");
}

function isVimeoUrl($url) {
    return $url.includes("vimeo.com");
}

function isCloudinaryVideoUrl($url) {
    return $url.includes("cloudinary.com") && $url.includes("/video/");
}

function isTumblrVideoUrl($url) {
    return $url.includes("tumblr.com");
}

function isImgurVideoUrl($url) {
    return $url.includes("imgur.com") && $url.includes("mp4");
}

function isFacebookVideoUrl($url) {
    return $url.includes("facebook.com") && ($url.includes("/videos/") || $url.includes("/watch/"));
}

function isInstagramVideoUrl($url) {
    return $url.includes("instagram.com");
}

function isGagVideoUrl($url) {
    return $url.includes("9cache.com");
}

function idYoutube($url) {
    var $regex = /^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/;
    return $res = $url.match($regex);
}

function idVimeo($url) {
    var $regex = /^(http\:\/\/|https\:\/\/)?(www\.)?(vimeo\.com\/)([0-9]+)$/;
    return $res = $url.match($regex);
}

function idFacebook($url) {
    var $regex = /^(http\:\/\/|https\:\/\/)?(www\.)?(facebook\.com\/)?([^\/]+\/videos\/|watch\/\?v=)?([0-9]+)?([\/]*)$/;
    return $res = $url.match($regex);
}

function srcYoutube($url) {
    return "https://www.youtube.com/embed/" + idYoutube($url)[1];
}

function srcVimeo($url) {
    return "https://player.vimeo.com/video/" + idVimeo($url)[4];
}

function srcCloudinary($url) {
    return $url;
}

function srcTumblr($url) {
    return $url;
}

function srcImgur($url) {
    return $url;
}

function srcFacebook($url) {
    return "https://www.facebook.com/video/embed?video_id=" + idFacebook($url)[5];
}

function srcInstagram($url) {
    return $url + "embed/";
}

function srcGag($url) {
    return $url;
}

function srcVideo($url) {
    if (isYoutubeUrl($url))
        return srcYoutube($url);
    if (isVimeoUrl($url))
        return srcVimeo($url);
    if (isCloudinaryVideoUrl($url))
        return srcCloudinary($url);
    if (isTumblrVideoUrl($url))
        return srcTumblr($url);
    if (isImgurVideoUrl($url))
        return srcImgur($url);
    if (isFacebookVideoUrl($url))
        return srcFacebook($url);
    if (isInstagramVideoUrl($url))
        return srcInstagram($url);
    if (isGagVideoUrl($url))
        return srcGag($url);
    return null;
}
