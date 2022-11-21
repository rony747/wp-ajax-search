jQuery(document).ready(function ($) {
    let search_files = $('#rjs_search');
    let rjs_post_type = $('#rjs_post_type_field');
    let rjs_result_area = $('#rjs_search_result_area');

    search_files.keypress(function () {
        if ($(this).val().length > 2) {
            $.post(rjs.ajax_url, {'action':'rjs_search_ajax', 'rjs_nonce':rjs.rjs_nonce, 'rjs_search_text':search_files.val(), 'rjs_post_type':rjs_post_type.val()}, function (data) {
                rjs_result_area.html(data)

            })

        } else {

        }
    });

})