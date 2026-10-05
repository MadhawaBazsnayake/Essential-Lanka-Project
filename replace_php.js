const fs = require('fs');
const files = ['about.html', 'contact.html', 'faq.html', 'how-it-works.html', 'services.html', 'privacy.html', 'terms.html', 'tools-feed.html'];

const translations = {
    // Header & Nav
    'nav_home': 'Home',
    'nav_about': 'About Us',
    'nav_how_it_works': 'How It Works',
    'nav_jobs': 'Jobs',
    'nav_tools': 'Tool Rental',
    'nav_contact': 'Contact',
    'nav_services': 'Services',
    'nav_safety': 'Safety',
    'nav_login': 'Log In',
    'nav_dashboard': 'Dashboard',
    'nav_tool_portal': 'Tool Portal',
    'nav_logout': 'Logout',
    'nav_privacy': 'Privacy Policy',
    'nav_terms': 'Terms of Service',

    // Footer
    'footer_desc': 'Empowering daily wage earners and connecting them with households across Sri Lanka.',
    'footer_quick_links': 'Quick Links',
    'footer_link_find': 'Find a Worker',
    'footer_link_become': 'Become a Worker',
    'footer_link_ussd': 'USSD Service',
    'footer_support': 'Support',
    'footer_faq': 'FAQ',
    'footer_contact': 'Contact Us',
    'footer_terms': 'Terms of Service',
    'footer_rights': 'Essential Lanka. All rights reserved.'
};

const headerHtml = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Essential Lanka</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="assets/css/style.min.css">
    <link rel="stylesheet" href="assets/css/pill-alerts.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <header id="site-header"></header>
`;

const footerHtml = `
    <footer id="site-footer"></footer>
    <script type="module">
        import { loadLayout } from './js/layout.js';
        document.addEventListener('DOMContentLoaded', () => {
            loadLayout();
        });
    </script>
</body>
</html>
`;

files.forEach(f => {
    if(fs.existsSync(f)) {
        let content = fs.readFileSync(f, 'utf8');
        
        // Replace Header
        content = content.replace(/<\?php\s+include\s+'includes\/header\.php';\s*\?>/g, headerHtml);
        
        // Replace Footer
        content = content.replace(/<\?php\s+include\s+'includes\/footer\.php';\s*\?>/g, footerHtml);
        
        // Replace translations
        content = content.replace(/<\?php\s+echo\s+t\('([^']+)'\);\s*\?>/g, (match, p1) => {
            return translations[p1] || p1;
        });

        // Strip remaining <?php blocks
        content = content.replace(/<\?php[\s\S]*?\?>/g, '');

        fs.writeFileSync(f, content);
    }
});
