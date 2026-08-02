<?php include 'includes/header.php'; ?>

<style>
    /* Complex Search Box */
    .complex-search { display: flex; align-items: center; justify-content: space-between; padding: 10px; border-radius: 50px; flex-wrap: wrap; gap: 10px; }
    .search-input-group { display: flex; align-items: center; flex: 1; min-width: 200px; padding: 5px 15px; }
    .search-input-group input { width: 100%; border: none; background: transparent; outline: none; font-size: 1.05rem; margin-left: 10px; }
    .search-divider { width: 1px; height: 30px; background: rgba(0,0,0,0.1); }
    
    /* Stats Section Spacing Fix & Animations */
    .stats-section { padding-bottom: 100px; padding-top: 40px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px; text-align: center; position: relative; z-index: 10; }
    .stat-card { background: white; padding: 35px 20px; border-radius: 28px; box-shadow: 0 15px 40px rgba(0,0,0,0.06); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 1px solid rgba(0,0,0,0.02); }
    .stat-card:hover { transform: translateY(-10px) scale(1.05); box-shadow: 0 20px 50px rgba(16,185,129,0.15); border-color: rgba(16,185,129,0.2); }
    .stat-card h3 { font-size: 2.8rem; font-weight: 800; color: var(--accent-green); margin-bottom: 5px; display: flex; justify-content: center; align-items: center; }
    .stat-card p { font-weight: 600; color: var(--text-muted); font-size: 1.1rem; }

    /* Hero Buttons */
    .hero-btn-solid { background: var(--accent-green); color: white; padding: 16px 40px; border-radius: 50px; font-weight: 800; font-size: 1.1rem; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); box-shadow: 0 10px 25px rgba(16,185,129,0.3); border: none; display: inline-block;}
    .hero-btn-solid:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 15px 35px rgba(16,185,129,0.4); color: white;}
    .hero-btn-outline { background: transparent; color: var(--accent-green); padding: 14px 40px; border-radius: 50px; font-weight: 800; font-size: 1.1rem; border: 2px solid var(--accent-green); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: inline-block;}
    .hero-btn-outline:hover { background: var(--accent-green); color: white; transform: translateY(-5px) scale(1.05); box-shadow: 0 15px 35px rgba(16,185,129,0.3); }
    .hero-buttons-wrapper { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: 50px; }

    /* Reviews Grid Fix */
    .reviews-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 40px; }

    /* Worker CTA Banner */
    .worker-cta-banner { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 60px 5%; border-radius: 40px; text-align: center; box-shadow: 0 20px 40px rgba(16,185,129,0.2); }
    .worker-cta-banner h2 { font-size: 2.8rem; font-weight: 800; margin-bottom: 15px; }
    .worker-cta-banner p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 35px; }
    .cta-buttons-wrapper { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
    
    .btn-solid-white { background: #ffffff !important; color: #059669 !important; padding: 16px 35px !important; border-radius: 50px !important; font-weight: 800 !important; font-size: 1.1rem !important; transition: 0.3s; border: none; }
    .btn-solid-white:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .btn-outline-white { background: transparent !important; color: #ffffff !important; padding: 14px 35px !important; border-radius: 50px !important; font-weight: 800 !important; font-size: 1.1rem !important; border: 2px solid #ffffff !important; transition: 0.3s; }
    .btn-outline-white:hover { background: #ffffff !important; color: #059669 !important; transform: translateY(-3px) scale(1.05); }

    /* Mobile Responsiveness Strict Rules */
    @media (max-width: 768px) {
        .complex-search { flex-direction: column; border-radius: 20px; padding: 15px; }
        .search-divider { width: 100%; height: 1px; margin: 10px 0; }
        .hero-btn-solid, .hero-btn-outline { width: 100%; text-align: center; }
        .hero-buttons-wrapper { flex-direction: column; gap: 15px; margin-top: 35px; }
        .stats-section { padding-bottom: 60px; }
        .cta-buttons-wrapper { flex-direction: column; }
        .worker-cta-banner h2 { font-size: 2rem; }
    }
</style>

<section class="hero bg-cream">
    <div class="blob-bg"></div>
    <div class="hero-content fade-up">
        <h1 class="massive-text" style="margin-bottom: 15px;">
            Essential Lanka <br>
            <span class="pill-image floating-element" style="background-image: url('https://images.unsplash.com/photo-1581578731548-c64695cc6952?q=80&w=600&auto=format&fit=crop');"></span> 
            is Support.
        </h1>
        <p class="hero-sub" style="margin-bottom: 40px;">What service do you need?</p>
        
        <div class="quick-search floating-element" style="animation-delay: 1s; max-width: 800px;">
            <form action="services.php" method="GET" class="glass-card complex-search ios-glass">
                <div class="search-input-group">
                    <i class="ph ph-magnifying-glass search-icon"></i>
                    <input type="text" name="q" placeholder='"Electrician", "Plumber", "Painter"...' required>
                </div>
                <div class="search-divider"></div>
                <div class="search-input-group">
                    <i class="ph-fill ph-map-pin text-red search-icon"></i>
                    <input type="text" name="location" placeholder="Your Location" required>
                </div>
                <button type="submit" class="btn-primary" style="padding: 15px 40px; margin: 0;">Search Workers</button>
            </form>
        </div>

        <div class="hero-buttons-wrapper fade-up">
            <a href="register.php?role=worker" class="hero-btn-solid">Join as a Worker</a>
            <a href="register.php?role=client" class="hero-btn-outline">Join as a Customer</a>
        </div>
        
    </div>
</section>

<section class="bg-cream stats-section">
    <div class="grid-container fade-up">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><span class="counter" data-target="10000">0</span>+</h3>
                <p>Verified Workers</p>
            </div>
            <div class="stat-card">
                <h3><span class="counter" data-target="25000">0</span>+</h3>
                <p>Jobs Completed</p>
            </div>
            <div class="stat-card">
                <h3><span class="counter" data-target="100">0</span>+</h3>
                <p>Service Categories</p>
            </div>
            <div class="stat-card">
                <h3 style="color: var(--accent-blue);">24/7</h3>
                <p>Support</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-white" id="services">
    <div class="grid-container">
        <div class="section-header text-center fade-up">
            <h2>Popular Services</h2>
        </div>
        <div class="cards-grid grid-4">
            <div class="service-card premium-card card-cream fade-up">
                <div class="card-content">
                    <div class="icon-box icon-green"><i class="ph-fill ph-lightning"></i></div>
                    <h3>Electrical</h3>
                    <p class="text-muted mt-2">Wiring, installations, and repairs.</p>
                </div>
                <a href="#" class="card-link"><i class="ph ph-arrow-right"></i></a>
            </div>
            <div class="service-card premium-card card-cream fade-up" style="transition-delay: 100ms;">
                <div class="card-content">
                    <div class="icon-box icon-blue"><i class="ph-fill ph-drop"></i></div>
                    <h3>Plumbing</h3>
                    <p class="text-muted mt-2">Leak fixes, pipe laying, and maintenance.</p>
                </div>
                <a href="#" class="card-link"><i class="ph ph-arrow-right"></i></a>
            </div>
            <div class="service-card premium-card card-cream fade-up" style="transition-delay: 200ms;">
                <div class="card-content">
                    <div class="icon-box icon-purple"><i class="ph-fill ph-broom"></i></div>
                    <h3>Cleaning</h3>
                    <p class="text-muted mt-2">Deep house cleaning and organizing.</p>
                </div>
                <a href="#" class="card-link"><i class="ph ph-arrow-right"></i></a>
            </div>
            <div class="service-card premium-card card-cream fade-up" style="transition-delay: 300ms;">
                <div class="card-content">
                    <div class="icon-box icon-orange"><i class="ph-fill ph-paint-roller"></i></div>
                    <h3>Painting</h3>
                    <p class="text-muted mt-2">Interior and exterior house painting.</p>
                </div>
                <a href="#" class="card-link"><i class="ph ph-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-cream" id="how-it-works">
    <div class="grid-container bg-white rounded-section">
        <div class="section-header text-center fade-up">
            <span class="badge-pill" style="color:var(--accent-green); background: rgba(16,185,129,0.1); margin-bottom:15px;">Process</span>
            <h2>How It Works</h2>
        </div>
        <div class="process-steps">
            <div class="step fade-up">
                <div class="step-icon"><i class="ph-fill ph-map-pin"></i></div>
                <h4>1. Search & Match</h4>
                <p class="text-muted mt-2">Use GPS to find verified workers near you.</p>
            </div>
            <div class="step fade-up" style="transition-delay: 100ms;">
                <div class="step-icon"><i class="ph-fill ph-chat-circle-text"></i></div>
                <h4>2. Accept & Chat</h4>
                <p class="text-muted mt-2">Compare bids and chat securely in-app.</p>
            </div>
            <div class="step fade-up" style="transition-delay: 200ms;">
                <div class="step-icon"><i class="ph-fill ph-check-circle"></i></div>
                <h4>3. Job Done</h4>
                <p class="text-muted mt-2">Pay securely and leave a star rating.</p>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-dark rounded-section mt-5 mx-2" id="safety">
    <div class="grid-2-col fade-up">
        <div class="info-text">
            <h2 class="mb-4">Your Safety Matters</h2>
            <ul class="feature-list-large trust-spacing-fix" style="color: white;">
                <li><i class="ph-fill ph-check-circle text-green"></i> Identity Verified Workers</li>
                <li><i class="ph-fill ph-lock-key text-green"></i> Secure Communication</li>
                <li><i class="ph-fill ph-star text-green"></i> Review System</li>
                <li><i class="ph-fill ph-scales text-green"></i> Dispute Resolution</li>
                <li><i class="ph-fill ph-shield-check text-green"></i> Safe Payments</li>
            </ul>
        </div>
        <div class="trust-image-wrapper">
            <img src="https://images.unsplash.com/photo-1573164713988-8665fc963095?q=80&w=600&auto=format&fit=crop" class="rounded-image shadow-lg" alt="Safety">
        </div>
    </div>
</section>

<section class="section-padding bg-cream">
    <div class="grid-container">
        <div class="section-header text-center fade-up">
            <h2>Customer Feedback</h2>
        </div>
        <div class="reviews-grid">
            <div class="review-card premium-card ios-glass text-center fade-up">
                <div class="stars mb-3" style="font-size: 1.3rem;"><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i></div>
                <p class="review-text" style="font-size: 1.1rem; font-weight: 500; font-style: italic;">"Found a plumber within 20 minutes. Very professional service."</p>
                <h5 class="mt-4 text-muted">- Nimal, Colombo</h5>
            </div>
            
            <div class="review-card premium-card ios-glass text-center fade-up" style="transition-delay: 100ms;">
                <div class="stars mb-3" style="font-size: 1.3rem;"><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i></div>
                <p class="review-text" style="font-size: 1.1rem; font-weight: 500; font-style: italic;">"Super easy to use. The electrician arrived on time and fixed the issue fast."</p>
                <h5 class="mt-4 text-muted">- Sarah, Dehiwala</h5>
            </div>
            
            <div class="review-card premium-card ios-glass text-center fade-up" style="transition-delay: 200ms;">
                <div class="stars mb-3" style="font-size: 1.3rem;"><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star text-gold"></i><i class="ph-fill ph-star-half text-gold"></i></div>
                <p class="review-text" style="font-size: 1.1rem; font-weight: 500; font-style: italic;">"Great platform to find trusted daily workers without any middlemen."</p>
                <h5 class="mt-4 text-muted">- Dinesh, Kandy</h5>
            </div>
        </div>
    </div>
</section>

<section class="section-padding bg-cream pt-0">
    <div class="grid-container">
        <div class="worker-cta-banner fade-up">
            <i class="ph-fill ph-briefcase" style="font-size: 4rem; margin-bottom: 20px; color: rgba(255,255,255,0.9);"></i>
            <h2>Have a Skill? Earn Money From Your Skills</h2>
            <p>Join thousands of Sri Lankan workers earning daily on our platform.</p>
            
            <div class="cta-buttons-wrapper mt-4">
                <a href="register.php?role=worker" class="btn-solid-white">Join as a Worker</a>
                <a href="register.php?role=client" class="btn-outline-white">Join as a Customer</a>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const counters = document.querySelectorAll('.counter');
    const speed = 200; // Animation speed

    const startCounting = (counter) => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const count = +counter.innerText.replace(/,/g, '');
            const inc = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc).toLocaleString();
                setTimeout(updateCount, 15);
            } else {
                counter.innerText = target.toLocaleString();
            }
        };
        updateCount();
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounting(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => {
        observer.observe(counter);
    });
});
</script>

<?php include 'includes/footer.php'; ?>