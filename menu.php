<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="images/head.ico" type="image/ico">
    <style>
        .neon {
            text-shadow: 0 0 5px #00f, 0 0 10px #00f, 0 0 20px #00f;
        }

        
    </style>
    <script>
        function toggleMenu() {
            const menu = document.getElementById("sidebar");
            menu.classList.toggle("-translate-x-full");
        }

        async function fetchClientInfo() {
            const res = await fetch("https://ipapi.co/json/");
            const data = await res.json();
            document.getElementById("client-ip").textContent = data.ip;
            document.getElementById("client-location").textContent = `${data.city}, ${data.region}, ${data.country_name}`;
            document.getElementById("client-browser").textContent = navigator.userAgent;
            let os = "Unknown OS";
            if (navigator.userAgent.includes("Linux; Android")) {
                os = "Android";
            } else if (navigator.userAgent.includes("Windows")) {
                os = "Windows";
            } else if (navigator.userAgent.includes("Linux")) {
                os = "Linux";
            }
            document.getElementById("client-os").textContent = os;
        }

        async function fetchServerStatus() {
            try {
                const res = await fetch("http://127.0.0.1:5000/server-status");
                if (!res.ok) {
                    throw new Error(`HTTP error! status: ${res.status}`);
                }
                const data = await res.json();
                console.log("Server Status:", data);
                document.getElementById("server-ip").textContent = data.local_ip || "Unavailable";
                document.getElementById("server-ram").textContent = `${data.ram_used_gb} GB / ${data.ram_total_gb} GB (${data.ram_percent}%)`;
                document.getElementById("server-cpu").textContent = `${data.cpu_percent}%`;
            } catch (error) {
                console.error("Error fetching server status:", error);
                document.getElementById("server-ip").textContent = "Error";
                document.getElementById("server-ram").textContent = "Error";
                document.getElementById("server-cpu").textContent = "Error";
            }
        }

        window.onload = () => {
            fetchClientInfo();
            fetchServerStatus();
            setInterval(fetchServerStatus, 1000);
        };
    </script>
</head>

<body class="bg-black text-white font-sans">
    <?php
    session_start();
    require_once 'db_connection.php';
    
    if (!isset($_SESSION['user'])) {
        header("Location: /smwc/index");
        exit;
    }
    $serverName = gethostname() ?: ($_SERVER['COMPUTERNAME'] ?? php_uname('n') ?? 'Unknown Server');
    ?>
    <button onclick="toggleMenu()" class="fixed top-4 left-4 z-50 bg-zinc-700 p-2 rounded-full shadow-lg md:hidden">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    <?php include 'particles.php'; ?>
    <?php include 'navbar.php'; ?>

    <main class="md:ml-64 min-h-screen p-10">
        <section class="text-center space-y-6">
            <h1 class="text-4xl font-bold neon">Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?></h1>
            <!-- <p class="text-zinc-400 max-w-xl mx-auto">testing text </p> -->
            <p class="text-zinc-400 max-w-xl mx-auto">SMWC version 1.3 BETA.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-10">
                <div class="bg-zinc-800 p-6 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-cyan-400 mb-2">Connected to:</h3>
                    <p class="text-sm text-zinc-300"><?php echo htmlspecialchars($serverName); ?></p>
                </div>
                <div class="bg-zinc-800 p-6 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-green-400 mb-2">¿Did you know?</h3>
                    <p class="text-sm text-zinc-300">Urna tempor pulvinar vivamus fringilla lacus nec metus. Integer nunc posuere ut hendrerit semper vel class.</p>
                </div>
                <div class="bg-zinc-800 p-6 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold text-red-400 mb-2">Lorem</h3>
                    <p class="text-sm text-zinc-300">Lorem ipsum dolor sit amet consectetur adipiscing elit. Placerat in id cursus mi pretium tellus duis.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10 text-left">
                <div class="bg-zinc-800 p-6 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold mb-4">Client Info</h3>
                    <div class="space-y-2">
                        <div class="bg-zinc-700 rounded-lg p-3 shadow-inner flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                            <span><strong>IP:</strong> <span id="client-ip">Loading...</span></span>
                        </div>
                        <div class="bg-zinc-700 rounded-lg p-3 shadow-inner flex items-center space-x-2">
                            <div class="w-6 h-6 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                </svg>
                            </div>
                            <span><strong>Browser:</strong> <span id="client-browser">Loading...</span></span>
                        </div>
                        <div class="bg-zinc-700 rounded-lg p-3 shadow-inner flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span><strong>Location:</strong> <span id="client-location">Loading...</span></span>
                        </div>
                        <div class="bg-zinc-700 rounded-lg p-3 shadow-inner flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-6">
                                <path fill-rule="evenodd" d="M2.25 6a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V6Zm3.97.97a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06l-2.25 2.25a.75.75 0 0 1-1.06-1.06l1.72-1.72-1.72-1.72a.75.75 0 0 1 0-1.06Zm4.28 4.28a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                            </svg>
                            <span><strong>OS:</strong> <span id="client-os">Loading...</span></span>
                        </div>
                    </div>
                </div>

                <div class="bg-zinc-800 p-6 rounded-xl shadow-lg">
                    <h3 class="text-lg font-bold mb-4">Server Status</h3>
                    <div class="space-y-2">
                        <div class="bg-zinc-700 rounded-lg p-3 shadow-inner flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" />
                            </svg>
                            <span><strong>RAM:</strong> <span id="server-ram">Loading...</span></span>
                        </div>
                        <div class="bg-zinc-700 rounded-lg p-3 shadow-inner flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z" />
                            </svg>
                            <span><strong>CPU:</strong> <span id="server-cpu">Loading...</span></span>
                        </div>
                        <div class="bg-zinc-700 rounded-lg p-3 shadow-inner flex items-center space-x-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                            </svg>
                            <span><strong>IP:</strong> <span id="server-ip">Loading...</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
