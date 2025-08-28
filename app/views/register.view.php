
<?php
    //guard from unnessesary accesses
    if (isset($_SESSION['user_id']))
    {
        header('Location: '.ROOT.'dashboard');
        exit;
    }

    //setting page variables
    $title = 'Register: Openminds';
    $filename = 'register';
    $no_navbar = true;

    //put header
    include_once('../app/views/partials/header.view.php');

?>

    <div class="body-container">
        <div class="form-container container">
            <h1 class="heading">Register</h1>

        <form action="<?=ROOT?>register/register" method="post">
            <div class="field">
                <div class="input-group">
                <input type="email" placeholder="Email" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                </div>
                <div class="error">email already exists.</div>
            </div>
            <div class="field">
                <div class="input-group">
                <input type="text" placeholder="Username" name="username" value="<?= htmlspecialchars($data['username'] ?? '') ?>">
                </div>
            <div class="error">usename already taken.</div>
            </div>   
            <div class="field">                         
                <div class="input-group">
                <input type="password" placeholder="Password" name="password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
                </div>
            <div class="error">not strong enough.</div>
            </div>    
            <div class="field">                          
                <div class="input-group">
                <input type="password" placeholder="Confirm Password" name="confirm_password" value="<?= htmlspecialchars($data['password'] ?? '') ?>">
                </div>
            <div class="error">does not match with above.</div>
            </div>    
            <div class="introduction">Password must be 8+ chars, with upper, lower, number, special char.</div>
            <input type="submit" class="button" value="Register">
        </form>
        <a href="<?=ROOT?>login" class="link">Already have an account?</a>
        </div>

    </div>

<?php 
    include_once('../app/views/partials/footer.view.php');
?>