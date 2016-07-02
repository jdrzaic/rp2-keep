type Note = { content: string,
              owner: string,
              updated_at: string,
              created_at: string,
              id: string }

function updateNote(noteArea : HTMLTextAreaElement) : void {
    noteArea.style.height = "1px";
    noteArea.style.height = (25 + noteArea.scrollHeight) + "px";
    $.post(`/note/${noteArea.dataset["noteId"]}/edit`,
           { content: noteArea.value, tags: "" },
           (resp) => console.log(resp));
}

function addNote(note : Note) : void {
    const template = `
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel note-panel panel-default">
                    <textarea class="note-input"
                              onkeyup="updateNote(this)"
                              data-note-id=${note.id}>${note.content}</textarea>
                </div>
            </div>
        </div>
    `;
    $("#notes-container").append($.parseHTML(template));
}

$("#new-note-btn").on("click", () => {
    $.getJSON("/note/create", addNote);
});

$(document).ready(() => {
    $.getJSON("/notes/my", (notesObj : { notes : Note[] }) => notesObj.notes.forEach(addNote));
});
