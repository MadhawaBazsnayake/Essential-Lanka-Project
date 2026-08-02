<?php include 'includes/header.php'; ?>

<style>
    .auth-section { padding: 80px 5%; min-height: 85vh; display: flex; align-items: center; justify-content: center; position: relative; }
    .auth-container { width: 100%; max-width: 450px; position: relative; z-index: 10; }
    
    .ios-glass-card { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.8); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08); padding: 45px 40px; border-radius: 32px; }
    
    .form-group { margin-bottom: 25px; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.95rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 15px 20px; border-radius: 18px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; }
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    
    .btn-submit { background: #10b981; color: white; padding: 18px; border-radius: 50px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(16,185,129,0.2); margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(16,185,129,0.3); }
    
    .blob-bg-auth { position: absolute; top: 10%; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, rgba(244,238,227,0) 70%); border-radius: 50%; z-index: 1; }
    
    .forgot-link { float: right; color: #10b981; font-weight: 600; font-size: 0.9rem; text-decoration: none; margin-top: -15px; margin-bottom: 20px; display: inline-block;}
    .forgot-link:hover { text-decoration: underline; }
</style>

<section class="auth-section bg-cream">
    <div class="blob-bg-auth"></div>
    
    <div class="auth-container fade-up show">
        <div class="ios-glass-card text-center">
            
            <i class="ph-fill ph-user-circle" style="font-size: 4rem; color: #10b981; margin-bottom: 15px;"></i>
            <h2 style="font-weight: 800; font-size: 2.2rem; margin-bottom: 10px; color: #1a1a1a;">Welcome Back</h2>
            <p style="color: #666; margin-bottom: 35px; font-size: 1.1rem;">Please enter your details to sign in.</p>

            <form action="auth/auth.php" method="POST">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="tel" name="phone" class="form-control" placeholder="07XXXXXXXX" pattern="[0-9]{10}" required>
                </div>

                <div class="form-group" style="margin-bottom: 10px;">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <div style="text-align: right; width: 100%;">
                    <a href="forgot-password.php" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
                
                <p style="margin-top: 25px; color: #666; font-size: 1rem; font-weight: 500;">
                    Don't have an account? <a href="register.php" style="color: #10b981; font-weight: 800;">Sign Up</a>
                </p>
            </form>

        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>