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
 *
 *
 * TODO
 * - visible notice on ajax error
 */

(function() {

  // *************************
  // DEPLOYMENT TRIGGERS
  // *************************
  // // deploy buttons that call the url with alert or without alert by ajax and prevent being routed
  var ajaxButtons = document.querySelectorAll("#crabify-deploy-live, #crabify-deploy-preview");
  for(var i = 0; i<ajaxButtons.length; i++) {
    ajaxButtons[i].onclick = function(e) {
      // e.preventDefault();
      if(confirm("Soll das deployment auf netlify getriggert werden? (" + e.target.href +")")) {
        e.preventDefault();
        callAjaxUrl(e.target.href);
        console.log('deploy triggered');
      } else {
        console.log('aborted');
        return false;
      }
    };
  }

  // function that makes ajax calls to netlify deploy triggers
  function callAjaxUrl(url) {
    fetch(url, {
      method: 'post',
    }).then(() => console.log('yay')).catch(error => console.log('error is', error));

    // var xmlHttp = new XMLHttpRequest();
    // xmlHttp.onreadystatechange = function() {
    //   if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
    //     // console.log('ajax Call succeeded');
    //   }
    // }
    // xmlHttp.open("GET", url, true); // true for asynchronous
    // xmlHttp.send(null);
  }


  // *************************
  // STATUS
  // *************************
  // everything that needs classes (live-started, live-failed, live-succeeded) of status Updates
  var liveStatusElements = document.querySelectorAll("#crabify-deploy-live, #nav-crabify");
  var previewStatusElements = document.querySelectorAll("#crabify-deploy-preview, #nav-crabify, #crabify-entry-preview");
  // console.log(liveStatusElements, previewStatusElements);
  var liveStati = [
    'live-status-started',
    'live-status-error',
    'live-status-failed',
    'live-status-succeeded',
  ];
  var previewStati = [
    'preview-status-started',
    'preview-status-error',
    'preview-status-failed',
    'preview-status-succeeded'
  ];


  // status variables
  var liveStatus = '';
  var previewStatus = '';
  var oldLiveStatus = '';
  var oldPreviewStatus = '';

  // interval that checks statusses
  function getStatus() {
    getDeployStatus();
  }

  getStatus(); // trigger directly
  setInterval(function() {
    getStatus();
  }, 10000); // trigger every 3 seconds

  // functions that retrieve the status
  function getDeployStatus() {
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() {
      if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
        // console.log(xmlHttp.responseText);
        try {
           statuses = JSON.parse(xmlHttp.responseText);
           // console.log(statuses);
           liveStatus = statuses.live;
           previewStatus = statuses.preview;
           setStatusClasses();
        } catch(e) {
        }
      }
    }
    xmlHttp.open("GET", '/actions/cra-by-fy/deploy/deploy-status', true); // true for asynchronous
    xmlHttp.send(null);
  }

  // // function that adds and removes classes to all in querySelector
  function setStatusClasses() {
    if (oldLiveStatus !== liveStatus) {
      setClasses(liveStatusElements, 'live-status-'+liveStatus, liveStati);
      oldLiveStatus = liveStatus;
    }
    if (oldPreviewStatus !== previewStatus) {
      setClasses(previewStatusElements, 'preview-status-'+previewStatus, previewStati);
      oldPreviewStatus= previewStatus;
    }
  }

  function setClasses(elements, classes, removeClasses) {
    console.log(elements, classes);
    for(var i = 0; i<elements.length; i++) {
      for (var j = 0; j < removeClasses.length; j++) {
        elements[i].classList.remove(removeClasses[j]);
      }
      elements[i].classList.add(classes);
    }
  }
})();
