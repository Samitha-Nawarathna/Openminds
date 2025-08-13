<?php

class AccountVerificationServices
{
    public function process_forward($verification_for)
    {
        switch ($verification_for) {
            case 'delete_profile':
                header("Location: ".ROOT."confirmdeletion");
                break;
            
            default:
                
                break;
        }
    }
}