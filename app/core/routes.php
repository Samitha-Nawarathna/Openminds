<?php

//note module
App::post('notes/create', 'Notes@create');
App::get('notes/create', 'Notes@create');
App::post('notes/edit/{id}', 'Notes@edit'); 
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


//question module api
App::post('question/api/answer', 'Question@api_create_answer');
App::post('question/api/vote', 'Question@api_vote_question');
App::post('question/api/vote_answer', 'Question@api_vote_answer');
App::post('question/api/edit', 'Question@api_edit_question');
App::post('question/api/edit_answer', 'Question@api_edit_answer');
App::post('question/api/delete', 'Question@api_delete_question');
App::post('question/api/delete_answer', 'Question@api_delete_answer');
App::post('question/api/load_more', 'Question@api_load_more');



// --- NEW EXERCISES UI/VIEW ROUTES (Existing, for context) ---
App::get('exercises', 'Exercises@index');
App::get('exercises/create', 'Exercises@create');
App::post('exercises/create', 'Exercises@create');
App::post('exercises/attempt', 'Exercises@attempt'); // POST submission
App::get('exercises/attempt', 'Exercises@attempt'); // GET view
App::get('exercises/show', 'Exercises@show');
App::get('exercises/edit', 'Exercises@edit');
App::post('exercises/edit', 'Exercises@edit');
App::post('exercises/delete', 'Exercises@delete');
App::get('exercises/hide', 'Exercises@hide');
App::get('exercises/expertreview', 'Exercises@expertreview');
App::get('exercises/viewattempt/{exercise_id}/{attempt_id}', 'Exercises@viewattempt');
App::post('exercises/approve', 'Exercises@approve');
App::post('exercises/reject', 'Exercises@reject');


// ----------------------------------------------------------------------
// --- NEW EXERCISES API ENDPOINTS ---
// ----------------------------------------------------------------------

// Browsing and Filtering (R6)
App::get('exercises/api/published', 'Exercises@api_get_published');

// Attempting and Results (R7, R9)
App::post('exercises/api/attempt/{exercise_id}', 'Exercises@api_submit_attempt');
App::get('exercises/api/history', 'Exercises@api_get_attempt_history');
App::get('exercises/api/history/{attempt_id}', 'Exercises@api_get_attempt_details');

// Review/Expert Endpoints (R5)
App::get('exercises/api/pending_review', 'Exercises@api_get_pending_review');
// Note: You can use the existing approve/reject POSTs for the actual action if needed.

// Interaction (Voting) (R8)
App::post('exercises/api/vote/{exercise_id}', 'Exercises@api_submit_vote');
App::get('exercises/api/vote/{exercise_id}', 'Exercises@api_get_vote_status');
App::get('exercises/api/load_attempt_data/{exercise_id}', 'Exercises@api_load_attempt_data');