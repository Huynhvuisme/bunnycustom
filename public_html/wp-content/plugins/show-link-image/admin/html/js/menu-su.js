jQuery(function () {
    jQuery("#su-tabs").tabs();
});

function signUp() {
    var firstName = jQuery('#su_first_name').val();
    var lastName = jQuery('#su_last_name').val();
    var email = jQuery('#su_email').val();
    var site = jQuery('#su_site').val();

    if (!firstName || !lastName || !email || !site)
        return;

    var code = null;

    fifu_block();

    jQuery.ajax({
        method: "POST",
        url: homeUrl() + "/wp-json/fifu-premium/v2/sign_up/",
        data: {
            "first_name": firstName,
            "last_name": lastName,
            "email": email
        },
        async: true,
        success: function (data) {
            code = data['code'];
            message(data);

            if (code > 0) {
                remove_sign_up();
                active_first_tab();
                enable_login();
            }
            fifu_unblock();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
            fifu_unblock();
        }
    });
    return code;
}

function login() {
    var email = jQuery('#su_login_email').val();
    var site = jQuery('#su_login_site').val();

    if (!email || !site)
        return;

    var code = null;

    fifu_block();

    jQuery.ajax({
        method: "POST",
        url: homeUrl() + "/wp-json/fifu-premium/v2/login/",
        data: {
            "email": email
        },
        async: true,
        success: function (data) {
            code = data['code'];
            message(data);

            if (code > 0) {
                jQuery("#su_login_email").attr("disabled", true);
                jQuery("#su_login_button").attr("disabled", true);
                jQuery("#su_login_button").val("logged in");
            }
            fifu_unblock();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
            fifu_unblock();
        }
    });
    return code;
}

function resetCredentials() {
    var email = jQuery('#su_login_email').val();
    var site = jQuery('#su_login_site').val();

    if (!email || !site)
        return;

    var code = null;

    fifu_block();

    jQuery.ajax({
        method: "POST",
        url: homeUrl() + "/wp-json/fifu-premium/v2/reset_credentials/",
        data: {
            "email": email
        },
        async: true,
        success: function (data) {
            code = data['code'];
            message(data);

            if (code > 0) {
                remove_sign_up();
                enable_login();
            }
            fifu_unblock();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
            fifu_unblock();
        }
    });
    return code;
}

fifu_su_set = new Set();

function listAllSu() {
    var table = jQuery('#removeTable').DataTable({
        destroy: true,
        "columns": [{"width": "64px"}],
        "autoWidth": false,
        dom: 'lfrtBip',
        select: true,
        buttons: [
            {
                text: 'select all',
                action: function () {
                    table.rows({page: 'current'}).select();
                }
            },
            {
                text: 'select none',
                action: function () {
                    table.rows().deselect();
                }
            },
            {
                text: 'remove',
                action: function () {
                    fifu_su_set.clear();
                    jQuery("#su-dialog").dialog("open");
                }
            },
        ]
    });

    table.clear();
    fifu_block();

    jQuery.ajax({
        method: "POST",
        url: homeUrl() + "/wp-json/fifu-premium/v2/list_all_su/",
        async: true,
        success: function (data) {
            code = data['code'];
            message(data);
            var bucket = data['bucket'];
            var ids = data['ids'];
            for (var i = 0; i < ids.length; i++) {
                var url = 'https://storage.googleapis.com/' + bucket + '/' + ids[i] + '/img-75.webp';
                imgTag = '<img id="' + ids[i]['meta_id'] + '" data-src="' + url + '" style="border-radius:5%; height:48px; width:64px; object-fit:cover; text-align:center">';
                table.row.add([imgTag, ids[i]]);
            }
            table.draw(true);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        },
        complete: function (data) {
            fifu_unblock();
        }
    });

    jQuery("#su-dialog").dialog({
        autoOpen: false,
        modal: true,
        width: "400px",
        buttons: {
            "Delete": function () {
                count = table.rows({selected: true}).count();
                for (var i = 0; i < count; i++) {
                    id = table.rows({selected: true}).data()[i][1];
                    fifu_su_set.add(id);
                }
                fifu_block();
                jQuery(this).dialog("close");
                jQuery.ajax({
                    method: "POST",
                    url: homeUrl() + "/wp-json/fifu-premium/v2/delete/",
                    data: {
                        "ids": Array.from(fifu_su_set)
                    },
                    async: true,
                    success: function (data) {
                        table.rows().deselect();
                        listAllSu();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        fifu_unblock();
                        console.log(jqXHR);
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
                });
            },
            Cancel: function () {
                jQuery(this).dialog("close");
            }
        }
    });
}

jQuery(document).ready(function ($) {
    jQuery.extend(jQuery.lazyLoadXT, {
        srcAttr: 'data-src',
        visibleOnly: true,
        updateEvent: 'load orientationchange resize scroll touchmove focus hover'
    });
});
jQuery(window).on('ajaxComplete', function () {
    jQuery(window).lazyLoadXT();
});

jQuery(document).on("click", "a.paginate_button, select", function () {
    jQuery(window).lazyLoadXT();
});

jQuery(document).ready(function ($) {
    jQuery('#addTable tbody').on('click', 'tr', function () {
        jQuery(this).toggleClass('selected');
    });
});

function listAllFifu() {
    var table = jQuery('#addTable').DataTable({
        destroy: true,
        "columns": [{"width": "64px"}, {"width": "100%"}, {"width": "64px"}],
        "autoWidth": false,
        dom: 'lfrtBip',
        select: true,
        buttons: [
            {
                text: 'select all',
                action: function () {
                    table.rows({page: 'current'}).select();
                }
            },
            {
                text: 'select none',
                action: function () {
                    table.rows().deselect();
                }
            },
            {
                text: 'add',
                action: function () {
                    addSu(table);
                }
            },
        ]
    });
    table.clear();
    fifu_block();
    jQuery.ajax({
        method: "POST",
        url: homeUrl() + "/wp-json/fifu-premium/v2/list_all_fifu/",
        async: true,
        success: function (data) {
            for (var i = 0; i < data.length; i++) {
                imgTag = '<img id="' + data[i]['meta_id'] + '" data-src="' + data[i]['url'] + '" style="border-radius:5%; height:48px; width:64px; object-fit:cover; text-align:center">';
                table.row.add([imgTag, data[i]['post_title'], data[i]['post_date'], data[i]['post_id']]);
            }
            table.draw(true);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR);
            console.log(textStatus);
            console.log(errorThrown);
        },
        complete: function (data) {
            fifu_unblock();
        }
    });
}

function addSu(table) {
    fifu_su_set.clear();

    count = table.rows({selected: true}).count();
    for (var i = 0; i < count; i++) {
        id = table.rows({selected: true}).data()[i][3];
        fifu_su_set.add(id);
    }

    fifu_block();

    jQuery.ajax({
        method: "POST",
        url: homeUrl() + "/wp-json/fifu-premium/v2/delete/",
        data: {
            //         "ids": Array.from(fifu_su_set)
        },
        async: true,
        success: function (data) {
            //         table.rows().deselect();
            //         listAllSu();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            //         fifu_unblock();
            //         console.log(jqXHR);
            //         console.log(textStatus);
            //         console.log(errorThrown);
        }
    });
}

function remove_sign_up() {
    jQuery("#su-tabs-1").remove();
    jQuery("#su-li-tabs-1").remove();
}

function disable_login() {
    jQuery("#su_login_button").attr("disabled", true);
}

function enable_login() {
    jQuery("#su_login_button").attr("disabled", false);
}

function active_first_tab() {
    jQuery("#su-tabs").tabs("option", "active", 1);
}

function message(data) {
    jQuery("#su_response_message").css('background-color', data['color']);
    jQuery("#su_response_message").css('border-radius', '3px');
    jQuery("#su_response_message").css('padding', '6px');
    jQuery("#su_response_message").css('color', 'white');
    jQuery("#su_response_message").val(data['message']);
}

jQuery(function () {
    jQuery("#su-dialog").dialog({
        autoOpen: false,
        modal: true,
        width: "400px",
        buttons: {
            "Delete": function () {
                fifu_block();
                jQuery(this).dialog("close");
                jQuery.ajax({
                    method: "POST",
                    url: homeUrl() + "/wp-json/fifu-premium/v2/delete/",
                    data: {
                        "ids": Array.from(fifu_su_set)
                    },
                    async: true,
                    success: function (data) {
                        fifu_unblock();
                        for (let id of fifu_su_set)
                            jQuery('#' + id).hide();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR);
                        console.log(textStatus);
                        console.log(errorThrown);
                        fifu_unblock();
                    }
                });
            },
            Cancel: function () {
                jQuery(this).dialog("close");
            }
        }
    });
});

function fifu_block() {
    jQuery('.wrap').block({message: 'Please wait some seconds...', css: {backgroundColor: 'none', border: 'none', color: 'white'}});
}

function fifu_unblock() {
    jQuery('.wrap').unblock();
}
