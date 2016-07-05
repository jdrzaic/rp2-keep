<?php

namespace App\Http\Controllers;

use App\NotesService;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Util;

/**
 * Class is responsible for handling request connected to a single note.
 *
 * Class NoteController
 * @package App\Http\Controllers
 */
class NoteController extends Controller
{
    /**
     * NoteController constructor.
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Returns note with given id
     * @param Request $request
     * @param $id note id
     * @return note, if one with given id exists, error json response, otherwise
     */
    public function index(Request $request, $id) {
        $notesServise = new NotesService();
        $note = $notesServise->getNote($id);
        if($note == null) {
            return json_encode(array('error' => 'Note with given id not found'));
        }
        return json_encode(Util::getNotesResponse($note));

    }

    /**
     * Creates a new, empty note, and returns it, json-formatted
     * @return created note
     */
    public function create() {
        $notesServise = new NotesService();
        $note = $notesServise->createNote();
        return $note->toJson();
    }

    /**
     * Method handles sharing of a note with a user whose email is provided in POST variable 'email' of
     * request.
     * @param Request $request
     * @param $id note id
     * @return shared note
     */
    public function share(Request $request, $id) {
        $notesService = new NotesService();
        if($notesService->isNoteAccessible($id) == false) {
            return json_encode(array('error' => 'permission denied(note is not created by or shared with you'));
        }
        if($request->has('email') == false) {
            return response()->json(["error" => 'parameter "email" missing']);
        }
        $email = $request->input('email');
        $note = $notesService->shareNote($id, $email);
        if($note == false) {
            return response()->json(["error" => "no user"]);
        }
        $note->content = $note->content."  ";
        $note->save();
        return json_encode(Util::getNotesResponse($note));
    }

    /**
     * Method handles editing of a note with given id. It changes content to POST variable 'content', and
     * tags to POST variable 'tags'.
     * @param Request $request
     * @param $id note id
     * @return edited note, if exists and accessible, error, json-encoded, instead
     */
    public function edit(Request $request, $id) {
        $notesService = new NotesService();
        if($notesService->isNoteAccessible($id) == false) {
            return json_encode(array('error' => 'permission denied(note is not created by or shared with you'));
        }
        if($request->exists('content') == false) {
            return json_encode(array('error' => 'parameter "content" missing'));
        }
        $content = $request->input('content');
        $tags = array();
        if($request->has('tags')) {
            $tags = Util::parseTags($request->input('tags'));
        }
        $note = $notesService->editNote($id, $content, $tags);
        return json_encode(Util::getNotesResponse($note));
    }

    /**
     * Method handles deletion of a note with a given id.
     * @param Request $request
     * @param $id note id
     * @return status about note deletion, if successful, error message instead
     */
    public function delete(Request $request, $id) {
        $notesService = new NotesService();
        if($notesService->isNoteAccessible($id) == false) {
            return json_encode(array('error' => 'permission denied(note is not created by or shared with you'));
        }
        $deleted = $notesService->deleteNote($id);
        if($deleted == false) {
            return json_encode(array('error' => "note with id = $id does not exist"));
        }
        return json_encode(array('status' => "note with id = $id deleted"));
    }
}
