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
    static public function parseTags($tags) {
        return preg_split('/\s+/', $tags);
    }
}