<?php

function show($thing)
{
    echo "<pre>";
    print_r($thing);
    echo "</pre>";
}

function regenerate_session_id()
{
    if (!isset($_SESSION["regenerated_time"])) {
        session_regenerate_id();
        $_SESSION["regenerated_time"] = time();
    }else{
        $regenerate_time_period = 60*30;

        if (time() - $_SESSION["regenerated_time"] > $regenerate_time_period) {
            session_regenerate_id();
            $_SESSION["regenerated_time"] = time();                
        }
    }
}