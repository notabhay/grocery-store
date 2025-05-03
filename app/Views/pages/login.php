<?php




$page_title = $page_title ?? 'Login - GhibliGroceries';
$meta_description = $meta_description ?? 'Login to GhibliGroceries - Access your account to place orders.';
$meta_keywords = $meta_keywords ?? 'login, grocery, online shopping, account access';
$additional_css_files = $additional_css_files ?? ['assets/css/login.css']; 
$email = $email ?? ''; 
$login_error = $login_error ?? ''; 
$captcha_error = $captcha_error ?? ''; 
$csrf_token = $csrf_token ?? ''; 
?>
<!
<main>
    <!
    <section class="login-section">
        <div class="container">
            <!
            <div class="page-title">
                <h2>Login to Your Account</h2>
                <p>Enter your credentials to access your account</p>
            </div>

            <?php 
            ?>
            <?php if (!empty($login_error)): ?>
                <div class="alert alert-error">
                    <p><?php echo htmlspecialchars($login_error); ?></p>
                </div>
            <?php endif; ?>

            <!
            <div class="login-form-container">
                <!
                <form action="<?= BASE_URL ?>login" method="post" class="login-form">
                    <!
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <!
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); 
                                                                            ?>" required>
                    </div>

                    <!
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input type="password" name="password" id="password" required>
                            <!
                            <span class="toggle-password">
                                <i class="fas fa-eye"></i> <!
                            </span>
                        </div>
                    </div>

                    <!
                    <div class="form-group captcha-group">
                        <label for="captcha">Verification Code</label>
                        <div class="captcha-container">
                            <!
                            <div class="captcha-image">
                                <img src="<?= BASE_URL ?>captcha" alt="CAPTCHA Image" id="captcha-img">
                                <!
                                <button type="button" class="refresh-captcha">
                                    <i class="fas fa-sync-alt"></i> <!
                                </button>
                            </div>
                            <!
                            <input type="text" name="captcha" id="captcha" required autocomplete="off">
                        </div>
                        <?php 
                        ?>
                        <?php if (!empty($captcha_error)): ?>
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($captcha_error); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!
                    <div class="form-group remember-me">
                        <label>
                            <input type="checkbox" name="remember_me"> Remember me
                        </label>
                        <a href="<?= BASE_URL ?>forgot-password" class="forgot-password">Forgot Password?</a>
                    </div>

                    <!
                    <div class="form-group">
                        <button type="submit" name="login_submit" class="btn btn-primary">Login</button>
                    </div>
                </form>

                <!
                <aside class="register-link">
                    <p>Don't have an account? <a href="<?= BASE_URL ?>register">Register here</a></p>
                </aside>
            </div> <!
        </div> <!
    </section> <!
</main> <!

<!
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        
        const togglePassword = document.querySelector('.toggle-password');
        if (togglePassword) { 
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                const icon = this.querySelector('i'); 
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text'; 
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash'); 
                } else {
                    passwordInput.type = 'password'; 
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye'); 
                }
            });
        }

        
        const refreshCaptcha = document.querySelector('.refresh-captcha');
        const captchaImage = document.getElementById('captcha-img');
        if (refreshCaptcha && captchaImage) { 
            refreshCaptcha.addEventListener('click', function(e) {
                e.preventDefault(); 
                
                captchaImage.src = '<?= BASE_URL ?>captcha?' + new Date().getTime();
            });
        }
    });
</script>