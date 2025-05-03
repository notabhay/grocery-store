<?php
$page_title = $page_title ?? 'Contact Us';
$csrf_token = $csrf_token ?? '';
$flash_message = $flash_message ?? null;
$form_errors = $form_errors ?? [];
$old_input = $old_input ?? [];
$page_title_safe = htmlspecialchars($page_title);
$csrf_token_safe = htmlspecialchars($csrf_token);
?>
<! <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/contact.css">
    <! <main>
        <! <section class="contact-section">
            <div class="container">
                <! <div class="page-title">
                    <h2><?= $page_title_safe
                        ?></h2>
                    <p>We're here to help! Get in touch with us.</p>
            </div>
            <?php
            ?>
            <?php
            ?>
            <?php if (isset($flash_message) && is_array($flash_message) && isset($flash_message['text'], $flash_message['type'])): ?>
            <div class="alert alert-<?= htmlspecialchars($flash_message['type'])
                                        ?>" role="alert">
                <?= htmlspecialchars($flash_message['text'])
                    ?>
            </div>
            <?php endif; ?>
            <! <div class="contact-content">
                <! <address class="contact-info">
                    <h3>Contact Information</h3>
                    <! <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <! <div>
                            <h4>Address</h4>
                            <p>123 Grocery Lane, Foodville</p>
                            <p>ST4 2DE, Staffordshire, UK</p>
                            </div>
                            </div>
                            <! <div class="info-item">
                                <i class="fas fa-phone"></i>
                                <! <div>
                                    <h4>Phone</h4>
                                    <p>(123) 456-7890</p>
                                    <p>Monday to Friday, 9am to 6pm</p>
                                    </div>
                                    </div>
                                    <! <div class="info-item">
                                        <i class="fas fa-envelope"></i>
                                        <! <div>
                                            <h4>Email</h4>
                                            <p>contact@ghibligroceries.com</p>
                                            <p>We'll respond as soon as possible</p>
                                            </div>
                                            </div>
                                            <! <div class="social-media">
                                                <h4>Follow Us</h4>
                                                <div class="social-icons">
                                                    <a href="#" class="social-icon"><i
                                                            class="fab fa-facebook-f"></i></a>
                                                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                                                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                                                    <a href="#" class="social-icon"><i
                                                            class="fab fa-linkedin-in"></i></a>
                                                </div>
                                                </div>
                                                </address>
                                                <! <div class="contact-form-wrapper">
                                                    <h3>Send Us a Message</h3>
                                                    <! <form class="contact-form" method="POST"
                                                        action="<?= BASE_URL ?>contact/submit">
                                                        <! <input type="hidden" name="csrf_token" value="<?= $csrf_token_safe
                                                                                                            ?>">
                                                            <! <div class="form-group">
                                                                <label for="name">Your Name</label>
                                                                <input type="text" id="name" name="name" value="<?= htmlspecialchars($old_input['name'] ?? '')
                                                                                                                ?>"
                                                                    required>
                                                                <?php
                                                                ?>
                                                                <?php if (isset($form_errors['name'])): ?>
                                                                <span
                                                                    class="error-message"><?= htmlspecialchars($form_errors['name']) ?></span>
                                                                <?php endif; ?>
                                                                </div>
                                                                <! <div class="form-group">
                                                                    <label for="email">Your Email</label>
                                                                    <input type="email" id="email" name="email"
                                                                        value="<?= htmlspecialchars($old_input['email'] ?? '')
                                                                                                                        ?>" required>
                                                                    <?php
                                                                    ?>
                                                                    <?php if (isset($form_errors['email'])): ?>
                                                                    <span
                                                                        class="error-message"><?= htmlspecialchars($form_errors['email']) ?></span>
                                                                    <?php endif; ?>
                                                                    </div>
                                                                    <! <div class="form-group">
                                                                        <label for="message">Message</label>
                                                                        <textarea id="message" name="message" rows="5"
                                                                            required><?= htmlspecialchars($old_input['message'] ?? '')
                                                                                                                                ?></textarea>
                                                                        <?php
                                                                        ?>
                                                                        <?php if (isset($form_errors['message'])): ?>
                                                                        <span
                                                                            class="error-message"><?= htmlspecialchars($form_errors['message']) ?></span>
                                                                        <?php endif; ?>
                                                                        </div>
                                                                        <! <button type="submit"
                                                                            class="btn btn-primary">Send
                                                                            Message</button>
                                                                            </form>
                                                                            </div>
                                                                            <! </div>
                                                                                <! <! <section class="location-map">
                                                                                    <h3>Find Us</h3>
                                                                                    <! <div class="map-container">
                                                                                        <! <div class="map-placeholder">
                                                                                            <i class="fas fa-map"></i>
                                                                                            <p>Map location would be
                                                                                                displayed here</p>
                                                                                            <?php
                                                                                            ?>
                                                                                            </div>
                                                                                            </div>
                                                                                            </section>
                                                                                            </div>
                                                                                            <! </section>
                                                                                                <! </main>
                                                                                                    <!