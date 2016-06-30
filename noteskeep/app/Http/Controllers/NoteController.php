<?php

namespace App\Http\Controllers;

use App\NotesService;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Util;

class NoteController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request, $id) {
        $notesServise = new NotesService();
        $note = $notesServise->getNote($id);
        if($note == null) {
            return json_encode(array("error" => "Note with given id not found"));
        }
        return json_encode(Util::getNotesResponse($note));

    }

    public function create() {
        $notesServise = new NotesService();
        $note = $notesServise->createNote();
        return $note->toJson();
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function share(Request $request, $id) {
        $notesService = new NotesService();
        if($notesService->isNoteAccessible($id) == false) {
            return json_encode(array("error" => "permission denied(note is not created by or shared with you"));
        }
        if($request->has('email') == false) {
            return json_encode(array('error' => 'parameter "email" missing'));
        }
        $email = $request->input('email');
        $note = $notesService->shareNote($id, $email);
        return json_encode(Util::getNotesResponse($note));
    }

    public function edit(Request $request, $id) {
        $notesService = new NotesService();
        if($notesService->isNoteAccessible($id) == false) {
            return json_encode(array("error" => "permission denied(note is not created by or shared with you"));
        }
        if($request->has('content') == false) {
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

    public function delete(Request $request, $id) {
        $notesService = new NotesService();
        if($notesService->isNoteAccessible($id) == false) {
            return json_encode(array("error" => "permission denied(note is not created by or shared with you"));
        }
        $deleted = $notesService->deleteNote($id);
        if($deleted == false) {
            return json_encode(array("status" => "note with id = $id does not exist"));
        }
        return json_encode(array("status" => "note with id = $id deleted"));
    }
}
