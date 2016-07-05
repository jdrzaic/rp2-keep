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


class NotesService {

    public function createNote() {
        $note = new Note;
        $note->content = '';
        $note->owner = Auth::user()->email;
        $note->save();
        Auth::user()->note()->attach($note->id);
        return $note;
    }

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

    public function getNote($id) {
        return Note::find($id);
    }

    public function getAllNotes() {
        $notes = Auth::user()->note;
        return $notes;
    }

    public function getTagsForNote($note) {
        $noteTags = $note->tag;
        return $noteTags;
    }

    public function getUsersForNote($note) {
        $users = $note->user;
        return $users;
    }

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

    public function isNoteAccessible($id) {
        $accessible = DB::table('note_user')->where('note_id', $id)->where('user_id', Auth::user()->id)->first();
        if($accessible == null) {
            return false;
        }
        return true;
    }

    public function getNewOtherNotes($lastAccess) {
        $notes = Auth::user()->note;
        $newNotes = array();
        foreach ($notes as $note) {
            if($note->owner != Auth::user()->email && $note->created_at > $lastAccess) {
                $newNotes[] = $note;
            }
        }
        return $newNotes;
    }

    public function getUpdatedOtherNotes($lastAccess) {
        $notes = Auth::user()->note;
        $newNotes = array();
        foreach ($notes as $note) {
            if($note->owner != Auth::user()->email && $note->updated_at > $lastAccess) {
                $newNotes[] = $note;
            }
        }
        return $newNotes;
    }
}