<?php

namespace App\Http\Controllers;

use App\NotesService;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Util;
use Illuminate\Support\Facades\DB;

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
            $notesResponseArray[] = $this->getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    public function searchMyNotes(Request $request) {
        $notesService = new NotesService();
        if($request->has('query') == false) {
            $matchedNotes = $notesService->getNotesForQuery("", "my");
        } else {
            $matchedNotes = $notesService->getNotesForQuery($request->input('query'), "my");
        }
        $notesResponseArray = array();
        foreach ($matchedNotes as $note) {
            $notesResponseArray[] = $this->getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    public function searchOtherNotes(Request $request) {
        $notesService = new NotesService();
        if($request->has('query') == false) {
            $matchedNotes = $notesService->getNotesForQuery("", "other");
        } else {
            $matchedNotes = $notesService->getNotesForQuery($request->input('query'), "other");
        }
        $matchedNotes = $notesService->getNotesForQuery($request->input('query'), "other");
        $notesResponseArray = array();
        foreach ($matchedNotes as $note) {
            $notesResponseArray[] = $this->getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    public function reportShare(Request $request) {
        if($request->has('last_access_time') == false) {
            return json_encode(array("error" => 'parameter "last_access_time" missing'));
        }
        $lastAccess = $request->input('last_access_time');
        while(true) {
            $lastModifiedDate = DB::table('notes')->max('updated_at');
            if($lastModifiedDate == null) {
                usleep(5000000);
                continue;
            }
            $lastModified = strtotime($lastModifiedDate);
            if($lastModified > $lastAccess) {
                $addedNotes = DB::table('notes')->where('updated_at', '>', $lastAccess)->get();
                return $addedNotes;
            }
            sleep(5000000);
        }
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
            $notesResponseArray[] = $this->getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    public function delete(Request $request, $id) {
        $notesService = new NotesService();
        $deleted = $notesService->deleteNote($id);
        if($deleted == false) {
            return json_encode(array("status" => "note with id = $id does not exist"));
        }
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
            'owner' => $note->owner,
            'tags' => array_values(array_unique($noteTagsNames)),
            'users' => array_values($noteUsersInfo)
        );
        return $response;
    }
}
