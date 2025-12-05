<?php 
// views/public/login.php
include '../templates/header.php'; 

// Check for redirection messages (e.g., from successful registration)
$message = '';
$message_type = '';

if (isset($_GET['registration']) && $_GET['registration'] === 'success') {
    $message = "Registration successful! You may now log in to access your dashboard.";
    $message_type = 'success';
} elseif (isset($_GET['error']) && $_GET['error'] === 'auth_failed') {
    $message = "Login failed. Please check your email and password.";
    $message_type = 'danger';
}

?>

<style>
/* Local style adjustments for the login form */
#login-section {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--light-bg);
}

.login-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    padding: 40px;
    background-color: white;
    max-width: 450px;
    width: 100%;
    animation: fadeInSlideDown 0.8s ease-out; /* Subtle animation for entrance */
}

.login-title {
    color: var(--primary-red);
    font-weight: 700;
    margin-bottom: 25px;
}

/* Custom button theme for login */
.btn-login {
    background-color: var(--primary-blue);
    border-color: var(--primary-blue);
    transition: all 0.3s ease;
}
.btn-login:hover {
    background-color: #1562B5;
    border-color: #1562B5;
}

@keyframes fadeInSlideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<section id="login-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-card">

                    <div class="text-center">
                        <i class="fas fa-user-lock fa-3x mb-3" style="color: var(--primary-red);"></i>
                        <h2 class="login-title">Secure Login</h2>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> text-center mb-4" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form action="../../handlers/auth_process.php" method="POST">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter your registered email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                        </div>
                        
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember Me</label>
                        </div>

                        <button type="submit" class="btn btn-login btn-block btn-lg mt-4">
                            <i class="fas fa-sign-in-alt mr-2"></i> Log In to JeevanSetu
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <small>New User? <a href="register.php" style="color: var(--primary-red); font-weight: 600;">Register Here</a></small>
                        <br>
                        <small><a href="#" class="text-muted">Forgot Password?</a></small>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php 
include '../templates/footer.php'; 
?>