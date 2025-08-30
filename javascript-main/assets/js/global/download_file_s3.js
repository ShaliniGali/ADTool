"use strict";

// Function currently not in use, vulnerability fixes applied but untested.
function download_file(encry_file,filename){
    $.post("/Upload/download_file_from_s3",{encry_file:encry_file,filename:filename,rhombus_token:rhombuscookie()}, function(data1, status){
      try {
        let data = JSON.parse(data1);
        if(data.status == 'ERROR'){
          return
        }
      } catch (error) {}
      let element = document.createElement('a');

	  let sanitizedFile = sanitizeHtml('data:text/plain;base64,' + data1, {
		allowedTags: false,
		allowedAttributes: false,
		allowedSchemes: [ 'data', 'http', 'https']
	  });

      element.setAttribute('href', sanitizedFile);
      element.setAttribute('download', filename);
      element.style.display = 'none';
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);   
    });
  }

// Expose class in window in order to make it reachable in Jest + jsdom.
if (!window._rb) window._rb = {};
window._rb.download_file = download_file;
