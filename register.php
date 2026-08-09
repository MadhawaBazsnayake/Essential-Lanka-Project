<?php 
include 'includes/header.php'; 
$selected_role = isset($_GET['role']) && $_GET['role'] === 'worker' ? 'worker' : 'client';
?>

<style>
    /* Inline CSS for Register Page */
    .auth-section { padding: 80px 5%; min-height: 85vh; display: flex; align-items: center; justify-content: center; position: relative; }
    .auth-container { width: 100%; max-width: 550px; position: relative; z-index: 10; }
    
    .ios-glass-card { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.8); box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08); padding: 45px 40px; border-radius: 32px; }
    
    /* Role Switcher */
    .role-switch { display: flex; background: #e5dfd5; border-radius: 50px; padding: 6px; margin-bottom: 30px; position: relative; }
    .role-btn { flex: 1; text-align: center; padding: 12px; border-radius: 50px; font-weight: 700; color: #666; cursor: pointer; transition: 0.3s; z-index: 2; font-size: 1rem; }
    .role-btn.active { color: white; }
    .role-slider { position: absolute; top: 6px; left: 6px; width: calc(50% - 6px); height: calc(100% - 12px); background: #10b981; border-radius: 50px; transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); z-index: 1; box-shadow: 0 4px 15px rgba(16,185,129,0.3); }
    .slide-worker { left: calc(50%); }

    /* Form Elements */
    .form-group { margin-bottom: 22px; text-align: left; }
    .form-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 0.95rem; color: #1a1a1a; }
    .form-control { width: 100%; padding: 15px 20px; border-radius: 18px; border: 2px solid #e5dfd5; background: #ffffff; font-size: 1rem; transition: 0.3s; outline: none; font-weight: 500; }
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.1); }
    .d-flex-gap { display: flex; gap: 15px; }
    .d-flex-gap .form-group { flex: 1; }
    
    /* Worker Extra Fields Animation */
    .worker-fields { max-height: 0; overflow: hidden; opacity: 0; transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .worker-fields.show { max-height: 300px; opacity: 1; margin-top: 10px; }

    .btn-submit { background: #10b981; color: white; padding: 18px; border-radius: 50px; font-weight: 800; border: none; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(16,185,129,0.2); margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(16,185,129,0.3); }
    
    .blob-bg-auth { position: absolute; top: 10%; left: 50%; transform: translateX(-50%); width: 600px; height: 600px; background: radial-gradient(circle, rgba(16,185,129,0.15) 0%, rgba(244,238,227,0) 70%); border-radius: 50%; z-index: 1; }
</style>

<section class="auth-section bg-cream">
    <div class="blob-bg-auth"></div>
    
    <div class="auth-container fade-up show">
        <div class="ios-glass-card text-center">
            
            <h2 style="font-weight: 800; font-size: 2.2rem; margin-bottom: 10px; color: #1a1a1a;">Create Account</h2>
            <p style="color: #666; margin-bottom: 30px; font-size: 1.1rem;">Join Essential Lanka today.</p>

            <form action="auth/auth.php" method="POST" id="registerForm">
                
                <!-- Role Toggle -->
                <div class="role-switch">
                    <div class="role-slider <?php echo $selected_role === 'worker' ? 'slide-worker' : ''; ?>" id="roleSlider"></div>
                    <div class="role-btn <?php echo $selected_role === 'client' ? 'active' : ''; ?>" onclick="setRole('client')">Customer</div>
                    <div class="role-btn <?php echo $selected_role === 'worker' ? 'active' : ''; ?>" onclick="setRole('worker')">Worker</div>
                </div>
                <input type="hidden" name="role" id="roleInput" value="<?php echo $selected_role; ?>">
                <input type="hidden" name="action" value="register">

                <!-- Basic Fields (Common for both) -->
                <div class="d-flex-gap">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" placeholder="John" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mobile Number</label>
                    <input type="tel" name="phone" class="form-control" placeholder="07XXXXXXXX" pattern="[0-9]{10}" required>
                </div>

                <!-- Extra Fields for Workers ONLY -->
                <div class="worker-fields <?php echo $selected_role === 'worker' ? 'show' : ''; ?>" id="workerExtraFields">
                    <div class="form-group">
                        <label>National Identity Card (NIC)</label>
                        <input type="text" name="nic" id="nicInput" class="form-control" placeholder="e.g. 1995XXXXXXV">
                    </div>
                    <div class="form-group">
                        <label>Primary Skill / Category</label>
                        <select name="category_id" id="categoryInput" class="form-control">
                            <option value="">Select your service...</option>
                            <option value="1">Electrical</option>
                            <option value="2">Plumbing</option>
                            <option value="3">Cleaning</option>
                            <option value="4">Carpentry</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a strong password" required minlength="6">
                </div>

                <button type="submit" class="btn-submit">Sign Up</button>
                
                <p style="margin-top: 25px; color: #666; font-size: 1rem; font-weight: 500;">
                    Already have an account? <a href="login.php" style="color: #10b981; font-weight: 800;">Log In</a>
                </p>
            </form>

        </div>
    </div>
</section>

<script>
function setRole(role) {
    const slider = document.getElementById('roleSlider');
    const input = document.getElementById('roleInput');
    const btns = document.querySelectorAll('.role-btn');
    const workerFields = document.getElementById('workerExtraFields');
    const nicInput = document.getElementById('nicInput');
    const categoryInput = document.getElementById('categoryInput');
    
    input.value = role;
    btns.forEach(btn => btn.classList.remove('active'));
    
    if(role === 'worker') {
        slider.classList.add('slide-worker');
        btns[1].classList.add('active');
        workerFields.classList.add('show');
        // Require extra fields for workers
        nicInput.required = true;
        categoryInput.required = true;
    } else {
        slider.classList.remove('slide-worker');
        btns[0].classList.add('active');
        workerFields.classList.remove('show');
        // Remove requirements for clients
        nicInput.required = false;
        categoryInput.required = false;
    }
}
</script>

<?php include 'includes/footer.php'; ?>