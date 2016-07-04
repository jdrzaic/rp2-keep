/**
 * Created by jelenadrzaic on 04/07/16.
 */

function generate(type, theme, text, layout) {
    var n = noty({
        text        : text,
        type        : type,
        dismissQueue: true,
        layout      : layout,
        theme       : theme,
        closeWith   : ['button', 'click'],
        maxVisible  : 20,
        timeout     : 1500,
        modal       : true
    });
}