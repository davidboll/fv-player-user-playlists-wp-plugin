// Main application file
import * as Sortable from './modules/sortable.js';
import * as Modal from './modules/modal.js';
import * as Messages from './modules/messages.js';
import * as Forms from './modules/forms.js';
import * as Playlist from './modules/playlist.js';
import * as Utils from './modules/utils.js';

jQuery(function($) {
    // Initialize functionality
    Utils.sameHeightChildren($);
    
    $(window).on('load resize', function() {
        Utils.sameHeightChildren($);
    });

    // Initialize sortable
    Sortable.initSortable($);

    // Expose global functions
    window.fvpupe_same_height_children = () => Utils.sameHeightChildren($);
    window.fvpupe_save_playlist_order = () => Playlist.savePlaylistOrder($);
    window.fvpupe_save_videos_order = () => Playlist.saveVideosOrder($);
    window.fvpupe_set_message = (msg, type) => Messages.setMessage($, msg, type);
    window.fvpupe_create_modal = (html) => Modal.createModal($, html);
    window.fvpupe_set_modal_content = (html, animate) => Modal.setModalContent($, html, animate);
    window.fvpupe_close_modal = () => Modal.closeModal($);
    window.fvpupe_reload_page = () => Utils.reloadPage();
    window.fvpupe_set_playlist_name = (name, id) => Playlist.setPlaylistName($, name, id);

    // Event handlers
    $('body').on('submit', '[data-fvpupe-saveform]', function(e) {
        e.preventDefault();
        Forms.handleFormSubmit($, $(this));
    });

    $('body').on('click', '[data-fvpupe-modal-close-button], [data-fvpupe-closemodal], [data-fvpupe-modal-overlay]', function(e) {
        e.preventDefault();
        Modal.closeModal($);
    });

    // Initialize other event handlers
    initEventHandlers($);
});

function initEventHandlers($) {
    // Add your event handlers here
    // This keeps the main file clean while allowing for easy event handling setup
}