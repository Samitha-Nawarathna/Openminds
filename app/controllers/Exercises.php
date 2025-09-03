<?php

class Exercises extends Controller {
	public function index() {
	// In future, pass exercises data to view if needed
	$this->view('exercises/exercises');
	}

	public function create() {
	$this->view('exercises/create');
	
	}

}
