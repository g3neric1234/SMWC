<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_connection.php';

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: /smwc/index");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cmd'])) {
    header('Content-Type: text/plain');
    
    $cmd = $_POST['cmd'];
    if (preg_match('exit', $cmd)) {
        echo "Error: You can't use this command!";
        exit;
    }
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec("chcp 65001>nul & " . $cmd . " 2>&1");
    } else {
        $output = shell_exec($cmd . " 2>&1");
    }
    
    echo $output ?: 'Command executed with no output';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Live Terminal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="images/head.ico" type="image/ico">
    <style>
        .neon {
            text-shadow: 0 0 5px #00f, 0 0 10px #00f, 0 0 20px #00f;
        }
        .neon-text {
            text-shadow: 0 0 5px #00f, 0 0 10px #00f;
        }
        body {
            background: #181818;
            overflow: hidden;
        }
        .terminal-container {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .terminal-window {
            background: #181818;
            color:rgb(255, 255, 255);
            font-family: 'Consolas', 'Courier New', monospace;
            border-radius: 0.5rem;
            box-shadow: 0 0 20px #000a;
            padding: 2rem 1.5rem 1.5rem 1.5rem;
            height: 80vh;
            width: 90%;
            max-width: 1000px;
            border: 2px solid #222;
            display: flex;
            flex-direction: column;
        }
        .terminal-bar {
            background: #222;
            border-radius: 0.5rem 0.5rem 0 0;
            padding: 0.5rem 1rem;
            margin: -2rem -1.5rem 1.5rem -1.5rem;
            display: flex;
            align-items: center;
        }
        .terminal-dot {
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            margin-right: 0.5rem;
        }
        .dot-red { background: #ff5f56; }
        .dot-yellow { background: #ffbd2e; }
        .dot-green { background: #27c93f; }
        .prompt {
            color:rgb(255, 255, 255);
            white-space: nowrap;
        }
        .output-container {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 0.5rem;
        }
        .output {
            white-space: pre-wrap;
            word-break: break-word;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }
        .input-line {
            display: flex;
            align-items: center;
            margin-top: auto;
        }
        .input-line input {
            background: transparent;
            border: none;
            outline: none;
            color:rgb(255, 255, 255);
            font-family: inherit;
            font-size: inherit;
            width: 100%;
            padding-left: 0.5rem;
        }
        ::selection {
            background:rgb(255, 255, 255);
            color: #181818;
        }
        .scrollbar::-webkit-scrollbar {
            width: 8px;
            background: #222;
        }
        .scrollbar::-webkit-scrollbar-thumb {
            background: #333;
            border-radius: 4px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('terminal-form');
            const input = document.getElementById('terminal-input');
            const output = document.getElementById('terminal-output');
            const terminal = document.getElementById('terminal-window');
            const outputContainer = document.getElementById('output-container');
            let prompt = '<?php echo htmlspecialchars(getenv("USERNAME") ?: "user"); ?>@<?php echo htmlspecialchars(gethostname()); ?>:~$';

            function appendOutput(text, isCmd = false) {
                const div = document.createElement('div');
                div.className = isCmd ? 'input-line' : 'output';
                if (isCmd) {
                    div.innerHTML = `<span class="prompt">${prompt}</span><input type="text" value="${text}" readonly class="readonly-input">`;
                } else {
                    div.textContent = text;
                }
                output.appendChild(div);
                outputContainer.scrollTop = outputContainer.scrollHeight;
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const cmd = input.value.trim();
                if (!cmd) return;
                
                const cmdLine = document.createElement('div');
                cmdLine.className = 'input-line';
                cmdLine.innerHTML = `<span class="prompt">${prompt}</span><input type="text" value="${cmd}" readonly class="readonly-input">`;
                output.appendChild(cmdLine);
                
                input.value = '';
                
                try {
                    const res = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'cmd=' + encodeURIComponent(cmd)
                    });
                    if (!res.ok) throw new Error('Network error');
                    const data = await res.text();
                    
                    const outputDiv = document.createElement('div');
                    outputDiv.className = 'output';
                    outputDiv.textContent = data;
                    output.appendChild(outputDiv);
                    
                    outputContainer.scrollTop = outputContainer.scrollHeight;
                } catch (err) {
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'output';
                    errorDiv.textContent = 'Error: ' + err.message;
                    output.appendChild(errorDiv);
                    outputContainer.scrollTop = outputContainer.scrollHeight;
                    console.error('Fetch error:', err);
                }
            });

            terminal.addEventListener('click', () => {
                input.focus();
            });

            // appendOutput('***********************************************************************\n**        THIS TERMINAL WILL CAUSE ERRORS ON MOBILE DEVICES!!        **\n**   PLEASE ENABLE "DESKTOP VIEW" IN ORDER TO SEE EVERTHING RIGHT!!  **\n***********************************************************************\nMicrosoft Windows [Versión 10.0.19045.5854]\n(c) Microsoft Corporation. Todos los derechos reservados.', false);
            input.focus();
            let commandHistory = [];
            let historyIndex = -1;
            
            input.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowUp' && commandHistory.length > 0) {
                    if (historyIndex < commandHistory.length - 1) {
                        historyIndex++;
                        input.value = commandHistory[commandHistory.length - 1 - historyIndex];
                    }
                    e.preventDefault();
                } else if (e.key === 'ArrowDown') {
                    if (historyIndex > 0) {
                        historyIndex--;
                        input.value = commandHistory[commandHistory.length - 1 - historyIndex];
                    } else {
                        historyIndex = -1;
                        input.value = '';
                    }
                    e.preventDefault();
                } else if (e.key === 'Enter') {
                    const cmd = input.value.trim();
                    if (cmd) {
                        commandHistory.push(cmd);
                        historyIndex = -1;
                    }
                }
            });
        });
    </script>
</head>

<body class="bg-black text-white font-sans">

    <?php include 'particles.php'; ?>
    <?php include 'navbar.php'; ?>

    <main class="md:ml-64 min-h-screen">
        <div class="terminal-container">
            <div id="terminal-window" class="terminal-window">
                <div class="terminal-bar">
                    <span class="terminal-dot dot-red"></span>
                    <span class="terminal-dot dot-yellow"></span>
                    <span class="terminal-dot dot-green"></span>
                    <span class="ml-3 text-zinc-400 text-xs select-none">cmd.exe - <?php echo htmlspecialchars(getenv("USERNAME") ?: "user"); ?>@<?php echo htmlspecialchars(gethostname()); ?></span>
                </div>
                <div id="output-container" class="output-container scrollbar">
                    <div id="terminal-output" class="mb-2"></div>
                </div>
                <form id="terminal-form" autocomplete="off" class="input-line">
                    <span class="prompt"><?php echo htmlspecialchars(getenv("USERNAME") ?: "user"); ?>@<?php echo htmlspecialchars(gethostname()); ?>:~$</span>
                    <input id="terminal-input" type="text" autofocus autocomplete="off" spellcheck="false" />
                </form>
            </div>
        </div>
    </main>
</body>
</html>