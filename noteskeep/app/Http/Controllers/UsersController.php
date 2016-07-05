<?php

namespace App\Http\Controllers;

use App\UsersService;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

/**
 * Controller returning information on users
 * Class UsersController
 * @package App\Http\Controllers
 */
class UsersController extends Controller
{
    /**
     * Method returns the emails that match given query.
     * Searches through all registered users
     * @param Request $request
     * @return mixed
     */
    public function search(Request $request) {
        $usersService = new UsersService();
        if($request->has('q') == false) {
            $users = $usersService->getUsersByQuery("");
        } else {
            $users = $usersService->getUsersByQuery($request->input('q'));
        }
        $usersInfo = array();
        foreach ($users as $user) {
            $usersInfo[] = array(
                "email" => $user->email,
                "name" => $user->name
            );
        }
        return json_encode(array('users' => array_values($usersInfo)));
    }

    /**
     * Method returns logged user
     * @return mixed
     */
    public function index() {
        $user = Auth::user();
        return json_encode(array("user" => $user));
    }
}
