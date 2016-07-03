var currentUser;
function idFromElement(elem) {
    return Number($(elem).data("noteId"));
}
function deleteNote(elem) {
    elem.parentNode.removeChild(elem);
}
function checkDelete(evt, noteArea) {
    if (noteArea.value == "" && evt.key == "Backspace") {
        $.post("/note/" + idFromElement(noteArea) + "/delete", function (resp) {
            console.log(resp);
            deleteNote(noteArea.parentElement.parentElement.parentElement);
        });
    }
}
function updateNote(noteArea) {
    noteArea.style.height = "1px";
    noteArea.style.height = (30 + noteArea.scrollHeight) + "px";
    $.post("/note/" + idFromElement(noteArea) + "/edit", { content: noteArea.value, tags: $(noteArea).siblings(".tag-input").val() }, function (resp) { return console.log(resp); });
}
function updateTags(tagText) {
    $.post("/note/" + idFromElement(tagText) + "/edit", { content: $(tagText).siblings(".note-input").val(), tags: tagText.value }, function (resp) { return console.log(resp); });
}
function addNote(note) {
    var owned = note.owner == currentUser;
    var sharedClass = owned ? "" : "shared-note";
    var template = "\n        <div class=\"row\">\n            <div class=\"col-md-10 col-md-offset-1\">\n                <div class=\"panel note-panel " + sharedClass + " panel-default\">\n                    <textarea class=\"note-input\"\n                              onkeydown=\"checkDelete(event, this)\"\n                              onkeyup=\"updateNote(this)\"\n                              data-note-id=" + note.id + "\n                              onfocus=\"this.placeholder='Press backspace to delete note'\"\n                              onblur=\"this.placeholder=''\">" + note.content + "</textarea>\n                    <span class=\"tag-label\">Tags:</span>\n                    <input type=\"text\"\n                           class=\"tag-input\"\n                           placeholder=\"Untagged...\"\n                           onkeydown=\"updateTags(this)\"\n                           data-note-id=" + note.id + "\n                           value=\"" + note.tags.join(" ") + "\">\n                    <div class=\"share-btn-container\">\n                        <span class=\"btn glyphicon glyphicon-share-alt share-btn\"\n                              onclick=\"shareModal(this.dataset['noteId'])\"\n                              data-note-id=" + note.id + "></span>\n                    </div>\n                </div>\n            </div>\n        </div>\n    ";
    $("#notes-container").append($.parseHTML(template));
}
function newNote(obj) {
    obj.tags = [];
    obj.users = [];
    return obj;
}
function setCurrentUser(email) {
    $("#user-email-span").html(email);
    currentUser = email;
}
function shareModal(id) {
    $(".share-panel-blackout").show();
    $("#share-input").val("");
    $("#share-text-btn").data("note-id", id + "");
}
function shareNote(btn) {
    var email = $("#share-input").val();
    $.post("/note/" + idFromElement(btn) + "/share", { email: email }, function (resp) { return console.log(resp); });
}
var lastSearch = 0;
function search(query) {
    var thisSearch = ++lastSearch;
    var allNotes = [];
    $.when($.getJSON("/notes/my", { query: query }, function (notesObj) {
        allNotes = allNotes.concat(notesObj.notes);
    }), $.getJSON("/notes/other", { query: query }, function (notesObj) {
        allNotes = allNotes.concat(notesObj.notes);
    }))
        .then(function () {
        if (thisSearch == lastSearch) {
            $("#notes-container").empty();
            allNotes.forEach(addNote);
        }
    });
}
$("#new-note-btn").on("click", function () {
    $.getJSON("/note/create", function (resp) { return addNote(newNote(resp)); });
});
$("#cancel-share-btn").on("click", function () { return $(".share-panel-blackout").hide(); });
$("#share-text-btn").on("click", function () {
    shareNote($("#share-text-btn")[0]);
    $(".share-panel-blackout").hide();
});
$(".search-box").keyup(function () { return search($(".search-box").val()); });
$(document).ready(function () {
    $.getJSON("/user", function (json) { return setCurrentUser(json.user.email); })
        .then(function () {
        ;
        $.getJSON("/notes/my", function (notesObj) { return notesObj.notes.forEach(addNote); });
        $.getJSON("/notes/other", function (notesObj) { return notesObj.notes.forEach(addNote); });
    });
});
