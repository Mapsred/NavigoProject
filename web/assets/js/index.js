/**
 * Created by maps_red on 17/11/16.
 */
$(document).ready(function () {
    $(".cookie-disclaimer-accept-button").click(function (e) {
        e.preventDefault();
        var date = new Date();
        date.setTime(date.getTime() + (12 * 30 * 24 * 60 * 60 * 1000));
        document.cookie = "cookieBanner=accepted; expires=" + date.toGMTString() + "; path=/;domain=" + base_domain;
        $(this).parent().hide();
    });
});
