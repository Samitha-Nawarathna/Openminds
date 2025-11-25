<?php

class ProfileServices
{
    public function delete($user_id)
    {
        $user = new User;
        header("Location: ".ROOT."confirmdeletion");
        $user->delete($user_id);
    }

    public function change_password($user_data)
    {
        $user_model = new User;

        $user_id = $user_data['user_id'];

        $existing_user = $user_model->first(['id' => $user_id]);
        
        if (verify_password($user_data['current_password'], $existing_user->password) === false) {
            return ServiceResult::failure(['Incorrect current password']);
        }

        if (verify_password($user_data['new_password'], $existing_user->password) === false) {
            return ServiceResult::failure(["cannot set current password an new password."]);
        }

        $result = $user_model->update($user_id, ['password'=>password_hash($user_data['new_password'], PASSWORD_DEFAULT)]);

        if ($result === false) {
            return ServiceResult::failure(["Unable to change password."]);
        }

        return ServiceResult::success();
    }
}