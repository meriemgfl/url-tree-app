// electron-shell/preload.js
const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('electronAPI', {
    onAddUrlModal: (callback) => ipcRenderer.on('open-add-url-modal', callback),
});