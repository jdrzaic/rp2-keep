/**
 * Created by jelenadrzaic on 04/07/16.
 */

function generate(type, theme, text, layout, timeout) {
    timeout = typeof timeout !== 'undefined' ? timeout : 1500;
    var n = noty({
        text        : text,
        type        : type,
        dismissQueue: true,
        layout      : layout,
        theme       : theme,
        closeWith   : ['button', 'click'],
        maxVisible  : 20,
        timeout     : timeout,
        modal       : true
    });
}