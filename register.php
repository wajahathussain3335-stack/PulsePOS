<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulsePOS - Create Business Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50/50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white border border-slate-100 rounded-3xl p-8 shadow-xl max-w-md w-full space-y-6">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-100 mx-auto">
                <i class="fa-solid fa-shield-halved text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-bold text-slate-900 text-xl tracking-tight">Register Your Business</h1>
                <p class="text-xs text-slate-400">Launch your personal secure standalone POS workspace.</p>
            </div>
        </div>

        <div id="reg-alert" class="hidden text-xs font-semibold p-3.5 rounded-xl text-center"></div>

        <form onsubmit="handleRegister(event)" class="space-y-4">
            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Choose Username</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 text-xs">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" required placeholder="e.g. wajahat_shop" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <div>
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1.5">Create Secure Password</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 text-xs">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 rounded-xl text-sm shadow-lg shadow-blue-100 transition-colors mt-2">
                Create Account
            </button>
        </form>

        <div class="text-center pt-2 border-t border-slate-50">
            <p class="text-xs text-slate-400">Already have a workspace? <a href="login.php" class="text-blue-600 font-semibold hover:underline">Sign In</a></p>
        </div>
    </div>

    <script>
    async function handleRegister(e) {
        e.preventDefault();
        const alertBox = document.getElementById('reg-alert');
        alertBox.className = 'hidden text-xs font-semibold p-3.5 rounded-xl text-center';

        const formData = new FormData(e.target);
        try {
            const response = await fetch('api/auth.php?action=register', {
                method: 'POST',
                body: formData
            });
            const res = await response.json();

            if (res.success) {
                alertBox.innerText = res.message;
                alertBox.classList.add('bg-emerald-50', 'text-emerald-600', 'border', 'border-emerald-100');
                alertBox.classList.remove('hidden');
                setTimeout(() => window.location.href = 'login.php', 2000);
            } else {
                alertBox.innerText = res.message;
                alertBox.classList.add('bg-rose-50', 'text-rose-600', 'border', 'border-rose-100');
                alertBox.classList.remove('hidden');
            }
        } catch (err) {
            console.error("Registration broken:", err);
        }
    }
    </script>
</body>
</html>