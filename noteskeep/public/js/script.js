function updateNote(noteArea) {
    noteArea.style.height = "1px";
    noteArea.style.height = (25 + noteArea.scrollHeight) + "px";
    $.post("/note/" + noteArea.dataset["noteId"] + "/edit", { content: noteArea.value, tags: "" }, function (resp) { return console.log(resp); });
}
function addNote(note) {
    var template = "\n        <div class=\"row\">\n            <div class=\"col-md-10 col-md-offset-1\">\n                <div class=\"panel note-panel panel-default\">\n                    <textarea class=\"note-input\"\n                              onkeyup=\"updateNote(this)\"\n                              data-note-id=" + note.id + ">" + note.content + "</textarea>\n                </div>\n            </div>\n        </div>\n    ";
    $("#notes-container").append($.parseHTML(template));
}
$("#new-note-btn").on("click", function () {
    $.getJSON("/note/create", addNote);
});
$(document).ready(function () {
    $.getJSON("/notes/my", function (notesObj) { return notesObj.notes.forEach(addNote); });
});
