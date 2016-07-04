type Note = { content: string,
              owner: string,
              id: string,
              tags: string[],
              users: User[]
};

type User = { email: string,
              name: string };

var currentUser;
var lastAccessTime;
var generate;

function idFromElement(elem : HTMLElement) : number {
    return Number($(elem).data("noteId"));
}

function deleteNote(elem : HTMLElement) : void {
    elem.parentNode.removeChild(elem);
}

function checkDelete(evt : KeyboardEvent, noteArea : HTMLTextAreaElement) : void {
    if (noteArea.value == "" && evt.key == "Backspace") {
        $.post(`/note/${idFromElement(noteArea)}/delete`, (resp) => {
            console.log(resp);
            deleteNote(noteArea.parentElement.parentElement.parentElement);
        });
    }
}

function updateNote(noteArea : HTMLTextAreaElement) : void {
    noteArea.style.height = "1px";
    noteArea.style.height = (30 + noteArea.scrollHeight) + "px";
    $.post(`/note/${idFromElement(noteArea)}/edit`,
           { content: noteArea.value, tags: $(noteArea).siblings(".tag-input").val() },
           (resp) => console.log(resp));
}

function updateTags(tagText : HTMLInputElement) : void {
    $.post(`/note/${idFromElement(tagText)}/edit`,
           { content: $(tagText).siblings(".note-input").val(), tags: tagText.value },
           (resp) => console.log(resp));
}

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

function newNote(obj) {
    obj.tags = [];
    obj.users = [];
    return obj;
}

function setCurrentUser(email : string) : void {
    $("#user-email-span").html(email);
    currentUser = email;
}

function shareModal(id : number) : void {
    $(".share-panel-blackout").show();
    $("#share-input").val("");
    $("#share-text-btn").html("Share");
    $("#share-text-btn").data("note-id", id + "");
}

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

var lastSearch = 0;
function search(query : string) : void {
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

function generateCSV(noteId : number | string) : string {
    return $.makeArray(document.getElementsByClassName("note-input"))
            .filter(node => node.dataset.noteId == noteId || noteId == "all")
            .map((elem : HTMLInputElement) => {
        const tags = (<HTMLInputElement>elem.nextSibling.nextSibling.nextSibling.nextSibling).value;
        return [elem.value, tags].join(",");
    }).join("\n");
}

function download(noteId : number | string) : void {
    downloadText("notes.csv", generateCSV(noteId));
}

function reportShare() {
    lastAccessTime = typeof lastAccessTime !== 'undefined' ? lastAccessTime : "2000-02-02 00:00:00";
    $.ajax(
        {
            url: "/report",
            data:
            {
                last_access_time: lastAccessTime,
            },
            type: "GET",
            dataType: "json", // očekivani povratni tip podatka
            success: function( json ) {
                console.log(json);
                if(json.last_access_time) {
                    if(lastAccessTime !== "2000-02-02 00:00:00") {
                        search("");
                        generate('information', 'someOtherTheme', 'there are new notes shared with you', 'topRight', 2000);
                    }
                    lastAccessTime = json.last_access_time;
                }
                setTimeout(reportShare, 5000);
            },
            error: function( xhr, status, errorThrown ) {
                if (status != "401") {
                    setTimeout(reportShare, 7000);
                    generate('warning', 'someOtherTheme', 'no connection', 'topRight', 7000);
                }
            },
            complete: function( xhr, status ) {
            }
        }
    );
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
    search("");
    setTimeout(reportShare, 5000);
});
