# rp2keep

laravel 5.2: https://laravel.com/docs/5.2

routes:
* __/home__ - home page
* __/redirect/facebook__ - facebook login (GET)
* __/redirect/google__ - google login (GET)
    * after authentication, redirecting to __/home__

* __/note/create__ - new empty note created (GET)
* __/note/{id}/edit__ - edit note with given id
    * POST parameters: 
        * content - note content
        * tags - note tags; whitespace separated string (e.g. "tag1 tag2 tag3")
* __/note/{id}/share__ - share note with given id
    * POST parameters: 
        * email - mail of the user you're sharing your note with
* __/note/{id}/delete__ - delete note with given id

* __/notes__ - retrieve all notes for the user that's logged in(his or the ones shared with him) (GET)
* __/notes/my__ - search for notes logged user created (GET)
    * query - content to search for (exact match or by tags) (e.g. __/notes/my?query=ana banana__ will match e.g. note="ja sam ana", note="ana banana", note="banana")
* __/notes/other__ - search for notes shared with logged user (GET) 
    * query - content to search for (exact match or by tags) (e.g. __/notes/other?query=ana banana__ will match e.g. note="ja sam ana", note="ana banana", note="banana")
* __/notes/search__ - search for notes with given query (GET)
    * GET parameters:
        * query - content to search for (exact match or by tags) (e.g. __/notes/search?query=ana banana__ will match e.g. note="ja sam ana", note="ana banana", note="banana")
        * __/notes/search__ returns the same result as __/notes__
* __/users__ returns all existing users
* __/users?q=somequery__ returns all users whose emails match __somequery__ (e.g. __/users?q=an__ returns lana@mail.com, banana@mail.com)
* __/user__ returns user that's logged in
