<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Napzárás Rendszer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">
                Napzárás Rendszer
            </h1>
            <p class="text-gray-600 mb-8">
                Üdvözölünk a rendszerben!
            </p>
            <div class="space-x-4">
                <a href="{{ route('login') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700">
                    Bejelentkezés
                </a>
                <a href="{{ route('register') }}" class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300">
                    Regisztráció
                </a>
            </div>
        </div>
    </div>
</body>
</html>