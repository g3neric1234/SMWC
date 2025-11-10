<?php if (!isset($_SESSION)) session_start(); ?>
<?php
if (!isset($pdo)) {
    include 'db_connection.php';
}
?>

<?php
$isAdmin = false;
if (isset($_SESSION['user'])) {
    try {
        $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['user']]);
        $user = $stmt->fetch();
        $isAdmin = $user && $user['is_admin'];
    } catch (PDOException $e) {
        $isAdmin = false;
    }
}
?>

<button onclick="toggleMenu()" class="fixed top-4 left-4 z-50 bg-zinc-700 p-2 rounded-full shadow-lg md:hidden hover:bg-zinc-600 transition-colors duration-300 transform hover:scale-110">
    <svg class="w-6 h-6 text-white transition-transform duration-300 hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-zinc-900 shadow-lg z-40 p-4 transform transition-transform duration-300 md:translate-x-0 -translate-x-full">
    <a href="menu.php" class="inline-block">
        <h1 class="text-xl font-bold mb-8 neon hover:text-cyan-400 transform hover:-translate-y-1 transition-all duration-300">
            SMWC
        </h1>
    </a>
    
    <ul class="space-y-4">
        <li class="menu-item flex items-center space-x-2 cursor-pointer hover:bg-zinc-800/50 px-3 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-cyan-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 transition-colors duration-300 group-hover:stroke-cyan-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
            </svg>
            <span class="group-hover:text-cyan-400 transition-colors duration-300">Network Scanning</span>
        </li>
        
        <li class="menu-item flex items-center space-x-2 cursor-pointer hover:bg-zinc-800/50 px-3 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 transition-colors duration-300 group-hover:stroke-red-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
            </svg>
            <span class="group-hover:text-green-400 transition-colors duration-300">Exploits</span>
        </li>

        <li class="menu-item flex items-center space-x-2 cursor-pointer hover:bg-zinc-800/50 px-3 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
            </svg>
            <span class="group-hover:text-green-400 transition-colors duration-300">Chat</span>
        </li>

        <li class="menu-item flex items-center space-x-2 cursor-pointer hover:bg-zinc-800/50 px-3 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
            <a href="../smwc/files.php"><span class="group-hover:text-green-400 transition-colors duration-300">Files</span></a>
        </li>
        
        <?php if ($isAdmin): ?>
        <li class="menu-item flex items-center space-x-2 cursor-pointer hover:bg-zinc-800/50 px-3 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
        </svg>

            <a href="../smwc/register.php"><span class="group-hover:text-green-400 transition-colors duration-300">Register new Users</span></a>
        </li>
        <li class="menu-item flex items-center space-x-2 cursor-pointer hover:bg-zinc-800/50 px-3 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
            <a href="../smwc/crear_usuario.php"><span class="group-hover:text-green-400 transition-colors duration-300">Create admin</span></a>
        </li>        
        <li class="menu-item flex items-center space-x-2 cursor-pointer hover:bg-zinc-800/50 px-3 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-green-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-6">
                <path fill-rule="evenodd" d="M2.25 6a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V6Zm3.97.97a.75.75 0 0 1 1.06 0l2.25 2.25a.75.75 0 0 1 0 1.06l-2.25 2.25a.75.75 0 0 1-1.06-1.06l1.72-1.72-1.72-1.72a.75.75 0 0 1 0-1.06Zm4.28 4.28a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
            </svg>
            <a href="../smwc/terminal.php"><span class="group-hover:text-green-400 transition-colors duration-300">Terminal</span></a>
        </li>
        <?php endif; ?>
    </ul>
    <div class="user-info absolute bottom-4 left-4 right-4 flex items-center justify-between cursor-pointer hover:bg-zinc-800/50 p-2 rounded-lg transition-all duration-300 group">
        <a href="profile.php" class="flex items-center space-x-4 w-full">
            <div class="w-10 h-10 bg-zinc-700 rounded-full flex items-center justify-center group-hover:bg-zinc-600 transition-colors duration-300">
                <?php if(isset($_SESSION['profile_pic']) && !empty($_SESSION['profile_pic'])): ?>
                    <img src="<?php echo htmlspecialchars($_SESSION['profile_pic']); ?>" class="w-full h-full rounded-full object-cover transition-transform duration-300 group-hover:scale-110">
                <?php else: ?>
                    <svg class="w-6 h-6 text-purple-400 transition-all duration-300 group-hover:text-purple-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A8.968 8.968 0 0112 15c2.21 0 4.21.805 5.879 2.137M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                <?php endif; ?>
            </div>
            <div>
                <h2 class="text-base font-semibold group-hover:text-purple-400 transition-colors duration-300">@<?php echo htmlspecialchars($_SESSION['user'] ?? ''); ?></h2>
                <p class="text-zinc-400 text-xs group-hover:text-zinc-300 transition-colors duration-300">
                    <?php echo $isAdmin ? 'Administrator' : 'User'; ?>
                </p>
            </div>
        </a>
        <a href="logout.php" class="text-red-400 hover:text-red-300 transition-all duration-300 ml-4 transform hover:scale-125">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
            </svg>
        </a>
    </div>
</div>

<script>
    function toggleMenu() {
        const menu = document.getElementById("sidebar");
        menu.classList.toggle("-translate-x-full");
    }
</script>