$(document).ready(function () {
    initDescriptionItem();
});

/**
 * Initializes description items with popover functionality.
 * Popovers are activated on elements with the attribute data-toggle="popover".
 * Popovers are configured to be HTML-enabled and placed on top of the triggering element.
 * After a popover is shown, this function attaches a copy-to-clipboard functionality to it.
 * Popovers are hidden when clicking outside of them or on elements with the class 'close'.
 * 
 * @function initDescriptionItem
 * @returns {void}
 */
function initDescriptionItem() {

    $('[data-toggle="popover"]').each(function () {
        // Get the title with the close button
        var titleWithCloseButton = 'Hint <a class="close close-hint" href="#">&times;</a>';

        // Initialize popover for each element
        $(this).popover({
            html: true,
            title: titleWithCloseButton,
            placement: 'top'
        }).on('shown.bs.popover', function () {
            addCopyToClipboard();
            $('.close-hint').off('click').click(function () {
                $('[data-toggle="popover"]').popover('hide');
            });
        });
    });
}

/**
 * Adds a copy icon to each <pre> element containing <code>, enabling users to copy the code inside.
 */
function addCopyToClipboard() {
    $('pre > code').each(function () {
        var preElement = $(this).parent();
        // Check if the icon already exists to avoid duplicates
        if (preElement.find('.copy-icon').length === 0) {
            // Create a copy icon element
            var copyIcon = $('<i class="fas fa-copy copy-icon" style="position:absolute; top:5px; right:5px; cursor:pointer;"> Copy</i>');

            // Insert the copy icon inside the <pre> element, but outside <code>
            preElement.css('position', 'relative').prepend(copyIcon);

            // Attach click event to the icon for copy functionality
            copyIcon.click(function (event) {
                // Prevent the click from closing the popover 
                event.preventDefault();
                event.stopPropagation();
                // Copy text to clipboard from <code>
                var textToCopy = $(this).siblings('code').text();
                navigator.clipboard.writeText(textToCopy).then(() => {
                    // Change the icon to a tick to indicate success
                    $(this).removeClass('fa-copy').addClass('fa-check');
                    $(this).html(' Copied');
                    $(this).addClass('text-success');
                    // Optional: Revert back to copy icon after some time
                    setTimeout(() => {
                        $(this).removeClass('fa-check').addClass('fa-copy');
                        $(this).html(' Copy');
                        $(this).removeClass('text-success');
                    }, 5000); // 5 seconds delay
                }, (error) => {
                    // Error feedback
                    console.error('Error copying text: ', error);
                });
            });
        }
    });
}
