// Modal functionality
export function createModal($, html) {
    $('[data-fvpupe-modal]').remove();

    const $modal = $(`
        <div data-fvpupe-modal class="fvpupe-modal-wrapper">
            <div data-fvpupe-modal-overlay class="fvpupe-modal-overlay"></div>
            <div data-fvpupe-modal-content-wrapper class="fvpupe-modal-content">
                <div data-fvpupe-modal-close-button class="fvpupe-close-button"></div>
                <div data-fvpupe-modal-content></div>
            </div>
        </div>
    `);

    $('body').append($modal);
    $modal.fadeIn(200);

    if (html !== undefined) {
        setModalContent($, html, false);
    }

    return $modal;
}

export function setModalContent($, html, animate) {
    const $content = $('[data-fvpupe-modal-content]');
    if (animate) {
        $content.fadeOut(200, function() {
            $content.html(html).fadeIn(200);
        });
    } else {
        $content.html(html);
    }
}

export function closeModal($) {
    $('[data-fvpupe-modal]').fadeOut(200, function() {
        $(this).remove();
    });
}