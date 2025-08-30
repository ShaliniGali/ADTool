<style>
.bx--toggle-input__label .bx--toggle__switch {
    margin-top: 0.5rem;
}

.bx--toggle-input:checked+.bx--toggle-input__label>.bx--toggle__switch::before {
    background-color: #0f62fe !important;
}

#user-nav-item {
  left: inherit !important;
  right: 1em;
  padding: 0;
  min-width: 10em;
  background: #333333 !important;
  color: #000 !important;
}

#user-nav-item .bx--tooltip__caret {
  border-bottom: 0.42969rem solid #464646 !important;
}

#user-nav-item .bx--tooltip__caret {
  left: 50%; /* position of carat at top of menu */
}

#user-nav-item li:hover {
  background-color: #252525;
}

.notional-banner{
    width: 100%;
    background: #008000;
    text-align: center;
    padding: 8px;
    color: white;
}

.nav-item {
  display: flex;
  align-items: center;
}

.notif-open {
  color: #F5F5F7;
  background-color: black;
  border-bottom: 2px solid royalblue;
}
</style>

<?php
  if($this->session->has_userdata('tooltip_toggle_flag')){
      // $tooltip_toggle_flag = $this->session->userdata['tooltip_toggle_flag'];
      $tooltip_toggle_flag = 'false';
  }
  else{
      $this->session->set_userdata('tooltip_toggle_flag', 'true');
      $tooltip_toggle_flag = 'false';
  }

  $js_files = array();
  $CI = &get_instance();
  $js_files['jquery'] = ['jquery.min.js','global'];

  $CI->load->library('RB_js_css');
  $CI->rb_js_css->compress($js_files);
?>

<!-- <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top" style="background-color: #262626 !important;"> -->
<div class="d-flex flex-column fixed-top" style="position: sticky !important">
<div class="header navbar navbar-expand-lg" style="background:#333333;">
<div class="d-flex flex-row w-100">
  <button class="bx--header__menu-trigger bx--header__action" aria-label="Open menu" title="Open menu"
      data-navigation-menu-panel-label-expand="Open menu" data-navigation-menu-panel-label-collapse="Close menu"
      data-navigation-menu-target="#navigation-menu-ni7sbqqqqw">
      <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" aria-hidden="true" class="bx--navigation-menu-panel-collapse-icon" width="20" height="20" viewBox="0 0 32 32"><path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z"></path></svg>
      <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" aria-hidden="true" class="bx--navigation-menu-panel-expand-icon" width="20" height="20" viewBox="0 0 20 20"><path d="M2 14.8H18V16H2zM2 11.2H18V12.399999999999999H2zM2 7.6H18V8.799999999999999H2zM2 4H18V5.2H2z"></path></svg>
    </button>

  <div class="cursor" onclick="window.location.href='<?= base_url(); ?><?=(P1_FLAG && (RHOMBUS_ENVIRONMENT === 'siprdevelopment' || RHOMBUS_ENVIRONMENT === 'siprproduction'))? 'kc_tiles' : 'home' ?>'">
    <div class="d-flex flex-row" >
      <div class="brand inline mt-2 ml-4" style="width:80px !important;">
        <img src="/assets/images/Logos/guardian_logo_70x70.png" alt="logo" data-src="/assets/images/Logos/guardian_logo_70x70.png" data-src-retina="/assets/images/Logos/guardian_logo_70x70.png" width="30">
      </div>
      <div class="p-auto mb-auto mt-3" style="color: #F5F5F7;font-size: 17px;position: relative;right: 2.2em;">Guardian</div>
      <div class="brand inline mt-2 ml-4" style="width:80px !important;">
        <img src="/assets/images/socom.png" alt="logo" data-src="/assets/images/Logos/guardian_logo_70x70.png" data-src-retina="/assets/images/Logos/guardian_logo_70x70.png" width="30">
      </div>
    </div>
  </div>
  <?php if ($this->session->userdata('logged_in')) :   ?>
  <div class="navbar-collapse">
    <ul class="navbar-nav ml-auto mt-2 mt-lg-0 list-group-horizontal">
      <div class="nav-item-right-group d-flex ml-auto">
        <li class="nav-item mr-4 pr-3">
          <div id='navbar-guide' class="bx--form-item d-none">
            <input class="bx--toggle-input bx--toggle-input--small" id="guide-toggle" type="checkbox" 
            <?php if($tooltip_toggle_flag=='true'): ?>
              unchecked
            <?php endif; ?>
            >
            <label class="bx--toggle-input__label" for="guide-toggle"
              aria-label="example toggle with state indicator text" style="color:white;">
              <span class="bx--toggle__switch">
                <svg class="bx--toggle__check" width="6px" height="5px" viewBox="0 0 6 5">
                  <path d="M2.2 2.7L5 0 6 1 2.2 5 0 2.7 1 1.5z" />
                </svg>
                <span class="bx--toggle__text--off" aria-hidden="true">Off</span>
                <span class="bx--toggle__text--on" aria-hidden="true">On</span>
              </span>
            </label>
          </div>
        </li>
        <li class="nav-item mr-4" style="margin:auto;">
          <div id="ser-nav-item-label">
            <button aria-expanded="false" aria-labelledby="ser-nav-item-label" data-tooltip-trigger data-tooltip-target="#user-nav-item"
              class="bx--tooltip__trigger" aria-controls="ser-nav-item" aria-haspopup="true" title="Profile">
              <svg id="icon" xmlns="http://www.w3.org/2000/svg" style="fill:#F5F5F7" width="20" height="18" viewBox="0 0 32 32">
                <path d="M16,8a5,5,0,1,0,5,5A5,5,0,0,0,16,8Z" transform="translate(0 0)"/>
                <path d="M16,2A14,14,0,1,0,30,16,14.0158,14.0158,0,0,0,16,2Zm7.9925,22.9258A5.0016,5.0016,0,0,0,19,20H13a5.0016,5.0016,0,0,0-4.9925,4.9258,12,12,0,1,1,15.985,0Z" transform="translate(0 0)"/>
                <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" style="fill:none" class="cls-1" width="32" height="32"/>
              </svg>
            </button>
          </div>
          <div id="user-nav-item" aria-hidden="true" data-floating-menu-direction="bottom" class="bx--tooltip">
            <span class="bx--tooltip__caret"></span>
            <div class="bx--tooltip__content bx--tooltip__content" style="width:10em" tabindex="-1" role="dialog" aria-labelledby="ser-nav-item-heading" aria-describedby="ser-nav-item-body">
              <ul>
                <li class="py-1">
                  <a class="nav-link" style="color: #F5F5F7" href="/dashboard"><i style="color: #F5F5F7" class="fa fa-cog mr-1"></i> Dashboard</a>
                </li>
                <!-- Commented for removal of features - FSWHAT-1401 -->
                <!-- <li class="py-1">
                  <a class="nav-link" style="color: #F5F5F7" href="/log"><i style="color: #F5F5F7" class="fa fa-cog mr-1"></i>Log Panel</a>
                </li>
                <li class="py-1">
                  <a class="nav-link" style="color: #F5F5F7" href="/mission"><i style="color: #F5F5F7" class="fa fa-cog mr-1"></i>Mission Panel</a>
                </li> -->
                <li class="py-1">
                  <a class="nav-link" style="color: #F5F5F7" href="<?php echo base_url()?>login/logout"><i class="fas fa-sign-out-alt mr-1"></i> Logout</a>
                </li>
              </ul>
            </div>
            <span tabindex="0"></span>
          </div>
        </li>
      </div>
    </ul>
  </div>
  <?php else : ?>
    <div class="custom-control custom-switch nav-link d-none">
      <input type="checkbox" class="custom-control-input" id="darkSwitch" />
      <label class="custom-control-label" for="darkSwitch">Dark Mode</label>
    </div>
  <?php endif ?>
</div>
</div>
</div>

<script>

var global_tooltip_toggle = <?= $tooltip_toggle_flag; ?>;

document.getElementById("guide-toggle").addEventListener("change", function() {
    if(this.checked) {
		$.get(window.location.origin+'/Tutorial/setTooltipData' , function (result) {
      global_tooltip_toggle = true;
		});
    }else{
		$.get(window.location.origin+'/Tutorial/unsetTooltipData' , function (result) {
      global_tooltip_toggle = false;
		})
	}
});

</script>
