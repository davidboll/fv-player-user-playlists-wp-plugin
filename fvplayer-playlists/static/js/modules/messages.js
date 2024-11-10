// Message handling
export function setMessage($, message, type) {
    let $wrapper = $('[data-fvpupe-message-wrapper]');
    if ($wrapper.length === 0) {
        $wrapper = $('<div data-fvpupe-message-wrapper class="fvpupe-message-wrapper"></div>');
        $('body').append($wrapper);
    }

    const $message = $(`
        <div data-fvpupe-message class="fvpupe-message fvpupe-message-type-${type === 1 ? 'success' : 'error'}">
            ${message}
        </div>
    `);
    
    $wrapper.append($message);
    $message.fadeIn(200);

    setTimeout(function() {
        $message.fadeOut(200, function() {
            $(this).remove();
        });
    }, 5000);
}