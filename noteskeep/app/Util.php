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
     * parsing comma separated values and returning as an array
     */
    public static function parseTags($tags) {
        return preg_split('/\s+/', $tags);
    }

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