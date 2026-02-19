<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nomitech - Superadmin</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
</head>

<body class="font-inter bg-gray-100 overflow-hidden">

    <div class="flex min-h-screen">

       <aside class="w-64 bg-blue-900 border-r border-blue-800 min-h-screen flex flex-col shadow-lg">
    <x-nomitech />
       </aside>


       <main class="flex-1 p-8 bg-gray-100 overflow-y-auto">

            @yield('content')
        </main>

    </div>

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</body>
</html>
