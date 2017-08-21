var testcase_items = new Array();
var active = false;
var empty = true;
var tab_id = null;
console.log('init');
chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
  console.log(request);
  if (request.action == "append") {
    testcase_items[testcase_items.length] = request.obj;
    empty = false;
    sendResponse({});
  }
  if (request.action == "poke") {
    testcase_items[testcase_items.length - 1] = request.obj;
    sendResponse({});
  }
  if (request.action == "get_status") {
    sendResponse({'active': active, 'empty': empty});
  }
  if (request.action == "start") {
  	if(!active) {
  	    active = true;
  	    empty = true;
  	    testcase_items = new Array();
  	    tab_id = request.recorded_tab;
  	    chrome.tabs.update(tab_id, {url: request.start_url}, function(tab) {
          alert("\n▹   click OK to start recording your actions\n▹   right-click for shortcuts to TagUI steps\n");
          chrome.tabs.sendMessage(tab_id, {action: "open", 'url': request.start_url});
          sendResponse({start: true});
  	    });
  	}
  }
  if (request.action == "stop") {
    active = false;
    chrome.tabs.sendMessage(tab_id, {action: "stop"});
    sendResponse({});
  }
  if (request.action == "get_items") {
	sendResponse({'items': testcase_items});
  }
});