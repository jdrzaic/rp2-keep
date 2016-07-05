<?php

namespace App\Http\Controllers;

use App\NotesService;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Util;
use Illuminate\Support\Facades\DB;

/**
 * Class NotesController
 * @package App\Http\Controllers
 */
class NotesController extends Controller
{
    /**
     * NotesController constructor.
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Method returns all notes owned by or shared with logged user.
     * @return json notes
     */
    public function index() {
        $notesService = new NotesService();
        $notes = $notesService->getAllNotes();
        $notesResponseArray = array();
        foreach ($notes as $note) {
            $notesResponseArray[] = Util::getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    /**
     * Method searches through notes owned by logged user.
     * @param Request $request
     * @return notes that match the given POST variable 'query'(by content or one of tags)
     */
    public function searchMyNotes(Request $request) {
        $notesService = new NotesService();
        if($request->has('query') == false) {
            $matchedNotes = $notesService->getNotesForQuery("", "my");
        } else {
            $matchedNotes = $notesService->getNotesForQuery($request->input('query'), "my");
        }
        $notesResponseArray = array();
        foreach ($matchedNotes as $note) {
            $notesResponseArray[] = Util::getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    /**
     * Method search through notes shared with logged user
     * @param Request $request
     * @return mixed
     */
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
            $notesResponseArray[] = Util::getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }

    /**
     * Method checks if there are changes related to notes shared with a logged user
     * @param Request $request
     * @return mixed
     */
    public function reportShare(Request $request) {
        $notesService = new NotesService();
        if($request->has('last_access_time') == false) {
            return json_encode(array("error" => 'parameter "last_access_time" missing'));
        }
        $lastAccess = $request->input("last_access_time");
        $newNotes = $notesService->getNewOtherNotes($lastAccess);
        $changedNotes = $notesService->getUpdatedOtherNotes($lastAccess);
        if(count($newNotes) + count($changedNotes) > 0) {
            return response()->json(["last_access_time" => date("Y-m-d H:i:s")]);
        }
        return response()->json(["status" => "no change"]);
    }

    /**
     * Method searches through all notes created by or shared with logged user
     * @param Request $request
     * @return json
     */
    public function search(Request $request) {
        $notesService = new NotesService();
        if($request->has('query') == false) {
            return $this->index();
        }
        $matchedNotes = $notesService->getNotesForQuery($request->input('query'));
        $notesResponseArray = array();
        foreach ($matchedNotes as $note) {
            $notesResponseArray[] = Util::getNotesResponse($note);
        }
        return json_encode(array('notes' => $notesResponseArray));
    }
}
