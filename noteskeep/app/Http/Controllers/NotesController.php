<?php

namespace App\Http\Controllers;

use App\NotesService;
use Illuminate\Http\Request;
use App\Note;
use App\Http\Requests;
use App\Util;
use App\Tag;

class NotesController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $notesService = new NotesService();
        $notes = $notesService->getAllNotes();
        $notesResponseArray = array();
        foreach ($notes as $note) {
            $notesResponseArray[] = self::getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
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
        if($request->has('email') == false) {
            return json_encode(array('error' => 'parameter "email" missing'));
        }
        $email = $request->input('email');
        $notesService = new NotesService();
        $note = $notesService->shareNote($id, $email);
        return json_encode($this->getNotesResponse($note));
    }

    public function edit(Request $request, $id) {
        if($request->has('content') == false) {
            return json_encode(array('error' => 'parameter "content" missing'));
        }
        $content = $request->input('content');
        $tags = array();
        if($request->has('tags')) {
            $tags = Util::parseTags($request->input('tags'));
        }
        $notesServise = new NotesService();
        $note = $notesServise->editNote($id, $content, $tags);
        return json_encode($this->getNotesResponse($note));
    }

    public function search(Request $request) {
        $notesService = new NotesService();
        if($request->has('query') == false) {
            return $this->index();
        }
        $matchedNotes = $notesService->getNotesForQuery($request->input('query'));
        $notesResponseArray = array();
        foreach ($matchedNotes as $note) {
            $notesResponseArray[] = self::getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    public function delete(Request $request, $id) {
        $notesService = new NotesService();
        $notesService->deleteNote($id);
        return json_encode(array("status" => "note with id = $id deleted"));
    }

    public function getNotesResponse($note) {
        $notesService = new NotesService();
        $noteTags = $notesService->getTagsForNote($note);
        $noteTagsNames = array();
        foreach ($noteTags as $noteTag) {
            $noteTagsNames[] = $noteTag->name;
        }
        $noteUsers = $notesService->getUsersForNote($note);
        $noteUsersInfo = array();
        foreach ($noteUsers as $noteUser) {
            $noteUsersInfo[] = array(
                "email" => $noteUser->email,
                "name" => $noteUser->name
            );
        }
        $response = array(
            'id' => $note->id,
            'content' => $note->content,
            'tags' => array_values(array_unique($noteTagsNames)),
            'users' => array_values($noteUsersInfo)
        );
        return $response;
    }
}
