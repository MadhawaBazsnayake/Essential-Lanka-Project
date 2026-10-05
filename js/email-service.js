const RESEND_API_KEY = 're_DgftkjjD_CNi3aQAWaAvn4NmeYCKTrZyJ';
const FROM_EMAIL = 'onboarding@resend.dev'; // Replace with your verified domain email if available

const brandColor = '#2563eb';
const brandName = 'Essential Lanka';

// Base HTML template wrapper for all emails
const baseTemplate = (content) => `
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; color: #1f2937; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .header { background-color: ${brandColor}; padding: 24px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
        .content { padding: 32px; line-height: 1.6; }
        .footer { background-color: #f9fafb; padding: 20px; text-align: center; font-size: 13px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .button { display: inline-block; padding: 12px 24px; background-color: ${brandColor}; color: #ffffff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 16px; }
        .otp-box { background-color: #f3f4f6; border: 2px dashed ${brandColor}; padding: 16px; text-align: center; font-size: 32px; letter-spacing: 8px; font-weight: bold; color: ${brandColor}; margin: 24px 0; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>${brandName}</h1>
        </div>
        <div class="content">
            ${content}
        </div>
        <div class="footer">
            &copy; ${new Date().getFullYear()} ${brandName}. All rights reserved.<br>
            If you did not request this email, please safely ignore it.
        </div>
    </div>
</body>
</html>
`;

async function sendResendEmail(toEmail, subject, htmlContent) {
    try {
        const response = await fetch("https://api.resend.com/emails", {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${RESEND_API_KEY}`,
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                from: `${brandName} <${FROM_EMAIL}>`,
                to: [toEmail],
                subject: subject,
                html: baseTemplate(htmlContent)
            })
        });

        if (!response.ok) {
            const err = await response.json();
            console.error("Resend API Error:", err);
            return { success: false, error: err.message };
        }
        return { success: true };
    } catch (error) {
        console.error("Fetch Error:", error);
        return { success: false, error: error.message };
    }
}

// 1. Job Published Successfully
export async function sendJobPublishedEmail(toEmail, jobTitle) {
    const html = `
        <h2 style="color: #111827; margin-top: 0;">Job Published Successfully! 🎉</h2>
        <p>Hello,</p>
        <p>Great news! Your job <strong>"${jobTitle}"</strong> has been successfully published on ${brandName}.</p>
        <p>Workers matching your category will now be able to see your job and start sending you bids. You will be notified as soon as someone bids on your job.</p>
        <a href="#" class="button">View My Job</a>
    `;
    return await sendResendEmail(toEmail, "Your Job is Live! - " + brandName, html);
}

// 2. Bid Accepted Notification
export async function sendBidAcceptedEmail(toEmail, jobTitle, clientName) {
    const html = `
        <h2 style="color: #111827; margin-top: 0;">Congratulations! Your Bid was Accepted 🏆</h2>
        <p>Hello,</p>
        <p>Excellent news! <strong>${clientName}</strong> has accepted your bid for the job <strong>"${jobTitle}"</strong>.</p>
        <p>You can now start working on the project. Please communicate with the client through our messaging platform if you need any further clarifications before starting.</p>
        <a href="#" class="button">View Job Details & Start</a>
    `;
    return await sendResendEmail(toEmail, "Bid Accepted! - " + brandName, html);
}

// 3. OTP for Registration / Verification
export async function sendOtpEmail(toEmail, otpCode) {
    const html = `
        <h2 style="color: #111827; margin-top: 0;">Verify your email address</h2>
        <p>Hello,</p>
        <p>Welcome to ${brandName}! To complete your registration, please enter the verification code below on the website:</p>
        <div class="otp-box">${otpCode}</div>
        <p>This code will expire in 10 minutes for your security.</p>
    `;
    return await sendResendEmail(toEmail, "Your Verification Code - " + brandName, html);
}

// 4. Password Reset
export async function sendForgotPasswordEmail(toEmail, resetLink) {
    const html = `
        <h2 style="color: #111827; margin-top: 0;">Reset Your Password 🔒</h2>
        <p>Hello,</p>
        <p>We received a request to reset the password for your ${brandName} account. Click the button below to set a new password:</p>
        <a href="${resetLink}" class="button">Reset Password</a>
        <p style="margin-top: 24px; font-size: 14px;">Or copy and paste this link into your browser:</p>
        <p style="font-size: 14px; color: ${brandColor}; word-break: break-all;">${resetLink}</p>
        <p style="margin-top: 24px;">If you didn't request a password reset, you can safely ignore this email. Your password won't change until you create a new one.</p>
    `;
    return await sendResendEmail(toEmail, "Reset Your Password - " + brandName, html);
}
