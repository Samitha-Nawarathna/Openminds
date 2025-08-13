<?php

class ProfileServices
{
    public function delete($user_id)
    {
        $user = new User;
        header("Location: ".ROOT."confirmdeletion");
        $user->delete($user_id);
    }
}