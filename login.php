<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulsePOS - Secure Terminal Login</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50/50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-xl max-w-md w-full space-y-6">
        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-100 mx-auto">
                <i class="fa-solid fa-shield-halved text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-bold text-slate-900 text-xl tracking-tight">PulsePOS Terminal</h1>
                <p class="text-xs text-slate-400">Enter your credentials to unlock your workspace.</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="login-alert" class="hidden bg-rose-50 border border-rose-100 text-rose-600 text-xs font-semibold p-3.5 rounded-xl text-center"></div>

        <!-- Login Form -->
        <form onsubmit="handleLogin(event)" class="space-y-4">
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 text-xs">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" required placeholder="e.g. admin" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 text-xs">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-xl text-sm shadow-lg shadow-blue-100 transition-colors mt-2">
                Sign In to Workspace
            </button>
        </form>

        <!-- Register Redirect Link -->
        <div class="text-center pt-2 border-t border-slate-50">
            <p class="text-xs text-slate-400">New here? <a href="register.php" class="text-blue-600 font-semibold hover:underline">Register your business</a></p>
        </div>
    </div>

    <script>
    async function handleLogin(e) {
        e.preventDefault();
        const alertBox = document.getElementById('login-alert');
        alertBox.classList.add('hidden');

        const formData = new FormData(e.target);
        try {
            // Direct hit to multi-user dynamic action
            const response = await fetch('api/auth.php?action=login', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();

            if (res.success) {
                window.location.href = res.redirect;
            } else {
                alertBox.innerText = res.message;
                alertBox.classList.remove('hidden');
            }
        } catch (err) {
            console.error("Auth pipeline failure:", err);
        }
    }
    </script>
</body>
</html>