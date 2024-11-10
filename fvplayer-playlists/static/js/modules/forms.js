// Form handling
export function handleFormSubmit($, $form) {
    if ($form.hasClass('disabled')) return;

    $form.addClass('disabled');
    const data = $form.serialize() + '&action=' + $form.attr('action');

    $.post(ajax.url, data, function(response) {
        $form.removeClass('disabled');
        const json = $.parseJSON(response);
        window.fvpupe_set_message(json.message, json.status);

        if ($form.is('[data-fvpupe-aftersave]') && json.status == 1) {
            handleAfterSave($, $form);
        }
    });
}

export function handleAfterSave($, $form) {
    const funcs = $form.attr('data-fvpupe-aftersave').split(' ');
    funcs.forEach(function(func_name) {
        const func = window[func_name];
        if (typeof func === "function") {
            if (func_name === 'fvpupe_set_playlist_name') {
                func(
                    $form.find('[name="fvpupe-playlist-name"]').val(),
                    $form.find('[name="fvpupe-playlistid"]').val()
                );
            } else {
                func();
            }
        }
    });
}