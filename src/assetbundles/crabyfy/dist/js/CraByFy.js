/**
 * CraByFy plugin for Craft CMS
 *
 * CraByFy JS
 *
 * @author    Dunckelfeld
 * @copyright Copyright (c) 2018 Dunckelfeld
 * @link      dunckelfeld.de
 * @package   CraByFy
 * @since     1.0.0
 */

(function() {
  var previewButton = document.getElementById("nav-preview-url");
  var previewButtonLink = document.querySelector("#nav-preview-url a");

  var deployButton = document.getElementById("nav-live-deploy");
  var deployButtonLink = document.querySelector("#nav-live-deploy a");

  var liveStatus = '';
  var previewStatus = '';

  var liveTriggered = false;
  var prviewTriggered = false;

  deployButton.onclick = function(e) {
    e.preventDefault();
    var url = deployButtonLink.href;
    // confirm("really deploy?");
    confirm("trigger " + url);
    callDeployUrl(url);
    liveTriggered = true;
    return false;
  };

  function callDeployUrl(url) {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
            setLiveDeployButton(xmlHttp.responseText, deployButtonLink, 'live');
    }
    xmlHttp.open("GET", url, true); // true for asynchronous
    xmlHttp.send(null);
  }

  function getLiveDeployStatus() {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
            setLiveDeployButton(xmlHttp.responseText, deployButtonLink, 'live');
    }
    xmlHttp.open("GET", '/actions/cra-by-fy/deploy/live-deploy-status', true); // true for asynchronous
    xmlHttp.send(null);
  }

  setInterval(function() {
    getLiveDeployStatus();
  }, 3000);

  function getPreviewDeployStatus() {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
            setPreviewDeployButton(xmlHttp.responseText, previewButtonLink, 'preview');
    }
    xmlHttp.open("GET", '/actions/cra-by-fy/deploy/preview-deploy-status', true); // true for asynchronous
    xmlHttp.send(null);
  }

  setInterval(function() {
    getPreviewDeployStatus();
  }, 3000);

  function setLiveDeployButton(response, button, deployType) {
    console.log(response, button, deployType);
    if(liveStatus != response ) {
      setButtonClass(response, button);
      liveStatus = response;
    }

    if(response == 'error' || response == 'failed' || response == 'succeeded') {
      liveTriggered = false;
    }
  }

  function setPreviewDeployButton(response, button, deployType) {
    console.log(response, button, deployType);
    if(previewStatus != response ) {
      setButtonClass(response, button);
      previewStatus = response;
    }

    if(response == 'error' || response == 'failed' || response == 'succeeded') {
      prviewTriggered = false;
    }
  }

  function setButtonClass(response,  button) {
    if(response == 'started') {
      if (!deployButtonLink.classList.contains('started')) {
        button.classList.remove('error');
        button.classList.remove('succeeded');
        button.classList.remove('failed');

        if (!button.classList.contains('started')) {
          button.classList.add('started');
        }
      }
    } else if(response == 'succeeded') {
      button.classList.remove('error');
      button.classList.remove('started');
      button.classList.remove('failed');

      if (!button.classList.contains('succeeded')) {
        button.classList.add('succeeded');
      }
    } else if(response == 'failed'){
      button.classList.remove('succeeded');
      button.classList.remove('started');
      button.classList.remove('error');

      if (!button.classList.contains('failed')) {
        button.classList.add('failed');
        // alert('Live Deployment did not pass :(');
        setTimeout(function() {
          button.classList.remove('failed');
        }, 10000);
      }
    } else {
      button.classList.remove('succeeded');
      button.classList.remove('started');
      button.classList.remove('error');

      if (!button.classList.contains('error')) {
        button.classList.add('error');
        // alert('Live Deployment could not be liveTriggered :(');
        setTimeout(function() {
          button.classList.remove('error');
        }, 10000);
      }
    }
  }


})();
