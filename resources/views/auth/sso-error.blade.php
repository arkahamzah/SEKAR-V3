<!-- resources/views/auth/sso-error.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSO Error - SEKAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md text-center">
            <!-- Error Icon -->
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>

            <!-- Error Message -->
            <h2 class="text-xl font-bold text-gray-900 mb-2">SSO Error</h2>
            <p class="text-gray-600 mb-6">{{ $message }}</p>

            <!-- Close Button -->
            <button 
                onclick="window.close()"
                class="w-full bg-red-600 text-white py-3 rounded-lg font-medium hover:bg-red-700 transition duration-200"
            >
                Tutup
            </button>

            <!-- Help Text -->
            <p class="text-xs text-gray-500 mt-4">
                Silakan coba login ulang dari halaman utama
            </p>
        </div>
    </div>

    <script>
    // Auto-close after 5 seconds
    setTimeout(function() {
        window.close();
    }, 5000);

    // Notify parent window about error
    if (window.opener) {
        window.opener.postMessage({
            type: 'sso_error',
            message: '{{ $message }}'
        }, window.location.origin);
    }
    </script>
</body>
</html>