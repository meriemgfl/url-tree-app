// electron-shell/main.js
const { app, BrowserWindow } = require('electron');
const { spawn } = require('child_process');
const path = require('path');

let phpServer;
let mainWindow;
//C:\php\php.exe
const PHP_PATH = 'C:\\php\\php.exe'; 
const PHP_INI_DIR = 'C:\\php';

function startPhpServer() {
    const projectRoot = path.join(__dirname, '..'); // points to url-tree-app/

    phpServer = spawn(PHP_PATH, ['artisan', 'serve', '--port=8000'], {
        cwd: projectRoot,

        env: {
            ...process.env,
            PHPRC: PHP_INI_DIR,
        },
        // shell: true,
    });

    phpServer.stdout.on('data', (data) => {
        console.log(`[php] ${data}`);
    });

    phpServer.stderr.on('data', (data) => {
        console.error(`[php error] ${data}`);
    });
}

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1100,
        height: 750,
        minWidth: 800,
        minHeight: 600,
        title: 'URL Tree',
        webPreferences: {
            contextIsolation: true,
            preload: path.join(__dirname, 'preload.js'),
        },
    });

    // Give PHP a moment to boot before loading the URL
    setTimeout(() => {
        mainWindow.loadURL('http://localhost:8000');
    }, 1000);

    mainWindow.on('closed', () => {
        mainWindow = null;
    });
}

app.whenReady().then(() => {
    startPhpServer();
    createWindow();
});

app.on('window-all-closed', () => {
    if (phpServer) phpServer.kill();
    if (process.platform !== 'darwin') app.quit();
});

app.on('before-quit', () => {
    if (phpServer) phpServer.kill();
});

app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
});