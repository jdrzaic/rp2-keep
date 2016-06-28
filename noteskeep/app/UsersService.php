<?php
/**
 * Created by PhpStorm.
 * User: jelenadrzaic
 * Date: 28/06/16
 * Time: 17:47
 */

namespace App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersService {

    public function getUsersByQuery($query) {
        $users = DB::table('users')->get();
        if($query == "") {
            return $users;
        }
        $matchingUsers = array();
        foreach ($users as $user) {
            $pos = strpos(strtolower($user->email), strtolower($query));
            if($pos === false) {
                continue;
            }
            $matchingUsers[] = $user;
        }
        return $matchingUsers;
    }
}