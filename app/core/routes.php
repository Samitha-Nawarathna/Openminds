<?php


//note module
App::post('notes/create', 'Notes@create');
App::get('notes/create', 'Notes@create');

App::post('notes/edit/{id}', 'Notes@edit'); //check is this ok?
App::get('notes/edit/{id}', 'Notes@edit');

App::get('notes/list/{id}', 'Notes@view_notes');
App::get('notes/view/{note_id}', 'Notes@show');

App::post('notes/delete/{id}', 'Notes@delete');


//note module api
App::get('notes/api/search_by_tags', 'Notes@api_search_notes_by_tags');
App::get('notes/api/get_by_id/{note_id}', 'Notes@api_get_note_by_id');
App::get('notes/api/share', 'Notes@api_share');

//topic module
App::get('topics/create', 'TopicController@create');
App::post('topics/create', 'TopicController@create');


//topic module api
App::post('topics/api/create', 'TopicController@api_create');
App::post('topics/api/filter', 'TopicController@api_filter');
App::post('topics/api/is_name_available', 'TopicController@api_is_name_available');

//tag module api
App::post('tags/api/search_by_name', 'TagController@api_search_tags_by_name');


//user module api
App::post('users/api/search_by_name', 'Users@api_search_users_by_name');

