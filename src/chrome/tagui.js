// --------------------------------------------------------------------------------
// CasperRenderer - class to render recorded steps to TA.Gui automation flow format
// --------------------------------------------------------------------------------

if (typeof(EventTypes) == "undefined") {
  EventTypes = {};
}

EventTypes.OpenUrl = 0;
EventTypes.Click = 1;
EventTypes.Change = 2;
EventTypes.Comment = 3;
EventTypes.Submit = 4;
EventTypes.CheckPageTitle = 5;
EventTypes.CheckPageLocation = 6;
EventTypes.CheckTextPresent = 7;
EventTypes.CheckValue = 8;
EventTypes.CheckValueContains = 9;
EventTypes.CheckText = 10;
EventTypes.CheckHref = 11;
EventTypes.CheckEnabled = 12;
EventTypes.CheckDisabled = 13;
EventTypes.CheckSelectValue = 14;
EventTypes.CheckSelectOptions = 15;
EventTypes.CheckImageSrc = 16;
EventTypes.PageLoad = 17;
EventTypes.ScreenShot = 18;
EventTypes.MouseDown = 19;
EventTypes.MouseUp = 20;
EventTypes.MouseDrag = 21;
EventTypes.MouseDrop = 22;
EventTypes.KeyPress = 23;

function CasperRenderer(document) {
  this.document = document;
  this.title = "Automation Flow";
  this.items = null;
  this.history = new Array();
  this.last_events = new Array();
  this.screen_id = 1;
  this.unamed_element_id = 1;
}

CasperRenderer.prototype.text = function(txt) {
  // todo: long lines
  this.document.writeln(txt);
}

CasperRenderer.prototype.stmt = function(text, indent) {
//  if(indent==undefined) indent = 1;
  if(indent==undefined) indent = 0;
  var output = (new Array(4*indent)).join(" ") + text;
  this.document.writeln(output);
}

CasperRenderer.prototype.cont = function(text) {
  this.document.writeln("    ... " + text);
}

CasperRenderer.prototype.pyout = function(text) {
  this.document.writeln("    " + text);
}

CasperRenderer.prototype.pyrepr = function(text, escape) {
  // todo: handle non--strings & quoting
  // There should a more eloquent way of doing this but by doing the escaping before adding the string quotes prevents the string quotes from accidentally getting escaped creating a syntax error in the output code.
	var s = text;
	if (escape) s = s.replace(/(['"])/g, "\\$1");
//	var s = "'" + s + "'"; 
  return s;
}

CasperRenderer.prototype.space = function() {
  this.document.write("\n");
}

CasperRenderer.prototype.regexp_escape = function(text) {
  return text.replace(/[-[\]{}()*+?.,\\^$|#\s\/]/g, "\\$&");
};

CasperRenderer.prototype.cleanStringForXpath = function(str, escape)  {
    var parts  = str.match(/[^'"]+|['"]/g);
    parts = parts.map(function(part){
        if (part === "'")  {
            return '"\'"'; // output "'"
        }

        if (part === '"') {
            return "'\"'"; // output '"'
        }
//        return "'" + part + "'";
        return part;
    });
    var xpath = '';
    if(parts.length>1) {
      xpath = "concat(" + parts.join(",") + ")";
    } else {
      xpath = parts[0];
    }
    if(escape) xpath = xpath.replace(/(["])/g, "\\$1");
    return xpath;
}

var d = {};
d[EventTypes.OpenUrl] = "openUrl";
d[EventTypes.Click] = "click";
// d[EventTypes.Change] = "change";
d[EventTypes.Comment] = "comment";
d[EventTypes.Submit] = "submit";
d[EventTypes.CheckPageTitle] = "checkPageTitle";
d[EventTypes.CheckPageLocation] = "checkPageLocation";
d[EventTypes.CheckTextPresent] = "checkTextPresent";
d[EventTypes.CheckValue] = "checkValue";
d[EventTypes.CheckText] = "checkText";
d[EventTypes.CheckHref] = "checkHref";
d[EventTypes.CheckEnabled] = "checkEnabled";
d[EventTypes.CheckDisabled] = "checkDisabled";
d[EventTypes.CheckSelectValue] = "checkSelectValue";
d[EventTypes.CheckSelectOptions] = "checkSelectOptions";
d[EventTypes.CheckImageSrc] = "checkImageSrc";
d[EventTypes.PageLoad] = "pageLoad";
d[EventTypes.ScreenShot] = "screenShot";
// d[EventTypes.MouseDown] = "mousedown";
// d[EventTypes.MouseUp] = "mouseup";
d[EventTypes.MouseDrag] = "mousedrag";
d[EventTypes.KeyPress] = "keypress";

CasperRenderer.prototype.dispatch = d;

var cc = EventTypes;

CasperRenderer.prototype.render = function(with_xy) {
  this.with_xy = with_xy;
  var etypes = EventTypes;
  this.document.open();
//  this.document.writeln('<button id="casperbox-button">Run it on Playbook</button>');
  this.document.write("<" + "pre" + ">");
  this.document.write("<span style=\"font-size: 18px\">");
  this.writeHeader();
  var last_down = null;
  var forget_click = false;

  for (var i=0; i < this.items.length; i++) {
    var item = this.items[i];
    if (item.type == etypes.Comment)
      this.space();
    
    if(i==0) {
        if(item.type!=etypes.OpenUrl) {
            this.text("ERROR - recorded steps did not start with opening an url");
        } else {
          this.startUrl(item);
          continue;
        }
    }

    // remember last MouseDown to identify drag
    if(item.type==etypes.MouseDown) {
      last_down = this.items[i];
      continue;
    }
    if(item.type==etypes.MouseUp && last_down) {
      if(last_down.x == item.x && last_down.y == item.y) {
        forget_click = false;
        continue;
      } else {
        item.before = last_down;
        this[this.dispatch[etypes.MouseDrag]](item);
        last_down = null;
        forget_click = true;
        continue;
      }
    }
    if(item.type==etypes.Click && forget_click) {
      forget_click = false;
      continue;
    }

    // we do not want click due to user checking actions
    if(i>0 && item.type==etypes.Click && 
            ((this.items[i-1].type>=etypes.CheckPageTitle && this.items[i-1].type<=etypes.CheckImageSrc) || this.items[i-1].type==etypes.ScreenShot)) {
        continue;
    }

    if (this.dispatch[item.type]) {
      this[this.dispatch[item.type]](item);
    }
    if (item.type == etypes.Comment)
      this.space();
  }
  this.writeFooter();
  this.document.write("<" + "/" + "span" + ">");
  this.document.write("<" + "/" + "pre" + ">");
  this.document.close();
}

CasperRenderer.prototype.writeHeader = function() {
// var date = new Date();
// this.text("/*==============================================================================*/", 0);
// this.text("/* Casper generated " + date + " */", 0);
// this.text("/*==============================================================================*/", 0);
// this.space();
// this.stmt("var x = require('casper').selectXPath;", 0);
}

CasperRenderer.prototype.writeFooter = function() {
// this.space();
// this.stmt("casper.run(function() {test.done();});");
// this.stmt("});", 0);
}

CasperRenderer.prototype.rewriteUrl = function(url) {
  return url;
}

CasperRenderer.prototype.shortUrl = function(url) {
  return url.substr(url.indexOf('/', 10), 999999999);
}

CasperRenderer.prototype.startUrl = function(item) {
  var url = this.pyrepr(this.rewriteUrl(item.url));
//  this.stmt("casper.options.viewportSize = {width: "+item.width+", height: "+item.height+"};", 0);
//  this.stmt("casper.on('page.error', function(msg, trace) {", 0);
//  this.stmt("this.echo('Error: ' + msg, 'ERROR');", 1);
//  this.stmt("for(var i=0; i&lt;trace.length; i++) {", 1);
//  this.stmt("var step = trace[i];", 2);
//  this.stmt("this.echo('   ' + step.file + ' (line ' + step.line + ')', 'ERROR');", 2);
//  this.stmt("}", 1);
//  this.stmt("});", 0);
//  this.stmt("casper.test.begin('Resurrectio test', function(test) {", 0);
//  this.stmt("casper.start(" + url + ");");
  this.stmt(url);
}
CasperRenderer.prototype.openUrl = function(item) {
  var url = this.pyrepr(this.rewriteUrl(item.url));
  var history = this.history;
//  if the user apparently hit the back button, render the event as such
//  if (url == history[history.length - 2]) {
//    this.stmt('casper.then(function() {');
//    this.stmt('    this.back();');
//    this.stmt('});');
//    history.pop();
//    history.pop();
//  } else {
//    this.stmt("casper.thenOpen(" + url + ");");
//  }
  this.stmt(url);
}

CasperRenderer.prototype.pageLoad = function(item) {
  var url = this.pyrepr(this.rewriteUrl(item.url));
  this.history.push(url);
}

CasperRenderer.prototype.normalizeWhitespace = function(s) {
  return s.replace(/^\s*/, '').replace(/\s*$/, '').replace(/\s+/g, ' ');
}

CasperRenderer.prototype.getControl = function(item) {
  var type = item.info.type;
  var tag = item.info.tagName.toLowerCase();
  var selector;
  if ((type == "submit" || type == "button") && item.info.value)
  //  selector = tag+'[type='+type+'][value='+this.pyrepr(this.normalizeWhitespace(item.info.value))+']';
    selector = this.pyrepr(this.normalizeWhitespace(item.info.value));
  else if (item.info.name)
  //  selector = tag+'[name='+this.pyrepr(item.info.name)+']';
    selector = this.pyrepr(item.info.name);
  else if (item.info.id)
  //  selector = tag+'#'+item.info.id;
    selector = item.info.id;
  else
    selector = item.info.selector;

  return selector;
}
  
CasperRenderer.prototype.getControlXPath = function(item) {
  var type = item.info.type;
  var way;
  if ((type == "submit" || type == "button") && item.info.value)
  //  way = '@value=' + this.pyrepr(this.normalizeWhitespace(item.info.value));
    way = this.pyrepr(this.normalizeWhitespace(item.info.value));
  else if (item.info.name)
  //  way = '@name=' + this.pyrepr(item.info.name);
    way = this.pyrepr(item.info.name);
  else if (item.info.id)
  //  way = '@id=' + this.pyrepr(item.info.id);
    way = this.pyrepr(item.info.id)
//  else
//  way = 'TODO';

  return way;
}

CasperRenderer.prototype.getLinkXPath = function(item) {
  var way;
  if (item.text)
  // way = 'normalize-space(text())=' + this.cleanStringForXpath(this.normalizeWhitespace(item.text), true);
    way = this.cleanStringForXpath(this.normalizeWhitespace(item.text), true);
  else if (item.info.id)
  // way = '@id=' + this.pyrepr(item.info.id);
    way = this.pyrepr(item.info.id);
  else if (item.info.href)
  // way = '@href=' + this.pyrepr(this.shortUrl(item.info.href));
    way = this.pyrepr(this.shortUrl(item.info.href));
  else if (item.info.title)
  // way = 'title='+this.pyrepr(this.normalizeWhitespace(item.info.title));
    way = this.pyrepr(this.normalizeWhitespace(item.info.title));

  return way;
}

CasperRenderer.prototype.mousedrag = function(item) {
//  if(this.with_xy) {
//    this.stmt('casper.then(function() {');
//    this.stmt('    this.mouse.down('+ item.before.x + ', '+ item.before.y +');');
//    this.stmt('    this.mouse.move('+ item.x + ', '+ item.y +');');
//    this.stmt('    this.mouse.up('+ item.x + ', '+ item.y +');');
//    this.stmt('});');
//  }
}

CasperRenderer.prototype.click = function(item) {
  var tag = item.info.tagName.toLowerCase();
  if(this.with_xy && !(tag == 'a' || tag == 'input' || tag == 'button')) {
//    this.stmt('casper.then(function() {');
//    this.stmt('    this.mouse.click('+ item.x + ', '+ item.y +');');
//    this.stmt('});');
  } else {
    var selector;
    if (tag == 'a') {
      var xpath_selector = this.getLinkXPath(item);
      if(xpath_selector) {
//        selector = 'x("//a['+xpath_selector+']")';
        selector = xpath_selector;
      } else {
        selector = item.info.selector;
      }
    } else if (tag == 'input' || tag == 'button') {
//      selector = this.getFormSelector(item) + this.getControl(item);
      selector = this.getControl(item);
//      selector = '"' + selector + '"';
    } else {
//      selector = '"' + item.info.selector + '"';
      selector = item.info.selector;
    }
//    this.stmt('casper.waitForSelector('+ selector + ',');
//    this.stmt('    function success() {');
//    this.stmt('        test.assertExists('+ selector + ');');
//    this.stmt('        this.click('+ selector + ');');
//    this.stmt('    },');
//    this.stmt('    function fail() {');
//    this.stmt('        test.assertExists(' + selector + ');')
//    this.stmt('});');
    if (selector.charAt(0) == '#') {selector = selector.substring(1);}
    this.stmt('click ' + selector);
  }
}

CasperRenderer.prototype.getFormSelector = function(item) {
  var info = item.info;
  if(!info.form) {
    return '';
  }
  if(info.form.name) {
        return "form[name=" + info.form.name + "] ";
    } else if(info.form.id) {
    return "form#" + info.form.id + " ";
  } else {
    return "form ";
  }
}

CasperRenderer.prototype.keypress = function(item) {
  var text = item.text.replace('\n','').replace('\r', '\\r');
  var selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('enter ' + selector + ' as ' + text);
//  this.stmt('casper.waitForSelector("' + this.getControl(item) + '",');
//  this.stmt('    function success() {');
//  this.stmt('        this.sendKeys("' + this.getControl(item) + '", "' + text + '");');
//  this.stmt('    },');
//  this.stmt('    function fail() {');
//  this.stmt('        test.assertExists("' + this.getControl(item) + '");')
//  this.stmt('});');
}

CasperRenderer.prototype.submit = function(item) {
  // the submit has been called somehow (user, or script)
  // so no need to trigger it.
//  this.stmt("/* submit form */");
}

CasperRenderer.prototype.screenShot = function(item) {
  // wait 1 second is not the ideal solution, but will be enough most
  // part of time. For slow pages, an assert before capture will make
  // sure evrything is properly loaded before screenshot.
//  this.stmt('casper.wait(1000);');
//  this.stmt('casper.then(function() {');
//  this.stmt('    this.captureSelector("screenshot'+this.screen_id+'.png", "html");');
//  this.stmt('});');
  this.screen_id = this.screen_id + 1;
}

CasperRenderer.prototype.checkPageTitle = function(item) {
//  var title = this.pyrepr(item.title, true);
//  this.stmt('casper.then(function() {');
//  this.stmt('    test.assertTitle('+ title +');');
//  this.stmt('});');
}

CasperRenderer.prototype.checkPageLocation = function(item) {
//  var url = this.regexp_escape(item.url);
//  this.stmt('casper.then(function() {');
//  this.stmt('    test.assertUrlMatch(/^'+ url +'$/);');
//  this.stmt('});');
}

CasperRenderer.prototype.checkTextPresent = function(item) {
//    var selector = 'x("//*[contains(text(), '+this.pyrepr(item.text, true)+')]")';
//    this.waitAndTestSelector(selector);
}

CasperRenderer.prototype.checkValue = function(item) {
//  var type = item.info.type;
//  var way = this.getControlXPath(item);
//  var selector = '';
//  if (type == 'checkbox' || type == 'radio') {
//    var selected;
//    if (item.info.checked)
//      selected = '@checked'
//    else
//      selected = 'not(@checked)'
//    selector = 'x("//input[' + way + ' and ' +selected+ ']")';
//  }
//  else {
//    var value = this.pyrepr(item.info.value)
//    var tag = item.info.tagName.toLowerCase();
//    selector = 'x("//'+tag+'[' + way + ' and @value='+value+']")';
//  }
//  this.waitAndTestSelector(selector);
}

CasperRenderer.prototype.checkText = function(item) {
//  var selector = '';
//  if ((item.info.type == "submit") || (item.info.type == "button")) {
//      selector = 'x("//input[@value='+this.pyrepr(item.text, true)+']")';
//  } else {
//      selector = 'x("//*[normalize-space(text())='+this.cleanStringForXpath(item.text, true)+']")';
//  }
//  this.waitAndTestSelector(selector);
}

CasperRenderer.prototype.checkHref = function(item) {
//  var href = this.pyrepr(this.shortUrl(item.info.href));
//  var xpath_selector = this.getLinkXPath(item);
//  if(xpath_selector) {
//    selector = 'x("//a['+xpath_selector+' and @href='+ href +']")';
//  } else {
//    selector = item.info.selector+'[href='+ href +']';
//  }
//    this.stmt('casper.then(function() {');
//    this.stmt('    test.assertExists('+selector+');');
//    this.stmt('});');
}

CasperRenderer.prototype.checkEnabled = function(item) {
//    var way = this.getControlXPath(item);
//    var tag = item.info.tagName.toLowerCase();
//    this.waitAndTestSelector('x("//'+tag+'[' + way + ' and not(@disabled)]")');
}

CasperRenderer.prototype.checkDisabled = function(item) {
//  var way = this.getControlXPath(item);
//  var tag = item.info.tagName.toLowerCase();
//  this.waitAndTestSelector('x("//'+tag+'[' + way + ' and @disabled]")');
}

CasperRenderer.prototype.checkSelectValue = function(item) {
//  var value = this.pyrepr(item.info.value);
//  var way = this.getControlXPath(item);
//  this.waitAndTestSelector('x("//select[' + way + ']/option[@selected and @value='+value+']")');
}

CasperRenderer.prototype.checkSelectOptions = function(item) {
//  this.stmt('/* TODO */');
}

CasperRenderer.prototype.checkImageSrc = function(item) {
//  var src = this.pyrepr(this.shortUrl(item.info.src));
//  this.waitAndTestSelector('x("//img[@src=' + src + ']")');
}

CasperRenderer.prototype.waitAndTestSelector = function(selector) {
//  this.stmt('casper.waitForSelector(' + selector + ',');
//  this.stmt('    function success() {');
//  this.stmt('        test.assertExists(' + selector + ');')
//  this.stmt('      },');
//  this.stmt('    function fail() {');
//  this.stmt('        test.assertExists(' + selector + ');')
//  this.stmt('});');
}

CasperRenderer.prototype.postToCasperbox = function() {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'http://play.tebel.org/api', true); 
  xhr.onload = function() {
    if (this.status == 202) {
      response = JSON.parse(this.responseText);
      window.open('http://play.tebel.org/api?' + response.id);  
    } else {
      alert("ERROR - "+this.status);
    }
    
  };
  xhr.send(document.getElementsByTagName('pre')[0].innerText);
}

var dt = new CasperRenderer(document);
window.onload = function onpageload() {
  var with_xy = false;
  if(window.location.search=="?xy=true") {
    with_xy = true;
  }
  chrome.runtime.sendMessage({action: "get_items"}, function(response) {
      dt.items = response.items;
      dt.render(with_xy);
      document.getElementById("casperbox-button").onclick = function() {
        dt.postToCasperbox();
      };
  });
};
