// Utility functions
export function sameHeightChildren($) {
    $('[data-fvpupe-same-height-children]').each(function() {
        const $children = $(this).children();
        const maxHeight = Math.max.apply(null, 
            $children.map(function() {
                return $(this).outerHeight();
            }).get()
        );
        $children.height(maxHeight);
    });
}

export function reloadPage() {
    location.reload();
}