var currentUser;
var lastAccessTime;
var generate;
var generateSimple;
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
    var sharedByTemplate = "<span class=\"shared-by-label\">Shared by " + note.owner + "</span>";
    var sharedBy = owned ? "" : sharedByTemplate;
    var template = "\n        <div class=\"row\">\n            <div class=\"col-md-10 col-md-offset-1\">\n                <div class=\"panel note-panel " + sharedClass + " panel-default\">\n                    <textarea class=\"note-input\"\n                              onkeydown=\"checkDelete(event, this)\"\n                              onkeyup=\"updateNote(this)\"\n                              data-note-id=" + note.id + "\n                              onfocus=\"this.placeholder='Press backspace to delete note'\"\n                              onblur=\"this.placeholder=''\">" + note.content + "</textarea>\n                    <span class=\"tag-label\">Tags:</span>\n                    <input type=\"text\"\n                           class=\"tag-input\"\n                           placeholder=\"Untagged...\"\n                           onkeydown=\"updateTags(this)\"\n                           data-note-id=" + note.id + "\n                           value=\"" + note.tags.join(" ") + "\">\n                    " + sharedBy + "\n                    <div class=\"share-btn-container\">\n                        <span class=\"btn glyphicon glyphicon-share-alt share-btn\"\n                              onclick=\"shareModal(this.dataset['noteId'])\"\n                              data-note-id=" + note.id + "></span>\n                        <span class=\"btn glyphicon glyphicon-cloud-download share-btn\"\n                              onclick=\"download(this.dataset['noteId'])\"\n                              data-note-id=" + note.id + "></span>\n                    </div>\n                </div>\n            </div>\n        </div>\n    ";
    $("#notes-container").append($.parseHTML(template));
}
function newNote(obj) {
    obj.tags = [];
    obj.users = [];
    return obj;
}
function shareModal(id) {
    $(".share-panel-blackout").show();
    $("#share-input").val("");
    $("#share-text-btn").html("Share");
    $("#share-text-btn").data("note-id", id + "");
}
function shareNote(btn) {
    var email = $("#share-input").val();
    $.post("/note/" + idFromElement(btn) + "/share", { email: email }, function (resp) {
        console.log(resp);
        if (resp.error) {
            generate('warning', 'someOtherTheme', 'Unable to share note, recheck the entered email', 'bottomCenter');
        }
        else {
            generate('success', 'someOtherTheme', 'Note successfully shared', 'bottomCenter');
            $(".share-panel-blackout").fadeOut(1000);
        }
    });
}
var lastSearch = 0;
var numNotes = 0;
function search(query, callback) {
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
        if (callback)
            callback(allNotes.length);
        numNotes = allNotes.length;
    });
}
function downloadText(filename, text) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}
function generateCSV(noteId) {
    return $.makeArray(document.getElementsByClassName("note-input"))
        .filter(function (node) { return node.dataset.noteId == noteId || noteId == "all"; })
        .map(function (elem) {
        var tags = elem.nextSibling.nextSibling.nextSibling.nextSibling.value;
        return [elem.value, tags].join(",");
    }).join("\n");
}
function download(noteId) {
    downloadText("notes.csv", generateCSV(noteId));
}
function reportShare() {
    lastAccessTime = typeof lastAccessTime !== 'undefined' ? lastAccessTime : "2000-02-02 00:00:00";
    $.ajax({
        url: "/report",
        data: {
            last_access_time: lastAccessTime,
        },
        type: "GET",
        dataType: "json",
        success: function (json) {
            console.log(json);
            if (json.last_access_time) {
                if (lastAccessTime !== "2000-02-02 00:00:00") {
                    search("", function (n) {
                        console.log(n, numNotes);
                        if (n > numNotes) {
                            generateSimple('information', 'someOtherTheme', 'there are new notes shared with you', 'topRight');
                        }
                    });
                    search("");
                }
                lastAccessTime = json.last_access_time;
            }
            setTimeout(reportShare, 5000);
        },
        error: function (xhr, status, errorThrown) {
            if (status != "401") {
                setTimeout(reportShare, 7000);
                generate('warning', 'someOtherTheme', 'no connection', 'topRight', 7000);
            }
        },
        complete: function (xhr, status) {
        }
    });
}
$("#new-note-btn").on("click", function () {
    $.getJSON("/note/create", function (resp) { return addNote(newNote(resp)); });
});
$("#cancel-share-btn").on("click", function () { return $(".share-panel-blackout").hide(); });
$("#share-text-btn").on("click", function () {
    shareNote($("#share-text-btn")[0]);
});
$(".search-box").keyup(function () { return search($(".search-box").val()); });
$("#download-btn").on("click", function () {
    download("all");
});
$("#upload-btn").on("click", function () {
    $("#upload-file").click();
});
$("#upload-file").on("change", function (evt) {
    var file = evt.target.files[0];
    var reader = new FileReader();
    reader.onload = function () {
        var text = reader.result;
        text.split("\n").map(function (line) {
            var _a = line.split(","), cont = _a[0], tags = _a[1];
            $.getJSON("/note/create", function (resp) {
                var note = newNote(resp);
                note.content = cont;
                note.tags = tags.split(" ");
                addNote(note);
                updateNote($(".note-input[data-note-id=" + note.id + "]")[0]);
                updateTags($(".tag-input[data-note-id=" + note.id + "]")[0]);
            });
        });
    };
    reader.readAsText(file);
});
$(document).ready(function () {
    search("");
    setTimeout(reportShare, 5000);
});
