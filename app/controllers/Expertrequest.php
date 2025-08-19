<!--create request logic,
view request logic,
edit request logic,
delete request logic,

-->
<?php

class Expertrequest extends Controller
{
    public function index()
    {
        $this->login_guard();
        $this->view('expertrequests/requestbrowser');
    }


    public function create()
    {
        // Logic to create a new expert request
        // Validate input, save to database, etc.
        // $this->login_guard();

        if ($this->is_post()) {
            echo "create post...";
        }

        if ($this->is_get()) {
            // Render the form for creating a new expert request
            $this->view('expertrequests/create');
        }



    }

    public function show()
    {
        // Logic to view an existing expert request by ID
        // Fetch from database and return the data
        $this->login_guard();

        $subject = "Physics";
        $description = "Some say the universe is governed by cold equations—I say it's also sprinkled with a little whimsy. I chase particles, poke at paradoxes, and whisper sweet nothings to Schrödinger’s cat (don’t worry, it’s probably fine). Currently mastering the math that makes stars dance and time bend. If it's weird, wavy, or wonderfully confusing, I’m into it.

Interests:
Helping others ✨
Relativity 🌀
Thought Experiments 🧠
Mathematical Proofs 📐
Coffee-fueled earlymorning derivations ☕🧾";

        $filename = "document.pdf";
        $this->view('expertrequests/view', ['subject' => $subject, 'description' => $description, 'filename' => $filename]);
    }

    public function edit()
    {
        // Logic to edit an existing expert request by ID
        // Validate input, update in database, etc.
        $this->login_guard();
        $this->view('expertrequests/edit');        
    }

    public function delete()
    {
        // Logic to delete an existing expert request by ID
        // Remove from database and handle any necessary cleanup
        $this->login_guard();
        $this->post_guard();

        $this->view('expertrequests/delete'); 
    }
}