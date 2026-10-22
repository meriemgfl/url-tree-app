<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>URL Tree</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900">
    <div x-data="app()" x-init="init()">
        @yield('content')
    </div>

    <script>
        function app() {
            return {
                showAddModal: false,
                init() {
                    if (window.electronAPI) {
                        window.electronAPI.onAddUrlModal(() => {
                            this.showAddModal = true;
                        });
                    }
                },
            };
        }
    </script>
</body>
</html>
<!-- <script>
    if (window.electronAPI) {
        window.electronAPI.onAddUrlModal(() => {
            console.log('Add URL triggered from native menu!');
            // later: actually open your add-URL modal/form here
        });
    }
</script> -->