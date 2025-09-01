<div id="view-history-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <button class="modal-close-btn" id="close-history-modal">&times;</button>
        <div id="editor-historical-graph-container" class="p-3"></div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
    padding: 20px;
    overflow: auto;
}

.modal-content {
    background: white;
    padding: 20px;
    width: 80vw;
    max-width: 1600px;
    max-height: 80vh;
    border-radius: 8px;
    position: relative;
    box-shadow: 0 0 10px rgba(0,0,0,0.25);
    overflow-y: auto;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 14px;
    color: #222;
    line-height: 1.5;
}

.modal-close-btn {
    position: absolute;
    top: 10px;
    right: 14px;
    font-size: 24px;
    background: none;
    border: none;
    cursor: pointer;
}
</style>