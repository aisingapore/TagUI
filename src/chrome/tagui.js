// --------------------------------------------------------------------------------
// CasperRenderer - class to render recorded steps to TagUI automation flow format
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
EventTypes.ElementScreenShot = 24;
EventTypes.MoveCursorToElement = 25;
EventTypes.PrintElementText = 26;
EventTypes.SaveElementText = 27;
EventTypes.ExplicitWait = 28;
EventTypes.FetchElementText = 29;
EventTypes.SelectElementOption = 30;
EventTypes.CancelLastStep = 31;
EventTypes.NoteDownElement = 32;
EventTypes.InspectElement = 33;
EventTypes.Cancel = 99;
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
}

CasperRenderer.prototype.text = function(text) {
  this.document.writeln(text);
}

CasperRenderer.prototype.stmt = function(text, indent) {
  if(indent==undefined) indent = 0;
  var output = (new Array(4*indent)).join(" ") + text;
  this.document.writeln(output);
}

CasperRenderer.prototype.pyrepr = function(text, escape) {
  var s = text;
  if (escape) s = s.replace(/(['"])/g, "\\$1");
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
            return '"\'"';
        }

        if (part === '"') {
            return "'\"'";
        }
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
//d[EventTypes.Change] = "change";
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
d[EventTypes.ElementScreenShot] = "elementScreenShot";
d[EventTypes.NoteDownElement] = "noteDownElement";
d[EventTypes.InspectElement] = "inspectElement";
d[EventTypes.MoveCursorToElement] = "moveCursorToElement";
d[EventTypes.PrintElementText] = "printElementText";
d[EventTypes.SaveElementText] = "saveElementText";
d[EventTypes.FetchElementText] = "fetchElementText";
d[EventTypes.SelectElementOption] = "selectElementOption";
d[EventTypes.ExplicitWait] = "explicitWait";
d[EventTypes.CancelLastStep] = "cancelLastStep";
d[EventTypes.Cancel] = "cancel";
//d[EventTypes.MouseDown] = "mousedown";
//d[EventTypes.MouseUp] = "mouseup";
d[EventTypes.MouseDrag] = "mousedrag";
d[EventTypes.KeyPress] = "keypress";

CasperRenderer.prototype.dispatch = d;

var cc = EventTypes;

CasperRenderer.prototype.render = function(with_xy) {
  this.with_xy = with_xy;
  var etypes = EventTypes;
  this.document.open();
  this.document.write("<" + "pre" + ">");
  this.document.write("<span style=\"font-size: 18px\">");
  var last_down = null;
  var forget_click = false;

  for (var i=0; i < this.items.length; i++) {
    var item = this.items[i];
    
    if(i==0) {
        if(item.type!=etypes.OpenUrl) {
            this.text("ERROR - steps did not start with opening an url");
        }
        else {
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
      }
      else {
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
    
    // ignore click due to clicking on right-click menu
    if(i>0 && item.type==etypes.Click && 
      ((this.items[i-1].type>=5 && this.items[i-1].type<=16) ||
      (this.items[i-1].type==18 || this.items[i-1].type>=24)))
    {
        continue;
    }
    
    if (this.dispatch[item.type]) {
      this[this.dispatch[item.type]](item);
    }
  
  }
  this.document.write("<" + "/" + "span" + ">");
  this.document.write("<" + "/" + "pre" + ">");
  this.document.close();
  
}

CasperRenderer.prototype.rewriteUrl = function(url) {
  return url;
}

CasperRenderer.prototype.shortUrl = function(url) {
  return url.substr(url.indexOf('/', 10), 999999999);
}

CasperRenderer.prototype.startUrl = function(item) {
  var url = this.pyrepr(this.rewriteUrl(item.url));
  this.stmt(url);
}
CasperRenderer.prototype.openUrl = function(item) {
  var url = this.pyrepr(this.rewriteUrl(item.url));
  var history = this.history;
// if the user apparently hit the back button, render the event as such
  if (url == history[history.length - 2]) {
    this.stmt('this.back();');
    history.pop();
    history.pop();
  } else {
    this.stmt(url);
  }
}

CasperRenderer.prototype.pageLoad = function(item) {
  var url = this.pyrepr(this.rewriteUrl(item.url));
  this.history.push(url);
}

CasperRenderer.prototype.normalizeWhitespace = function(s) {
  return s.replace(/^\s*/, '').replace(/\s*$/, '').replace(/\s+/g, ' ');
}
  
//CasperRenderer.prototype.getControlXPath = function(item) {
//  var type = item.info.type;
//  var way;
//  if ((type == "submit" || type == "button") && item.info.value)
//  //  way = '@value=' + this.pyrepr(this.normalizeWhitespace(item.info.value));
//    way = this.pyrepr(this.normalizeWhitespace(item.info.value));
//  else if (item.info.name)
//  //  way = '@name=' + this.pyrepr(item.info.name);
//    way = this.pyrepr(item.info.name);
//  else if (item.info.id)
//  //  way = '@id=' + this.pyrepr(item.info.id);
//    way = this.pyrepr(item.info.id)
////  else
////  way = 'TODO';
//
//  return way;
//}

CasperRenderer.prototype.getControl = function(item) {
  return item.info.selector; // default identifiers to css for accuracy, to optimize later
  var type = item.info.type;
  var tag = item.info.tagName.toLowerCase();
  var selector;
  if (item.info.id)
  //  selector = tag+'#'+item.info.id;
    selector = this.pyrepr(item.info.id);
  else if ((type == "submit" || type == "button") && item.info.value)
  {
  //  selector = tag+'[type='+type+'][value='+this.pyrepr(this.normalizeWhitespace(item.info.value))+']';
    if (item.info.name)
      selector = '//*[@value="' + this.pyrepr(item.info.value) + '" or @name="' + this.pyrepr(item.info.name) + '"]';
    else
      selector = '//*[@value="' + this.pyrepr(item.info.value) + '"]';
  }
  else if (item.info.name)
  //  selector = tag+'[name='+this.pyrepr(item.info.name)+']';
    selector = this.pyrepr(item.info.name);
//  else if (item.info.className)
//  //  selector = tag+'#'+item.info.className;
//    selector = this.pyrepr(item.info.className);
  else
    selector = item.info.selector;
  return selector;
}

CasperRenderer.prototype.getLinkXPath = function(item) {
  var way;
  if (item.info.id)
    way = '@id=' + '"' + this.pyrepr(item.info.id) + '"';
//  else if (item.info.title)
//    way = '@title=' + '"' + this.pyrepr(item.info.title) + '"';
  else if (item.text)
  {
    if (item.text.indexOf('[whitespace]')==-1) // return normally if not beginning / ending whitespace
      way = 'text()=' + '"' + this.cleanStringForXpath(item.text, true) + '"';
    else // otherwise return using contains keyword in order to match the element via text comparison
      way = 'contains(text(),' + '"' + this.cleanStringForXpath(item.text.replace(/\[whitespace\]/g,''), true) + '")';
    if (item.info.href)
      way += ' or contains("' + this.pyrepr(item.info.href) + '",@href)';
  }
  else if (item.info.href)
    way = 'contains("' + this.pyrepr(item.info.href) + '",@href)';
  return way;
}

CasperRenderer.prototype.click = function(item) {
  var tag = item.info.tagName.toLowerCase();
  if(this.with_xy && !(tag == 'a' || tag == 'input' || tag == 'button')) {
    this.stmt('this.mouse.click('+ item.x + ', '+ item.y +');');
  }
  else {
    var selector;
    
    if (tag == 'a') {
      var xpath_selector = this.getLinkXPath(item);
      if(xpath_selector) {
        selector = '//a['+xpath_selector+']';
      }
      else {
        selector = item.info.selector;
      }
    }
    else if (tag == 'input' || tag == 'button') {
      // selector = this.getFormSelector(item) + this.getControl(item);
      selector = this.getControl(item);
    }
    else {
      selector = item.info.selector;
    }
    
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
  }
  else if(info.form.id) {
    return "form#" + info.form.id + " ";
  }
  else {
    return "form ";
  }
}

CasperRenderer.prototype.mousedrag = function(item) {
  if(this.with_xy) {
    this.stmt('this.mouse.down('+ item.before.x + ', '+ item.before.y +');');
    this.stmt('this.mouse.move('+ item.x + ', '+ item.y +');');
    this.stmt('this.mouse.up('+ item.x + ', '+ item.y +');');
  }
}

CasperRenderer.prototype.keypress = function(item) {
//  var text = item.text.replace('\n','').replace('\r', '\\r');
//  change above to [enter] to handle typing of enter key
  var text = item.text.replace(/\r\n/g,'[enter]').replace(/\r/g, '[enter]').replace(/\n/g, '[enter]');
  var selector; selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('type ' + selector + ' as ' + text);
}

CasperRenderer.prototype.submit = function(item) {
  // the submit has been called somehow (user, script) so no need to trigger it
}

CasperRenderer.prototype.screenShot = function(item) {
  this.stmt('snap page');
}

CasperRenderer.prototype.elementScreenShot = function(item) {
  var selector; selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('snap ' + selector);
}

CasperRenderer.prototype.inspectElement = function(item) {
}

CasperRenderer.prototype.noteDownElement = function(item) {
  var selector; selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('// element is ' + selector);
}

CasperRenderer.prototype.moveCursorToElement = function(item) {
  var selector; selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('hover ' + selector);
}

CasperRenderer.prototype.fetchElementText = function(item) {
  var selector; selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('read ' + selector + ' to variable');
}

CasperRenderer.prototype.selectElementOption = function(item) {
  var selector; selector = this.getControl(item); var value = this.pyrepr(item.info.value);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('select ' + selector + ' as ' + value);
}

CasperRenderer.prototype.cancelLastStep = function(item) {
  this.stmt('cancel the last step');
}

CasperRenderer.prototype.printElementText = function(item) {
  var selector; selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('show ' + selector);
}

CasperRenderer.prototype.saveElementText = function(item) {
  var selector; selector = this.getControl(item);
  if (selector.charAt(0) == '#') {selector = selector.substring(1);}
  this.stmt('save ' + selector);
}

CasperRenderer.prototype.explicitWait = function(item) {
  this.stmt('wait');
}

CasperRenderer.prototype.cancel = function(item) {
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
//  var href = this.pyrepr(this.shortUrl(item.info.href)); var xpath_selector = this.getLinkXPath(item);
//  if(xpath_selector) {
//    selector = '//a['+xpath_selector+' and @href='+ href +']';
//  } else {
//    selector = item.info.selector+'[href='+ href +']';
//  }
//    this.stmt('casper.then(function() {');
//    this.stmt('    test.assertExists('+selector+');');
//    this.stmt('});');
}

CasperRenderer.prototype.checkEnabled = function(item) {
//    var way = this.getControlXPath(item); var tag = item.info.tagName.toLowerCase();
//    this.waitAndTestSelector('x("//'+tag+'[' + way + ' and not(@disabled)]")');
}

CasperRenderer.prototype.checkDisabled = function(item) {
//  var way = this.getControlXPath(item); var tag = item.info.tagName.toLowerCase();
//  this.waitAndTestSelector('x("//'+tag+'[' + way + ' and @disabled]")');
}

CasperRenderer.prototype.checkSelectValue = function(item) {
//  var value = this.pyrepr(item.info.value); var way = this.getControlXPath(item);
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
//  this.stmt('test.assertExists(' + selector + ');')
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
  });
};
