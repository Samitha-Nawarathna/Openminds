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
//api endpoints for pinning and unpinning notes.
App::post('notes/api/pin/{id}', 'Notes@api_pin_note');
App::post('notes/api/unpin/{id}', 'Notes@api_unpin_note');
//api endpoint for generic note filter
App::get('notes/api/filter', 'Notes@api_filter');
//api endpoint for store note_refered event
App::post('notes/api/refer/{note_id}', 'Notes@api_store_refer_event');
//api endpoint for load more notes.
App::get('notes/api/load_more', 'Notes@api_load_more');


//topic module
App::get('topics/create', 'TopicController@create');
App::post('topics/create', 'TopicController@create');
//api endpoints for pinning and unpinning topics.
App::post('topics/api/pin/{id}', 'TopicController@api_pin_topic');
App::post('topics/api/unpin/{id}', 'TopicController@api_unpin_topic');


//topic module api
App::post('topics/api/create', 'TopicController@api_create');
App::post('topics/api/filter', 'TopicController@api_filter');
App::get('topics/api/is_name_available/{name}', 'TopicController@api_is_name_available');
//api endpoint for generic topic filter
//Note: api_filter is already defined above, assuming this is redundant or refers to the POST filter
//App::get('topics/api/filter', 'TopicController@api_filter'); 
//api endpoint for load more topics.
App::get('topics/api/load_more', 'TopicController@api_load_more');
// App::post('topic/api_is_name_available', 'TopicController@api_is_name_available');
App::post('topics/api/search_notes', 'TopicController@api_search_notes');
App::post('topics/api/move_notes_to_topic', 'TopicController@api_move_notes_to_topic');


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


// --- EXPERT REVIEW API ENDPOINTS (NEW) ---
// Get pending exercises for expert review
App::get('exercises/api/pending', 'Exercises@api_get_pending_exercises');
// Load exercise data for review (with questions and options)
App::get('exercises/api/load_review_data/{exercise_id}', 'Exercises@api_load_review_data');
// Approve a pending exercise
App::post('exercises/api/approve_exercise', 'Exercises@api_approve_exercise');
// Reject/send feedback on a pending exercise
App::post('exercises/api/reject_exercise', 'Exercises@api_reject_exercise');


// ----------------------------------------------------------------------
// --- NEW EXERCISES API ENDPOINTS ---
// ----------------------------------------------------------------------

// Browsing and Filtering (R6)
App::get('exercises/api/published', 'Exercises@api_get_published');

// API: Create Exercise (NEW)
App::post('api/exercises/create', 'Exercises@api_create');

// Attempting and Results (R7, R9)
App::post('exercises/api/attempt/{exercise_id}', 'Exercises@api_submit_attempt');
App::get('exercises/api/history', 'Exercises@api_get_attempt_history');
App::get('exercises/api/history/{attempt_id}', 'Exercises@api_get_attempt_details');

// Review/Expert Endpoints (R5)
App::get('exercises/api/pending_review', 'Exercises@api_get_pending_review');

// Interaction (Voting) (R8)
App::post('exercises/api/vote/{exercise_id}', 'Exercises@api_submit_vote');
App::get('exercises/api/vote/{exercise_id}', 'Exercises@api_get_vote_status');
App::get('exercises/api/load_attempt_data/{exercise_id}', 'Exercises@api_load_attempt_data');
//api endpoint for load more exercises.
App::get('exercises/api/load_more', 'Exercises@api_load_more');

// ----------------------------------------------------------------------
// --- END OF EXERCISES API ENDPOINTS ---
// ----------------------------------------------------------------------

App::get('analysis/api/dashboard_data', 'Analysis@api_dashboard_data');
App::get('analysis/api/influence_data', 'Analysis@api_influence_data');
App::get('analysis/api/reflection_data', 'Analysis@api_reflection_data');
App::get('analysis/api/systemview_data', 'Analysis@api_systemview_data'); 

//api endpoint for load notifications.
App::get('notifications/api/load_more', 'Notifications@api_load_more');

//api endpoint for post a annoucement by admin.
App::post('announcements/api/admin/create', 'Announcements@api_admin_create');
//api endpoint for edit a annoucement by admin.
App::post('announcements/api/admin/edit/{id}', 'Announcements@api_admin_edit');

App::get('announcements/view/{id}', 'Announcements@show');
//api endpoint for delete a annoucement by admin.
App::post('announcements/api/admin/delete/{id}', 'Announcements@api_admin_delete');
//api endpoint for load annoucements for admin.
App::get('announcements/api/admin/load_all', 'Announcements@api_admin_load_all');
//api endpoint for load annoucements for user.
App::get('announcements/api/load_latest', 'Announcements@api_user_load_latest');

App::post('announcements/api/hide/{id}', 'Announcements@api_admin_hide');

App::post('announcements/api/unhide/{id}', 'Announcements@api_admin_unhide');

App::get('api/dashboard/user-summary', 'Dashboard@getUserSummary');
App::get('api/dashboard/impact-metrics', 'Dashboard@getImpactMetrics');
App::get('api/dashboard/community-banner', 'Dashboard@getCommunityBanner');

// --- PAGINATED WIDGETS ---
App::get('api/dashboard/notifications', 'Dashboard@getNotifications');
App::get('api/dashboard/announcements', 'Dashboard@getAnnouncements');

// --- PAGINATED TAB CONTENT ---
// Note: Each method must handle limit/offset query parameters
App::get('api/dashboard/notes/pinned', 'Dashboard@getPinnedNotes'); 
App::get('api/dashboard/questions/asked', 'Dashboard@getAskedQuestions');
App::get('api/dashboard/exercises/created', 'Dashboard@getCreatedExercises');
App::get('api/dashboard/exercises/answered', 'Dashboard@getAnsweredExercises');
App::get('api/dashboard/exercises/attempt', 'Dashboard@getAttemptExercises'); // Mapping to 'Published' list
App::get('api/dashboard/exercises/pending', 'Dashboard@getPendingReviewRequests');


//expert request api
App::get('expertrequest/api/search_subjects', 'Expertrequest@api_search_subjects');

//profile browser module
App::get('profilebrowser', 'Profilebrowser@index');
App::post('profilebrowser/api/search_and_filter', 'Profilebrowser@api_search_and_filter');