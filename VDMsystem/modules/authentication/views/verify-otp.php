<?php
session_start();
require_once __DIR__ . '/../../core/config/config.php';

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/modules/dashboard/views/index.php");
    exit();
}

// Check if there's a pending OTP verification
if (!isset($_SESSION['otp_pending_user_id'])) {
    header("Location: " . LOGIN_URL);
    exit();
}

$targetEmail = $_SESSION['otp_target_email'] ?? 'your email';
// Mask the email for security
$emailParts = explode('@', $targetEmail);
$maskedEmail = substr($emailParts[0], 0, 2) . str_repeat('*', strlen($emailParts[0]) - 2) . '@' . $emailParts[1];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-gradient-to-br from-red-50 via-white to-red-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center mb-4">
                <div class="bg-white rounded-full shadow-xl p-3">
                    <img src="<?php echo BASE_URL; ?>/public/assets/images/logo.png" alt="Logo" class="w-[80px] h-[80px] object-contain">
                </div>
            </div>
            <h1 class="text-3xl font-black text-red-600 tracking-tighter">Identity Verification</h1>
            <p class="text-slate-500 font-bold text-sm tracking-tight mt-1">VDM Security Infrastructure</p>
        </div>

        <!-- Verification Card -->
        <div class="bg-white rounded-[2.5rem] shadow-2xl p-8 md:p-10 border border-white transform transition-all hover:shadow-red-500/10">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-red-50 text-red-600 rounded-3xl flex items-center justify-center mx-auto mb-4 text-2xl shadow-sm">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Enter Secure Code</h2>
                <p class="text-slate-500 font-bold text-sm mt-2 leading-relaxed px-4">
                    We've sent a 6-digit verification code to <br>
                    <span class="text-red-600"><?php echo htmlspecialchars($maskedEmail); ?></span>
                </p>
            </div>

            <!-- OTP Form -->
            <form action="../controllers/VerifyOtpController.php" method="POST" class="space-y-6">
                <!-- Session/Alert Messages -->
                <?php if (isset($_SESSION['otp_error'])): ?>
                    <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-2xl flex items-center text-xs font-bold animate-pulse">
                        <i class="bi bi-exclamation-circle-fill mr-2"></i>
                        <span><?php echo htmlspecialchars($_SESSION['otp_error']); unset($_SESSION['otp_error']); ?></span>
                    </div>
                <?php endif; ?>

                <div class="relative">
                    <input type="text" 
                           name="otp" 
                           id="otp-input"
                           maxlength="6" 
                           required 
                           autofocus
                           autocomplete="one-time-code"
                           placeholder="000 000"
                           class="w-full text-center text-4xl font-black tracking-[0.4em] py-5 bg-slate-50 border-2 border-slate-100 rounded-[2rem] focus:outline-none focus:border-red-500 focus:bg-white transition-all text-slate-800 placeholder-slate-200">
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full bg-red-600 text-white font-black py-4 rounded-[2rem] hover:bg-red-700 shadow-xl shadow-red-600/20 transform transition-all active:scale-[0.98] flex items-center justify-center gap-2 tracking-tight">
                        VERIFY & LOGIN
                        <i class="bi bi-arrow-right"></i>
                    </button>
                    
                    <a href="login.php" class="text-center text-slate-400 font-bold text-xs hover:text-slate-600 py-2 transition-colors uppercase tracking-widest">
                        Back to Login
                    </a>
                </div>
            </form>

            <!-- Resend Section -->
            <div class="mt-8 pt-8 border-t border-slate-50 text-center">
                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-3">Didn't receive code?</p>
                <button id="resend-btn" onclick="resendOtp()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-8 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    RESEND CODE <span id="timer"></span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-slate-400 font-bold text-[10px] uppercase tracking-[0.3em] mt-8">
            Secured by VDM Cloud Security Framework
        </p>
    </div>

    <script>
        // Simple timer logic for resend
        let seconds = 60;
        const resendBtn = document.getElementById('resend-btn');
        const timerSpan = document.getElementById('timer');

        function startTimer() {
            resendBtn.disabled = true;
            const interval = setInterval(() => {
                seconds--;
                timerSpan.textContent = `(${seconds}s)`;
                if (seconds <= 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    timerSpan.textContent = '';
                    seconds = 60;
                }
            }, 1000);
        }

        async function resendOtp() {
            // In a real app, this would call an endpoint to resend
            window.location.href = '../controllers/LoginController.php?resend=1';
        }

        // Auto-focus and clean input
        const input = document.getElementById('otp-input');
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
