<div class="slider bx--form-item d-none">
  <div class="bx--tile rounded">
  <label class="bx--label ">
  Selected Date:
  <text class="selected_date"></text>
  </label>
  <div class="bx--slider-container">
      <label id="slider-input-box_bottom-range-label text-muted" class="bx--slider__range-label min-label mx-1"></label>
      <?php
        $data = ['type'=>'ghost','id'=>'downYear','label'=>'<<','buttonClass'=>'bx--btn--sm px-1','formItem'=>false,'action'=>'onClick="changeTime(0,-1)"'];
        $this->load->view('components/button',$data); 
      ?>
      <?php
        $data = ['type'=>'ghost','id'=>'downMonth','label'=>'<','buttonClass'=>'bx--btn--sm px-1','formItem'=>false,'action'=>'onClick="changeTime(-1,0)"'];
        $this->load->view('components/button',$data); 
      ?>
      <input aria-label="slider" id="slider" type="range" step="1" min="0" max="100" value="25">
      
      <?php
        $data = ['type'=>'ghost','id'=>'upMonth','label'=>'>','buttonClass'=>'bx--btn--sm px-1','formItem'=>false,'action'=>'onClick="changeTime(1,0)"'];
        $this->load->view('components/button',$data); 
      ?>
      <?php
        $data = ['type'=>'ghost','id'=>'upYear','label'=>'>>','buttonClass'=>'bx--btn--sm px-1','formItem'=>false,'action'=>'onClick="changeTime(0,1)"'];
        $this->load->view('components/button',$data); 
      ?>  
      <label id="slider-input-box_top-range-label text-muted" class="bx--slider__range-label max-label mx-1"></label>
    </div>
    </div>
  </div>
</div>