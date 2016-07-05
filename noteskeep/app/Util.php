<?php
/**
 * Created by PhpStorm.
 * User: jelenadrzaic
 * Date: 28/06/16
 * Time: 00:40
 */

namespace App;


class Util
{

    /**
     * @param tags string, comma separated
     * parsing whitespace separated values and returning them as an array
     */
    public static function parseTags($tags) {
        return preg_split('/\s+/', $tags);
    }

    /**
     * Creates response array for a given note.
     * Includes note id, content, owner, users and tags
     * @param $note note
     * @return response, as an array
     */
    public static function getNotesResponse($note) {
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