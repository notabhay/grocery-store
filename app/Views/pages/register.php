<?php
use App\Core\Session;
use App\Core\Registry;
$page_title = $page_title ?? 'Register - GhibliGroceries';
$meta_description = $meta_description ?? 'Create an account with GhibliGroceries to start ordering fresh groceries online.';
$meta_keywords = $meta_keywords ?? 'register, grocery, create account, sign up';
$additional_css_files = $additional_css_files ?? ['assets/css/register.css'];
$csrf_token = $csrf_token ?? ''; 
$registration_error = $registration_error ?? ''; 
$registration_success = $registration_success ?? false; 
$input_data = Registry::get('session')->getFlash('input_data') ?? [];
$input_name = $input_data['name'] ?? '';
$input_phone = $input_data['phone'] ?? '';
$input_email = $input_data['email'] ?? '';
?>
<!
<main>
    <!
    <section class="register-section">
        <!
        <div class="container">
            <!
            <div class="page-title">
                <h2>Create an Account</h2>
                <p>Register to start shopping for fresh groceries</p>
            </div>
            <!
            <?php if ($registration_success): 
            ?>
                <div class="alert alert-success">
                    <p>Registration successful! You can now <a href="<?= BASE_URL ?>login">login</a> to your account.</p>
                </div>
            <?php elseif (!empty($registration_error)): 
            ?>
                <div class="alert alert-error">
                    <!
                    <p><?php echo nl2br(htmlspecialchars($registration_error)); ?></p>
                </div>
            <?php endif; 
            ?>
            <!
            <div id="register-form-container">
                <!
                <p>Loading registration form...</p>
            </div>
            <!
            <noscript>
                <!
                <p style="color: red; text-align: center; margin-bottom: 1em;">JavaScript is required for the
                    interactive registration form. You can use this basic form instead.</p>
                <!
                <div class="register-form-container">
                    <!
                    <form action="<?= BASE_URL ?>register" method="post" class="register-form">
                        <!
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <!
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($input_name); 
                                                                                        ?>">
                        </div>
                        <!
                        <div class="form-group">
                            <label for="phone">Phone Number (10 digits)</label>
                            <input type="tel" name="phone" id="phone" pattern="[0-9]{10}" required <!
                                validation -->
                            value="<?php echo htmlspecialchars($input_phone); 
                                    ?>">
                        </div>
                        <!
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($input_email); 
                                                                                        ?>">
                        </div>
                        <!
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" required>
                            <!
                        </div>
                        <!
                        <div class="form-group">
                            <button type="submit" name="register_submit" class="btn btn-primary">Register</button>
                        </div>
                    </form> <!
                    <!
                    <aside class="login-link">
                        <p>Already have an account? <a href="<?= BASE_URL ?>login">Login here</a></p>
                    </aside>
                </div> <!
            </noscript> <!
        </div> <!
    </section> <!
</main> <!
<!
<!
<script src="https://unpkg.com/react@17/umd/react.development.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js" crossorigin></script>
<!
<script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
<!
<script type="text/babel" src="<?= BASE_URL ?>assets/js/react_components/RegistrationForm.js"></script>
<!
<script type="text/babel">
    const csrfToken = "<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>";
    ReactDOM.render(
        <RegistrationForm csrfToken={csrfToken} />,
        document.getElementById('register-form-container')
    );
</script>