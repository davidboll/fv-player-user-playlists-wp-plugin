// Playlist functionality
export function saveVideosOrder($) {
    const $wrapper = $('[data-fvpupe-edit-playlist-video-list]');
    if (!$wrapper.length) return;

    const playlist_id = $wrapper.data('fvpupe-edit-playlist-video-list');
    const order = [];
    
    $wrapper.find('[data-fvpupe-video-id]').each(function() {
        const $this = $(this);
        order.push({
            video_id: $this.data('fvpupe-video-id'),
            page_id: $this.data('fvpupe-page-id')
        });
    });

    $.post(ajax.url, {
        action: 'ajaxSaveVideosOrder',
        order: order,
        playlist_id: playlist_id
    }, function(response) {
        const json = $.parseJSON(response);
        window.fvpupe_set_message(json.message, json.status);
    });
}

export function savePlaylistOrder($) {
    const $wrapper = $('[data-fvpupe-userplaylist-list]');
    if (!$wrapper.length) return;

    const order = $wrapper.find('[data-fvpupe-userplaylist]')
                       .map(function() {
                           return $(this).data('fvpupe-userplaylist');
                       })
                       .get();

    $.post(ajax.url, {
        action: 'ajaxSavePlaylistOrder',
        order: order
    }, function(response) {
        const json = $.parseJSON(response);
        window.fvpupe_set_message(json.message, json.status);
    });
}

export function setPlaylistName($, name, id) {
    $(`[data-fvpupe-userplaylist="${id}"] [data-fvpupe-playlistname]`).text(name);
}