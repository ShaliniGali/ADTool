"use strict";

// Ian Zablan
// June 11 2020

  // This function takes in amount of time in seconds, format in minutes or seconds, expiry message,
  // and the parent element where the timer will be appended.
  function showTimer(startTime, format, expiryMessage, parentElement){
    let timerInterval;
    let timerUI = "<div class='circle-wrap'>" +
                    "<div class='circle'>" +
                      "<div class='mask full'>" +
                        "<div class='fill'></div>" +
                      "</div>" +
                      "<div class='mask half'>" +
                        "<div class='fill'></div>" +
                      "</div>" +
                      "<div class='inside-circle timer'>" +
                      "</div>" +
                    "</div>" +
                  "</div>";
    parentElement.append(timerUI);
    let timerClock = parentElement.find(".timer");
    let timerAnimation = parentElement.find(".circle-wrap .circle .mask.full, .circle-wrap .circle .fill");
    let minutes;
    let seconds;
    
    timerAnimation.css({"animation": "fill linear " + startTime + "s","transform": "rotate(360deg)"});
    timerInterval = setInterval(function(){
      startTime--
      minutes = startTime / 60;
      seconds = startTime % 60;
      if (seconds < 10)
        seconds = "0"+seconds;

      if (format == "minutes") {
        timerClock.html(sanitizeHtml(Math.trunc(minutes) + ":" + seconds,{allowedTags:false, allowedAttributes:false}));
	  } else {
        timerClock.html(sanitizeHtml(startTime),{allowedTags:false, allowedAttributes:false});
	  }

    if (startTime <= 0){
        clearInterval(timerInterval);
        timerClock.css({"font-size":"1em"});
        timerClock.html(sanitizeHtml(expiryMessage, { allowedAttributes:false, allowedTags:false,}));
        }
      
    }, 1000);
  };

// Expose class in window in order to make it reachable in Jest + jsdom.
if (!window._rb) window._rb = {};
window._rb.showTimer = showTimer;