<div class="bx--grid">
 <ul data-progress data-progress-current class="bx--progress">
    <?php 


        function get_svg_icon($status)
        {
            
            if($status == "bx--progress-step--incomplete")
            {
                return '<svg><path d="M8 1C4.1 1 1 4.1 1 8s3.1 7 7 7 7-3.1 7-7-3.1-7-7-7zm0 13c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6z"></path></svg>';
            }else if($status == "bx--progress-step--current")
            {
                return '<svg><path d="M 7, 7 m -7, 0 a 7,7 0 1,0 14,0 a 7,7 0 1,0 -14,0" ></path></svg>';
            }else if($status == "bx--progress-step--complete")
            {
                return '<svg><path d="M 7, 7 m -7, 0 a 7,7 0 1,0 14,0 a 7,7 0 1,0 -14,0" ></path></svg>';
            }
        }


        for($i=0;$i<=count($sections)-1;$i++)
        {
            $var = trim($sections[$i],".php");
            echo '<li class="bx--progress-step bx--progress-step--incomplete" id='.$var.'>
                <div id="progress-icon_'.$var.'>
                <svg><path d="M8 1C4.1 1 1 4.1 1 8s3.1 7 7 7 7-3.1 7-7-3.1-7-7-7zm0 13c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6z"></path></svg>
                </div>
                <p tabindex="0" class="bx--progress-label">
                    '.$var.'
                </p>
                <div role="tooltip" data-floating-menu-direction="bottom" class="bx--tooltip" data-avoid-focus-on-open>
                <span class="bx--tooltip__caret"></span>
                <p class="bx--tooltip__text">Overflow Ex.1</p>
                </div>
                <span class="bx--progress-line"></span>
                </li> ';
        }
        ?>
  </ul>
</div>