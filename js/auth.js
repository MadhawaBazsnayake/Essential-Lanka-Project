import { supabase } from './supabase-client.js';
import { sendOtpEmail } from './email-service.js';

// Inject custom CSS for beautiful pill toast
const style = document.createElement('style');
style.innerHTML = `
.custom-pill-toast {
    border-radius: 50px !important;
    padding: 10px 25px !important;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid rgba(255,255,255,0.4) !important;
    color: #1e293b !important;
}
.custom-pill-toast .swal2-title { font-size: 15px !important; margin-left: 10px; font-weight: 600; }
.custom-pill-toast .swal2-icon { transform: scale(0.7); margin: 0; }
`;
document.head.appendChild(style);

// Show Toast
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    customClass: { popup: 'custom-pill-toast' },
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Login User
export async function loginUser(emailOrPhone, password) {
    try {
        // Since Supabase Auth uses email, if phone is provided, we might need a custom edge function or handle it differently.
        // For now, we assume email login since standard Supabase Auth requires it.
        // If phone, supabase.auth.signInWithOtp({ phone }) is used, but for password, we use signInWithPassword.
        let isEmail = emailOrPhone.includes('@');
        
        let { data, error } = await supabase.auth.signInWithPassword({
            email: isEmail ? emailOrPhone : undefined,
            phone: !isEmail ? emailOrPhone : undefined,
            password: password,
        });

        if (error) throw error;
        
        Toast.fire({ icon: 'success', title: 'Successfully signed in!' });
        
        // Fetch user profile to get role
        let { data: profile } = await supabase.from('users').select('role').eq('auth_id', data.user.id).single();
        
        setTimeout(() => {
            if (profile && profile.role === 'admin') window.location.href = 'admin/dashboard.html';
            else if (profile && profile.role === 'worker') window.location.href = 'worker/dashboard.html';
            else if (profile && profile.role === 'tool_provider') window.location.href = 'tool_provider/dashboard.html';
            else window.location.href = 'client/dashboard.html';
        }, 1500);
        
    } catch (error) {
        Toast.fire({ icon: 'error', title: error.message });
        return { error };
    }
}

// Register User
export async function registerUser(userData) {
    try {
        // 1. Sign up in Supabase Auth
        const { data: authData, error: authError } = await supabase.auth.signUp({
            email: userData.email,
            password: userData.password,
            phone: userData.phone, // Supabase can take phone too if configured
            options: {
                data: {
                    first_name: userData.first_name,
                    last_name: userData.last_name,
                    role: userData.role
                }
            }
        });

        if (authError) throw authError;

        // 2. Insert into users table
        const { data: userRecord, error: dbError } = await supabase.from('users').insert([{
            auth_id: authData.user.id, // Store Supabase Auth UUID
            first_name: userData.first_name,
            last_name: userData.last_name,
            phone: userData.phone,
            password: 'SUPABASE_AUTH', // Managed by Supabase
            role: userData.role
        }]).select().single();

        if (dbError) throw dbError;

        // 3. If worker/tool provider, insert into worker_profiles
        if (userData.role === 'worker' || userData.role === 'tool_provider') {
            const { error: profileError } = await supabase.from('worker_profiles').insert([{
                worker_id: userRecord.id,
                category_id: userData.category_id || null,
                nic_number: userData.nic
            }]);
            if (profileError) throw profileError;
        }

        Toast.fire({ icon: 'success', title: 'Account created successfully! Please log in.' });
        setTimeout(() => { window.location.href = 'login.html'; }, 2000);

    } catch (error) {
        Toast.fire({ icon: 'error', title: error.message });
        return { error };
    }
}

// Request Password Reset
export async function resetPasswordRequest(email) {
    try {
        const { data, error } = await supabase.auth.resetPasswordForEmail(email, {
            redirectTo: window.location.origin + '/reset-password.html',
        });
        if (error) throw error;
        Toast.fire({ icon: 'success', title: 'Password reset email sent! Check your inbox.' });
    } catch (error) {
        Toast.fire({ icon: 'error', title: error.message });
        return { error };
    }
}

// Update Password
export async function updatePassword(newPassword) {
    try {
        const { data, error } = await supabase.auth.updateUser({
            password: newPassword
        });
        if (error) throw error;
        Toast.fire({ icon: 'success', title: 'Password updated successfully! You can now log in.' });
        setTimeout(() => { window.location.href = 'login.html'; }, 2000);
    } catch (error) {
        Toast.fire({ icon: 'error', title: error.message });
        return { error };
    }
}
