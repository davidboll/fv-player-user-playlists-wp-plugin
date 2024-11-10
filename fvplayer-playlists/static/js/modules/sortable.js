// Sortable functionality
export function initSortable($) {
    $('[data-fvpupe-sortable-playlist]').sortable({
        placeholder: "fvpupe-sortable-placeholder",
        handle: ".fvpupe-order-handle",
        delay: 150,
        tolerance: "pointer",
        scroll: true,
        change: function() {
            window.fvpupe_same_height_children();
        },
        start: function() {
            window.fvpupe_same_height_children();
            $('body').css('overflow', 'hidden');
        },
        stop: function() {
            window.fvpupe_save_playlist_order();
            $('body').css('overflow', '');
        }
    }).disableSelection();

    initVideoSortable($);
}

export function initVideoSortable($) {
    $('[data-fvpupe-sortable-videos]').sortable({
        placeholder: "fvpupe-sortable-placeholder",
        handle: ".fvpupe-order-handle-updown",
        delay: 150,
        tolerance: "pointer",
        scroll: true,
        start: function() {
            $('body').css('overflow', 'hidden');
        },
        stop: function() {
            window.fvpupe_save_videos_order();
            $('body').css('overflow', '');
        }
    }).disableSelection();
}