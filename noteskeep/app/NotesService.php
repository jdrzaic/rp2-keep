<?php
/**
 * Created by PhpStorm.
 * User: jelenadrzaic
 * Date: 28/06/16
 * Time: 10:27
 */

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Service handling communication between controllers and model,
 * connected to Note object
 * Class NotesService
 * @package App
 */
class NotesService {

    /**
     * Method returns new note, with content="", tags = (), and owner=logged user
     * @return Note
     */
    public function createNote() {
        $note = new Note;
        $note->content = '';
        $note->owner = Auth::user()->email;
        $note->save();
        Auth::user()->note()->attach($note->id);
        return $note;
    }

    /**
     * Method deletes note with given id,and its relations from database
     * @param $id note id
     * @return bool true if delete successful, false otherwise
     */
    public function deleteNote($id) {
        $note = Note::find($id);
        if($note == null) {
            return false;
        }
        $note->user()->detach();
        $note->tag()->detach();
        DB::table('notes')->where('id', $id)->delete();
        return true;
    }

    /**
     * Method handles editing of a note with given id
     * @param $id Note id
     * @param $content new content of a note
     * @param $tags new tags of a note
     * @return mixed edited note
     */
    public function editNote($id, $content, $tags) {
        $note = Note::find($id);
        $note->content = $content;
        $note->save();
        $note->tag()->detach();
        if(sizeof($tags) == 0) {
            return $note;
        }
        foreach ($tags as $tagRow) {
            $tag = DB::table('tags')->where('name', $tagRow)->first();
            if($tag == null) {
                $tag = new Tag;
                $tag->name = $tagRow;
                $tag->save();
            }
            $attached = DB::table('note_tag')->where('note_id', $id)->where('tag_id', $tag->id)->first();
            if($attached != null) {
                continue;
            }
            $note->tag()->attach($tag->id);
        }
        return $note;
    }

    /**
     * Method handles sharing of a note
     * @param $id note id
     * @param $email email of a user to share a note with
     * @return note, if sharing successful, false otherwise
     */
    public function shareNote($id, $email) {
        $note = Note::find($id);
        $user = DB::table('users')->where('email', $email)->first();
        if($user == null) {
            return false;
        }
        $attached = DB::table('note_user')->where('note_id', $id)->where('user_id', $user->id)->first();
        if($attached != null) {
            return $note;
        }
        $note->user()->attach($user->id);
        $note->save();
        return $note;
    }

    /**
     * Method returns note with given id
     * @param $id Note id
     * @return note
     */
    public function getNote($id) {
        return Note::find($id);
    }

    /**
     * Method returns all notes for given user(created and shared with)
     * @return mixed
     */
    public function getAllNotes() {
        $notes = Auth::user()->note;
        return $notes;
    }

    /**
     * Method returns tags, as array, of a given note
     * @param $note note te search tags for
     * @return note tags
     */
    public function getTagsForNote($note) {
        $noteTags = $note->tag;
        return $noteTags;
    }

    /**
     * Method returns users for a given note
     * @param $note note
     * @return users of a note(creator + shares)
     */
    public function getUsersForNote($note) {
        $users = $note->user;
        return $users;
    }

    /**
     * Method returns note for given query(by content, or matching at least one of the tags)
     * @param $query string
     * @param string $part other if searching through shared notes, my if searching through created notes,
     * "" otherwise
     * @return notes that match query
     */
    public function getNotesForQuery($query, $part = "") {
        if($part == "my" ) {
            $notes = $this->getMyNotes();
        } elseif ($part == "other") {
            $notes = $this->getOtherNotes();
        } else {
            $notes = Auth::user()->note;
        }
        if($query == "") {
            return $notes;
        }
        $notesContentMatch = $this->getNotesByContent($query, $notes);
        $notesTagsMatch = $this->getNotesByTag(Util::parseTags($query), $notes);
        return array_unique(array_merge($notesContentMatch, $notesTagsMatch));
    }

    /**
     * Method gets notes whose content matches with given query
     * @param $content query
     * @param $notes notes to search through
     * @return array matching notes
     */
    private function getNotesByContent($content, $notes) {
        $matchingNotes = array();
        foreach ($notes as $note) {
            $pos = strpos(strtolower($note->content), strtolower($content));
            if($pos === false) {
                continue;
            }
            $matchingNotes[] = $note;
        }
        return $matchingNotes;
    }

    /**
     * Metod gets notes thaat match query my tag
     * @param $tags
     * @param $notes
     * @return array
     */
    private function getNotesByTag($tags, $notes) {
        $matchingNotes = array();
        foreach ($tags as $tag) {
            foreach ($notes as $note) {
                $noteTags = $this->getTagsForNote($note);
                foreach ($noteTags as $noteTag) {
                    $pos = false;
                    if($tag != "") {
                        $pos = strpos(strtolower($noteTag->name), strtolower($tag));
                    }
                    if($pos === false) {
                        continue;
                    }
                    $matchingNotes[] = $note;
                    break;
                }
            }
        }
        return $matchingNotes;
    }

    /**
     * Method gets all my notes
     * @return array notes from logged user
     */
    public function getMyNotes() {
        $notes = Auth::user()->note;
        $myNotes = array();
        foreach ($notes as $note) {
            if($note->owner == Auth::user()->email) {
                $myNotes[] = $note;
            }
        }
        return $myNotes;
    }

    /**
     * Get notes shared with user
     * @return notes shared with logged user
     */
    public function getOtherNotes() {
        $notes = Auth::user()->note;
        $otherNotes = array();
        foreach ($notes as $note) {
            if($note->owner != Auth::user()->email) {
                $otherNotes[] = $note;
            }
        }
        return $otherNotes;
    }

    /**
     * Method checks if user can access a note with given id
     * @param $id id of a note
     * @return bool true if accessible, false otherwise
     */
    public function isNoteAccessible($id) {
        $accessible = DB::table('note_user')->where('note_id', $id)->where('user_id', Auth::user()->id)->first();
        if($accessible == null) {
            return false;
        }
        return true;
    }

    /**
     * Method returns new notes, created and shared with user after last access to the server
     * @param $lastAccess last access to the server
     * @return array new notes
     */
    public function getNewOtherNotes($lastAccess) {
        $notes = Auth::user()->note;
        $newNotes = array();
        foreach ($notes as $note) {
            if($note->owner != Auth::user()->email && $note->updated_at > $lastAccess) {
                $newNotes[] = $note;
            }
        }
        return $newNotes;
    }

    /**
     * Notes shared with user and updated after last access
     * @param $lastAccess last access to server
     * @return array updated notes
     */
    public function getUpdatedOtherNotes($lastAccess) {
        $notes = Auth::user()->note;
        $newNotes = array();
        foreach ($notes as $note) {
            if(($note->owner != Auth::user()->email && $note->updated_at > $lastAccess) ||
                ($note->owner == Auth::user()->email && $note->updated_at > $lastAccess && $note->created_at < $lastAccess)) {
                $newNotes[] = $note;
            }
        }
        return $newNotes;
    }
}