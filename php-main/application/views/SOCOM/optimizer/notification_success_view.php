<div data-notification
  class="bx--inline-notification bx--inline-notification--success bx--inline-notification--low-contrast ml-auto mr-auto <?php if(isset($class)):?><?=$class;?><?php endif;?>" id="<?php if(isset($id)):?><?=$id;?><?php endif;?>"
  role="alert"> 
  <div class="bx--inline-notification__details">
    <svg focusable="false" preserveAspectRatio="xMidYMid meet" style="will-change: transform;" xmlns="http://www.w3.org/2000/svg" class="bx--inline-notification__icon" width="20" height="20" viewBox="0 0 20 20" aria-hidden="true"><path d="M10,1c-5,0-9,4-9,9s4,9,9,9s9-4,9-9S15,1,10,1z M9.2,5h1.5v7H9.2V5z M10,16c-0.6,0-1-0.4-1-1s0.4-1,1-1	s1,0.4,1,1S10.6,16,10,16z"></path><path d="M9.2,5h1.5v7H9.2V5z M10,16c-0.6,0-1-0.4-1-1s0.4-1,1-1s1,0.4,1,1S10.6,16,10,16z" data-icon-path="inner-path" opacity="0"></path></svg>
    <div class="bx--inline-notification__text-wrapper">
      <p class="bx--inline-notification__title">Notification</p>
      <p class="bx--inline-notification__subtitle"><?=$message;?></p>
    </div>
  </div>
  <?php if (isset($custom_close) && $custom_close): ?>
      <button class="bx--inline-notification__close-button" type="button" onclick=<?= $custom_close; ?>
        aria-label="close">
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bx--inline-notification__close-icon" width="20" height="20" viewBox="0 0 32 32" aria-hidden="true"><path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z"></path></svg>
      </button>
    <?php endif; ?>
    <?php if (isset($close) && $close): ?>
      <button data-notification-btn class="bx--inline-notification__close-button" type="button"
        aria-label="close">
        <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bx--inline-notification__close-icon" width="20" height="20" viewBox="0 0 32 32" aria-hidden="true"><path d="M24 9.4L22.6 8 16 14.6 9.4 8 8 9.4 14.6 16 8 22.6 9.4 24 16 17.4 22.6 24 24 22.6 17.4 16 24 9.4z"></path></svg>
      </button>
    <?php endif; ?>
</div>