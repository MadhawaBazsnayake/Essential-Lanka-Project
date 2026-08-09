<footer class="site-footer fade-up">
    <div class="footer-grid">
        <div class="footer-brand">
            <h3><i class="ph-fill ph-infinity"></i> ESSENTIAL LANKA</h3>
            <p style="color: var(--text-muted); margin-top: 10px;"><?php echo t('footer_desc'); ?></p>
        </div>
        <div class="footer-links">
            <h4><?php echo t('footer_quick_links'); ?></h4>
            <!-- නිවැරදි කළ ලිංක් -->
            <a href="services.php"><?php echo t('footer_link_find'); ?></a>
            <a href="register.php?role=worker"><?php echo t('footer_link_become'); ?></a>
            <a href="faq.php#ussd"><?php echo t('footer_link_ussd'); ?></a>
        </div>
        <div class="footer-links">
            <h4><?php echo t('footer_support'); ?></h4>
            <a href="faq.php"><?php echo t('footer_faq'); ?></a>
            <a href="contact.php"><?php echo t('footer_contact'); ?></a>
            <a href="terms.php"><?php echo t('footer_terms'); ?></a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> <?php echo t('footer_rights'); ?></p>
        <div class="social-icons">
            <i class="ph-fill ph-facebook-logo"></i>
            <i class="ph-fill ph-instagram-logo"></i>
            <i class="ph-fill ph-twitter-logo"></i>
        </div>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>