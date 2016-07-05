// The note type is used to deserialize messages from the server
type Note = { content: string,
              owner: string,
              id: string,
              tags: string[],
              users: User[]
};

type User = { email: string,
              name: string };

// currentUser gets filled in the actual HTML file that's served
var currentUser;
// Used for the notification system to know when a new note is available
var lastAccessTime;

// Dummy variables that get filled by the noty library
var generate;
var generateSimple;

// Helper to get the data-note-id attribute value
function idFromElement(elem : HTMLElement) : number {
    return Number($(elem).data("noteId"));
}

// Deletes the note from the interface
function deleteNote(elem : HTMLElement) : void {
    elem.parentNode.removeChild(elem);
}

// If the note is empty and backspace was pressed, delete the note
function checkDelete(evt : KeyboardEvent, noteArea : HTMLTextAreaElement) : void {
    if (noteArea.value == "" && evt.key == "Backspace") {
        $.post(`/note/${idFromElement(noteArea)}/delete`, (resp) => {
            console.log(resp);
            deleteNote(noteArea.parentElement.parentElement.parentElement);
        });
    }
}

// Update the note content and send it to the server
function updateNote(noteArea : HTMLTextAreaElement) : void {
    noteArea.style.height = "1px";
    noteArea.style.height = (30 + noteArea.scrollHeight) + "px";
    $.post(`/note/${idFromElement(noteArea)}/edit`,
           { content: noteArea.value, tags: $(noteArea).siblings(".tag-input").val() },
           (resp) => console.log(resp));
}

// Update the tags and send them to the server
function updateTags(tagText : HTMLInputElement) : void {
    $.post(`/note/${idFromElement(tagText)}/edit`,
           { content: $(tagText).siblings(".note-input").val(), tags: tagText.value },
           (resp) => console.log(resp));
}

// Generate a new note making sure to add event handlers and attach it to the interface
function addNote(note : Note) : void {
    const owned = note.owner == currentUser;
    const sharedClass = owned ? "" : "shared-note";
    const sharedByTemplate = `<span class="shared-by-label">Shared by ${note.owner}</span>`;
    const sharedBy = owned ? "" : sharedByTemplate;
    const template = `
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel note-panel ${sharedClass} panel-default">
                    <textarea class="note-input"
                              onkeydown="checkDelete(event, this)"
                              onkeyup="updateNote(this)"
                              data-note-id=${note.id}
                              onfocus="this.placeholder='Press backspace to delete note'"
                              onblur="this.placeholder=''">${note.content}</textarea>
                    <span class="tag-label">Tags:</span>
                    <input type="text"
                           class="tag-input"
                           placeholder="Untagged..."
                           onkeydown="updateTags(this)"
                           data-note-id=${note.id}
                           value="${note.tags.join(" ")}">
                    ${sharedBy}
                    <div class="share-btn-container">
                        <span class="btn glyphicon glyphicon-share-alt share-btn"
                              onclick="shareModal(this.dataset['noteId'])"
                              data-note-id=${note.id}></span>
                        <span class="btn glyphicon glyphicon-cloud-download share-btn"
                              onclick="download(this.dataset['noteId'])"
                              data-note-id=${note.id}></span>
                    </div>
                </div>
            </div>
        </div>
    `;
    $("#notes-container").append($.parseHTML(template));
}

// Creates a proper Note object from what the server returns on `create`
function newNote(obj) {
    obj.tags = [];
    obj.users = [];
    return obj;
}

// Pops open and resets the share modal
function shareModal(id : number) : void {
    $(".share-panel-blackout").show();
    $("#share-input").val("");
    $("#share-text-btn").html("Share");
    $("#share-text-btn").data("note-id", id + "");
}

// Try to share the note with the given user
function shareNote(btn : HTMLElement) {
    const email = $("#share-input").val();
    $.post(`/note/${idFromElement(btn)}/share`, { email: email }, (resp) => {
        console.log(resp)
        if (resp.error) {
            generate('warning', 'someOtherTheme', 'Unable to share note, recheck the entered email', 'bottomCenter')
        }
        else {
            generate('success', 'someOtherTheme', 'Note successfully shared', 'bottomCenter')
            $(".share-panel-blackout").fadeOut(1000);
        }
    });
}

// lastSearch prevents old ajax requests from being processed later and updating the interface
var lastSearch = 0;
// numNotes is here to detect if new notes were share with the user
var numNotes = 0;

// Apply the search query to all owned and shared notes
// The query can also be left empty to get all notes
// The given callback will be called with the new number of notes visible after the search
function search(query : string, callback? : ((numNotes : number) => void)) : void {
    var thisSearch = ++lastSearch;
    var allNotes : Note[] = [];
    $.when(
        $.getJSON("/notes/my", { query: query },
                  (notesObj : { notes : Note[] }) => {
                      allNotes = allNotes.concat(notesObj.notes)
                  }),
        $.getJSON("/notes/other", { query: query },
                  (notesObj : { notes : Note[] }) => {
                      allNotes = allNotes.concat(notesObj.notes)
                  }))
    .then(() => {
        if (thisSearch == lastSearch) {
            $("#notes-container").empty();
            allNotes.forEach(addNote);
        }
        if (callback) callback(allNotes.length);
        numNotes = allNotes.length;
    });

}

// Utility function to initiate a download of a text document
function downloadText(filename, text) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
}

// Generate the CSV of the note with the given id
// noteId can also be "all" to generate the CSV for all notes
function generateCSV(noteId : number | string) : string {
    return $.makeArray(document.getElementsByClassName("note-input"))
            .filter(node => node.dataset.noteId == noteId || noteId == "all")
            .map((elem : HTMLInputElement) => {
        const tags = (<HTMLInputElement>elem.nextSibling.nextSibling.nextSibling.nextSibling).value;
        return [elem.value, tags].join(",");
    }).join("\n");
}

// Download one or all notes
function download(noteId : number | string) : void {
    downloadText("notes.csv", generateCSV(noteId));
}

// Pings the server to check if there are any updates that need to be reported
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
                    search("", (n) => {
                        console.log(n, numNotes);
                        if (n > numNotes) {
                            generateSimple('information', 'someOtherTheme', 'there are new notes shared with you', 'topRight');
                        } else if (n < numNotes) {
                            generateSimple('information', 'someOtherTheme', 'some shared notes have been deleted', 'topRight');
                        }
                    });
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


$("#new-note-btn").on("click", () => {
    $.getJSON("/note/create", (resp) => addNote(newNote(resp)));
});

$("#cancel-share-btn").on("click", () => $(".share-panel-blackout").hide());

$("#share-text-btn").on("click", () => {
    shareNote($("#share-text-btn")[0]);
});

$(".search-box").keyup(() => search($(".search-box").val()));

$("#download-btn").on("click", () => {
    download("all");
});

$("#upload-btn").on("click", () => {
    $("#upload-file").click();
});

// Gets the CSV from the user and processes it
$("#upload-file").on("change", (evt : Event) => {
    const file = (<HTMLInputElement>evt.target).files[0];
    const reader = new FileReader();
    reader.onload = function () {
        const text : string = reader.result;
        text.split("\n").map(line => {
            const [cont, tags] = line.split(",");
            $.getJSON("/note/create", (resp) => {
                var note = newNote(resp);
                note.content = cont;
                note.tags = tags.split(" ");
                addNote(note);
                updateNote(<HTMLTextAreaElement>$(`.note-input[data-note-id=${note.id}]`)[0]);
                updateTags(<HTMLInputElement>$(`.tag-input[data-note-id=${note.id}]`)[0]);
            });
        });
    };
    reader.readAsText(file);
});

$(document).ready(() => {
    search(""); // We do this initially to populate the interface
    setTimeout(reportShare, 5000); // Spark the notification system
});
