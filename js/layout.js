import { supabase } from './supabase-client.js';

export async function loadLayout(activeNavId = null) {
    try {
        // Load Header
        const headerRes = await fetch('components/header.html');
        if (headerRes.ok) {
            const headerHtml = await headerRes.text();
            document.getElementById('site-header').innerHTML = headerHtml;
        }

        // Load Footer
        const footerRes = await fetch('components/footer.html');
        if (footerRes.ok) {
            const footerHtml = await footerRes.text();
            document.getElementById('site-footer').innerHTML = footerHtml;
        }

        // Inject AOS (Animate On Scroll) Library Globally
        if (!document.getElementById('aos-css')) {
            const aosCss = document.createElement('link');
            aosCss.id = 'aos-css';
            aosCss.rel = 'stylesheet';
            aosCss.href = 'https://unpkg.com/aos@2.3.1/dist/aos.css';
            document.head.appendChild(aosCss);
        }

        if (!document.getElementById('aos-js')) {
            const aosJs = document.createElement('script');
            aosJs.id = 'aos-js';
            aosJs.src = 'https://unpkg.com/aos@2.3.1/dist/aos.js';
            aosJs.onload = () => {
                AOS.init({
                    duration: 800,
                    easing: 'ease-out-cubic',
                    once: true,
                    offset: 50
                });
            };
            document.body.appendChild(aosJs);
        } else if (typeof AOS !== 'undefined') {
            AOS.refresh();
        }

        // Mobile Menu Logic
        const hamburger = document.getElementById('elpHamburger');
        const drawer = document.getElementById('elpMobileDrawer');
        const closeBtn = document.getElementById('elpDrawerClose');

        if(hamburger && drawer && closeBtn) {
            hamburger.addEventListener('click', () => {
                drawer.classList.add('open');
            });
            closeBtn.addEventListener('click', () => {
                drawer.classList.remove('open');
            });
            drawer.addEventListener('click', (e) => {
                if (e.target === drawer) drawer.classList.remove('open');
            });
        }

        // Set Active Nav Link
        if (activeNavId) {
            const activeEl = document.getElementById(activeNavId);
            if (activeEl) activeEl.classList.add('active');
        }

        // Check Auth Status and Update UI
        const { data: { session } } = await supabase.auth.getSession();
        
        if (session) {
            // Fetch User Role
            const { data: profile } = await supabase.from('users').select('role').eq('auth_id', session.user.id).single();
            let dashUrl = 'login.html';
            
            if (profile) {
                if (profile.role === 'admin') dashUrl = 'admin/dashboard.html';
                else if (profile.role === 'worker') dashUrl = 'worker/dashboard.html';
                else if (profile.role === 'tool_provider') dashUrl = 'tool_provider/dashboard.html';
                else dashUrl = 'client/dashboard.html';
            }

            const authHtml = `
                <a href="chat.html" class="btn-outline" style="border-color:#e5e7eb;color:#374151;" title="Messages"><i class="ph-bold ph-chat-circle-dots"></i></a>
                <a href="${dashUrl}" class="btn-outline" style="background:#10b981;color:white;border-color:#10b981;"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <a href="#" id="logoutBtn" class="btn-outline" style="color:#ef4444;border-color:#fca5a5;" title="Logout"><i class="ph-bold ph-sign-out"></i></a>
            `;
            
            const mobileAuthHtml = `
                <a href="chat.html" style="background:#f3f4f6;color:#374151;justify-content:center;margin-top:6px;"><i class="ph-bold ph-chat-circle-dots"></i> Messages</a>
                <a href="${dashUrl}" style="background:#10b981;color:white;justify-content:center;margin-top:6px;"><i class="ph-bold ph-squares-four"></i> Dashboard</a>
                <a href="#" id="mobileLogoutBtn" style="color:#ef4444;background:#fee2e2;justify-content:center;margin-top:6px;"><i class="ph-bold ph-sign-out"></i> Logout</a>
            `;

            document.getElementById('auth-buttons').innerHTML = authHtml;
            document.getElementById('mobile-auth-buttons').innerHTML = mobileAuthHtml;

            // Logout Logic
            const handleLogout = async (e) => {
                e.preventDefault();
                await supabase.auth.signOut();
                window.location.href = 'index.html';
            };

            document.getElementById('logoutBtn')?.addEventListener('click', handleLogout);
            document.getElementById('mobileLogoutBtn')?.addEventListener('click', handleLogout);
        }

    } catch (err) {
        console.error("Error loading layout:", err);
    }
}
