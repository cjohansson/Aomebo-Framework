/**
 * Window Handler
 *
 * @namespace wh_
 */

/**
 * Open Dialogues
 *
 * This variable holds open dialogues.
 *
 * @type {Array}
 */
var wh_openDialogues;

/**
 * Overflow-Y
 *
 * @type {Boolean}
 */
var wh_overflowY;

/**
 * Open Dialog
 *
 * This function opens up a jquery window
 * with options.
 *
 * @param {Object} object
 * @param {Object} options
 * @return {Object}|{Boolean}
 */
function wh_openDialog(object, options)
{
    if (typeof(object) != 'undefined'
        && typeof(options) != 'undefined'
    ) {
        var hash = wh_makeHashFromObject(object);
        if (typeof(wh_openDialogues[hash]) != 'undefined') {
            $(wh_openDialogues[hash]).dialog('close');
        }
        if (typeof(options['close']) == 'undefined') {
            eval("options['close'] = "
                + "function(event, ui) { "
                + "wh_closeDialog(hash); "
                + "$(this).dialog().remove(); }");
        }
        if (typeof(options['open']) == 'undefined') {
            options['open'] =
                function(event, ui) {
                    window.setTimeout(function() {
                        $(document)
                        .unbind('mousedown.dialog-overlay')
                        .unbind('mouseup.dialog-overlay');
                    }, 100);
                    $('html').css('overflowY', 'scroll');
                };
        }
        var dialog = $(object).dialog(options);
        wh_openDialogues[hash] = dialog;
        return dialog;
    } else {
        alert('Invalid parameters for wh_openDialog');
    }
    return false;
}

/**
 * Close Dialog
 *
 * This function closes a dialog.
 *
 * @param {String} hash
 * @return void
 */
function wh_closeDialog(hash)
{
    if (typeof(hash) != 'undefined') {
        wh_openDialogues[hash] = null;
    } else {
        alert('Invalid parameters for wh_closeDialog');
    }
}

/**
 * Make Hash From Object
 *
 * This function generates hash from object.
 *
 * @param {Object} object
 * @throws alert
 * @return {String}
 */
function wh_makeHashFromObject(object)
{
    if (typeof(object) != 'undefined') {
        return $(object).attr('class') + '_' + $(object).attr('id')+ '_' + $(object).attr('title');
    } else {
        alert('Invalid parameters for wh_makeHashFromObject');
    }
}

/**
 *
 */
$(document).ready(function(event) {
    wh_openDialogues = [];
    wh_overflowY = $('html').css('overflowY');
});
