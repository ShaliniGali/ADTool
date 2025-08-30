<div class="whatif-loader loading-indicator align-items-center justify-content-center flex-column">
    <div id="net_speed_box"></div>
    <div class="lds-ellipsis">
      <div></div><div></div><div></div><div></div>
    </div>
    <div id="loading-icon-text"></div>
</div>

<style>
  .whatif-loader {
    background-color: rgba(0, 0, 0, 0.8);
    position: absolute;
    top: 0;
    left: 0;
    z-index: 111;
    display: flex;
    height: 150% !important;
    width: 100% !important;
  }

  .whatif-loader .lds-ellipsis {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 80px;
  }
  #loading-icon-text {
    display: flex;
    flex-direction: column;
    width: 400px;
    height: 105px;
    overflow: auto;
    align-items:center;
    overflow-anchor: none;
  }
  #loading-icon-text p {
    font-size: 24px;
    font-weight: 800;
  }
  #net_speed_box {
    font-size:24px;
    font-weight: 800;
    color: white;
  }
</style>