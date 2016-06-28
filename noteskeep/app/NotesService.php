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
            return $note;
        }
        $attached = DB::table('note_user')->where('note_id', $id)->where('user_id', $user->id)->first();
        if($attached != null) {
            return $note;
        }
        $note->user()->attach($user->id);
        $note->save();
        return $note;
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

    public function getNotesForQuery($query) {
        $notes = Auth::user()->note;
        $notesContentMatch = $this->getNotesByContent($query, $notes);
        $notesTagsMatch = $this->getNotesByTag(Util::parseTags($query), $notes);
        return array_unique(array_merge($notesContentMatch, $notesTagsMatch));
    }

    private function getNotesByContent($content, $notes) {
        $matchingNotes = array();
        foreach ($notes as $note) {
            $pos = strpos($note->content, $content);
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
                    $pos = strpos($noteTag->name, $tag);
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
}