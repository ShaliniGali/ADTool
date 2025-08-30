<style>
	    .irs-line {
        height: 5px !important;
    }
    .irs-bar {
        background-color:#1a7cfa !important;
        height: 5px !important;
    }
    .irs-handle > i {
        background-color:#1a7cfa !important;
        height: 65% !important;
    }
    .irs-to {
        background-color:#1a7cfa !important;
    }
    .irs-to:before {
        border-top-color:#1a7cfa !important;
    }
    .irs-from{
        background-color:#1a7cfa !important;
    }
    .irs-from:before{
        border-top-color:#1a7cfa !important;
    }
    .irs-single {
        background-color:#1a7cfa !important;
    }
    .irs-single:before {
        border-top-color:#1a7cfa !important;
    }
    .round {
  position: relative;
}

.round label {
  background-color: #fff;
  border: 1px solid #ccc;
  border-radius: 50%;
  cursor: pointer;
  height: 20px;
  left: 0;
  position: absolute;
  top: 0;
  width: 20px;
}

.round label:after {
  border: 2px solid #fff;
  border-top: none;
  border-right: none;
  content: "";
  height: 6px;
  left: 4px;
  opacity: 0;
  position: absolute;
  top: 5px;
  transform: rotate(-45deg);
  width: 12px;
}

.round input[type="checkbox"] {
  visibility: hidden;
}

.round input[type="checkbox"]:checked + label {
    background-color: #3b4752;
    border-color: #3b4752;
}

.round input[type="checkbox"]:checked + label:after {
  opacity: 1;
}
</style>


<div class="d-flex flex-column h-100 mt-5 mb-5">
	<div class="d-flex flex-column ml-auto mr-auto w-75 card-body">
		<div class="d-flex flex-row">
			<h1 class="mb-5">Weights and StoRM Scores</h1>
		</div>
		
		<ul data-progress data-progress-current class="bx--progress mb-2">
			<li class="bx--progress-step bx--progress-step--current">
				<button type="button" class="bx--progress-step-button bx--progress-step-button--clickable" tabindex="-1" title="First step" index="0">
					<span class="bx--assistive-text">Current</span>
					<svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16" height="16" viewBox="0 0 32 32" aria-hidden="true">
						<path d="M23.7642 6.8593l1.2851-1.5315A13.976 13.976 0 0020.8672 2.887l-.6836 1.8776A11.9729 11.9729 0 0123.7642 6.8593zM27.81 14l1.9677-.4128A13.8888 13.8888 0 0028.14 9.0457L26.4087 10A12.52 12.52 0 0127.81 14zM20.1836 27.2354l.6836 1.8776a13.976 13.976 0 004.1821-2.4408l-1.2851-1.5315A11.9729 11.9729 0 0120.1836 27.2354zM26.4087 22L28.14 23a14.14 14.14 0 001.6382-4.5872L27.81 18.0659A12.1519 12.1519 0 0126.4087 22zM16 30V2a14 14 0 000 28z"></path><title></title>
					</svg>
					<div class="bx--progress-text">
						<p class="bx--progress-label">Guidance</p>
					</div>
					<span class="bx--progress-line"></span>
				</button>
			</li>
			<li class="bx--progress-step bx--progress-step--incomplete">
				<button type="button" class="bx--progress-step-button bx--progress-step-button--unclickable" tabindex="-1" title="Second step with tooltip" index="1">
					<span class="bx--assistive-text">Incomplete</span>
					<svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16" height="16" viewBox="0 0 32 32" aria-hidden="true">
						<path d="M7.7 4.7a14.7 14.7 0 00-3 3.1L6.3 9A13.26 13.26 0 018.9 6.3zM4.6 12.3l-1.9-.6A12.51 12.51 0 002 16H4A11.48 11.48 0 014.6 12.3zM2.7 20.4a14.4 14.4 0 002 3.9l1.6-1.2a12.89 12.89 0 01-1.7-3.3zM7.8 27.3a14.4 14.4 0 003.9 2l.6-1.9A12.89 12.89 0 019 25.7zM11.7 2.7l.6 1.9A11.48 11.48 0 0116 4V2A12.51 12.51 0 0011.7 2.7zM24.2 27.3a15.18 15.18 0 003.1-3.1L25.7 23A11.53 11.53 0 0123 25.7zM27.4 19.7l1.9.6A15.47 15.47 0 0030 16H28A11.48 11.48 0 0127.4 19.7zM29.2 11.6a14.4 14.4 0 00-2-3.9L25.6 8.9a12.89 12.89 0 011.7 3.3zM24.1 4.6a14.4 14.4 0 00-3.9-2l-.6 1.9a12.89 12.89 0 013.3 1.7zM20.3 29.3l-.6-1.9A11.48 11.48 0 0116 28v2A21.42 21.42 0 0020.3 29.3z"></path><title></title>
					</svg>
					<div class="bx--progress-text">
						<p class="bx--progress-label">POM</p>
					</div>
					<span class="bx--progress-line"></span>
				</button>
			</li>
			<li class="bx--progress-step bx--progress-step--incomplete">
				<button type="button" class="bx--progress-step-button bx--progress-step-button--unclickable" tabindex="-1" title="Second step with tooltip" index="1">
					<span class="bx--assistive-text">Incomplete</span>
					<svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" width="16" height="16" viewBox="0 0 32 32" aria-hidden="true">
						<path d="M7.7 4.7a14.7 14.7 0 00-3 3.1L6.3 9A13.26 13.26 0 018.9 6.3zM4.6 12.3l-1.9-.6A12.51 12.51 0 002 16H4A11.48 11.48 0 014.6 12.3zM2.7 20.4a14.4 14.4 0 002 3.9l1.6-1.2a12.89 12.89 0 01-1.7-3.3zM7.8 27.3a14.4 14.4 0 003.9 2l.6-1.9A12.89 12.89 0 019 25.7zM11.7 2.7l.6 1.9A11.48 11.48 0 0116 4V2A12.51 12.51 0 0011.7 2.7zM24.2 27.3a15.18 15.18 0 003.1-3.1L25.7 23A11.53 11.53 0 0123 25.7zM27.4 19.7l1.9.6A15.47 15.47 0 0030 16H28A11.48 11.48 0 0127.4 19.7zM29.2 11.6a14.4 14.4 0 00-2-3.9L25.6 8.9a12.89 12.89 0 011.7 3.3zM24.1 4.6a14.4 14.4 0 00-3.9-2l-.6 1.9a12.89 12.89 0 013.3 1.7zM20.3 29.3l-.6-1.9A11.48 11.48 0 0116 28v2A21.42 21.42 0 0020.3 29.3z"></path><title></title>
					</svg>
					<div class="bx--progress-text">
						<p class="bx--progress-label">StoRM</p>
					</div>
					<span class="bx--progress-line"></span>
				</button>
			</li>
		</ul>
	</div>

	<div class="d-flex flex-column ml-auto mr-auto w-75 pl-3 mb-5">
		<div class="bx--form-item w-50">
			<label for="text-input-title" class="bx--label">Title</label>
			<div class="bx--form__helper-text"></div>
			<input id="text-input-title" type="text"
			class="bx--text-input" name="title" value="<?= set_value('title'); ?>"
			placeholder="Weights Title">
		</div>
	</div>

 	<div class="d-flex flex-column ml-auto mr-auto w-75 mb-5 card-body border">
		<div class="neumorphism mb-auto mt-3">
			<div class="w-100 p-4">

				<div class="bx--tab-content">
					<?php $this->load->view('SOCOM/weights/weights_tab_panel', [
						'tab' => 'guidance',
						'hidden' => false
					]) ;?>
					<?php $this->load->view('SOCOM/weights/weights_tab_panel', [
						'tab' => 'pom',
						'hidden' => true
					]) ;?>
					<?php $this->load->view('SOCOM/weights/storm_panel', [
						'tab' => 'storm',
						'hidden' => true
					]) ;?>
				</div>
			</div>
		</div>
	</div>
	
</div>
