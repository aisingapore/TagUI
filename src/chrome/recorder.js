//----------------------------------------------------------------------------
//Copyright (c) 2005 Zope Foundation and Contributors.

//This software is subject to the provisions of the Zope Public License,
//Version 2.1 (ZPL).  A copy of the ZPL should accompany this distribution.
//THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
//WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
//WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
//FOR A PARTICULAR PURPOSE.


//TestRecorder - a javascript library to support browser test recording. It
//is designed to be as cross-browser compatible as possible and to promote 
//loose coupling with the user interface, making it easier to evolve the UI
//and experiment with alternative interfaces.

//caveats: popup windows undefined, cant handle framesets

//todo:
//- capture submit (w/lookback for doctest)
//- cleanup strings


//Contact Brian Lloyd (brian@zope.com) with questions or comments.
//---------------------------------------------------------------------------

if (typeof(TestRecorder) == "undefined") {
    TestRecorder = {};
}

//---------------------------------------------------------------------------
//Browser -- a singleton that provides a cross-browser API for managing event 
//handlers and miscellaneous browser functions.

//Methods:

//captureEvent(window, name, handler) -- capture the named event occurring
//in the given window, setting the function handler as the event handler.
//The event name should be of the form "click", "blur", "change", etc. 

//releaseEvent(window, name, handler) -- release the named event occurring
//in the given window. The event name should be of the form "click", "blur",
//"change", etc. 

//getSelection(window) -- return the text currently selected, or the empty
//string if no text is currently selected in the browser.

//---------------------------------------------------------------------------

if (typeof(TestRecorder.Browser) == "undefined") {
    TestRecorder.Browser = {};
}

TestRecorder.Browser.captureEvent = function(wnd, name, func) {
    var lname = name.toLowerCase();
    var doc = wnd.document;
    wnd.captureEvents(Event[name.toUpperCase()]);
    wnd["on" + lname] = func;
}

TestRecorder.Browser.releaseEvent = function(wnd, name, func) {
    var lname = name.toLowerCase();
    var doc = wnd.document;
    wnd.releaseEvents(Event[name.toUpperCase()]);
    wnd["on" + lname] = null;
}

TestRecorder.Browser.getSelection = function(wnd) {
    var doc = wnd.document;
    if (wnd.getSelection) {
        return wnd.getSelection() + "";
    }
    else if (doc.getSelection) {
        return doc.getSelection() + "";
    } 
    else if (doc.selection && doc.selection.createRange) {
        return doc.selection.createRange().text + "";
    }
    return "";
}

TestRecorder.Browser.windowHeight = function(wnd) {
    var doc = wnd.document;
    if (wnd.innerHeight) {
        return wnd.innerHeight;
    }
    else if (doc.documentElement && doc.documentElement.clientHeight) {
        return doc.documentElement.clientHeight;
    }
    else if (document.body) {
        return document.body.clientHeight;
    }
    return -1;
}

TestRecorder.Browser.windowWidth = function(wnd) {
    var doc = wnd.document;
    if (wnd.innerWidth) {
        return wnd.innerWidth;
    }
    else if (doc.documentElement && doc.documentElement.clientWidth) {
        return doc.documentElement.clientWidth;
    }
    else if (document.body) {
        return document.body.clientWidth;
    }
    return -1;
}


//---------------------------------------------------------------------------
//Event -- a class that provides a cross-browser API dealing with most of the
//interesting information about events.

//Methods:

//type() -- returns the string type of the event (e.g. "click")

//target() -- returns the target of the event

//button() -- returns the mouse button pressed during the event. Because
//it is not possible to reliably detect a middle button press, this method 
//only recognized the left and right mouse buttons. Returns one of the  
//constants Event.LeftButton, Event.RightButton or Event.UnknownButton for 
//a left click, right click, or indeterminate (or no mouse click).

//keycode() -- returns the index code of the key pressed. Note that this 
//value may differ across browsers because of character set differences. 
//Whenever possible, it is suggested to use keychar() instead.

//keychar() -- returns the char version of the key pressed rather than a 
//raw numeric code. The resulting value is subject to all of the vagaries 
//of browsers, character encodings in use, etc.

//shiftkey() -- returns true if the shift key was pressed.

//posX() -- return the X coordinate of the mouse relative to the document.

//posY() -- return the y coordinate of the mouse relative to the document.

//stopPropagation() -- stop event propagation (if supported)

//preventDefault() -- prevent the default action (if supported)

//---------------------------------------------------------------------------

TestRecorder.Event = function(e) {
    this.event = (e) ? e : window.event;
}

TestRecorder.Event.LeftButton = 0;
TestRecorder.Event.MiddleButton = 1;
TestRecorder.Event.RightButton = 2;
TestRecorder.Event.UnknownButton = 3;

TestRecorder.Event.prototype.stopPropagation = function() {
    if (this.event.stopPropagation)
        this.event.stopPropagation();
}

TestRecorder.Event.prototype.preventDefault = function() {
    if (this.event.preventDefault)
        this.event.preventDefault();
}

TestRecorder.Event.prototype.type = function() {
    return this.event.type;
}

TestRecorder.Event.prototype.button = function() {
    if (this.event.button) {
        if (this.event.button == 2) {
            return TestRecorder.Event.RightButton;
        }
        return TestRecorder.Event.LeftButton;
    }
    else if (this.event.which) {
        if (this.event.which > 1) {
            return TestRecorder.Event.RightButton;
        }
        return TestRecorder.Event.LeftButton;
    }
    return TestRecorder.Event.UnknownButton;
}

TestRecorder.Event.prototype.target = function() {
    var t = (this.event.target) ? this.event.target : this.event.srcElement;
    if (t && t.nodeType == 3) // safari bug
        return t.parentNode;
    return t;
}

TestRecorder.Event.prototype.keycode = function() {
    return (this.event.keyCode) ? this.event.keyCode : this.event.which;
}

TestRecorder.Event.prototype.keychar = function() {
    return String.fromCharCode(this.keycode());
}

TestRecorder.Event.prototype.shiftkey = function() {
    if (this.event.shiftKey)
        return true;
    return false;
}

TestRecorder.Event.prototype.posX = function() {
    if (this.event.pageX)
        return this.event.pageX;
    else if (this.event.clientX) {
        return this.event.clientX + document.body.scrollLeft;
    }
    return 0;
}

TestRecorder.Event.prototype.posY = function() {
    if (this.event.pageY)
        return this.event.pageY;
    else if (this.event.clientY) {
        return this.event.clientY + document.body.scrollTop;
    }
    return 0;
}



//---------------------------------------------------------------------------
//TestCase -- this class contains the interesting events that happen in 
//the course of a test recording and provides some testcase metadata.

//Attributes:

//title -- the title of the test case.

//items -- an array of objects representing test actions and checks


//---------------------------------------------------------------------------

TestRecorder.TestCase = function() {
    this.title = "Test Case";
    // maybe some items are already stored in the background
    // but we do not need them here anyway
    this.items = new Array();
}

TestRecorder.TestCase.prototype.append = function(o) {
    this.items[this.items.length] = o;
    chrome.runtime.sendMessage({action: "append", obj: o});
}

TestRecorder.TestCase.prototype.peek = function() {
    return this.items[this.items.length - 1];
}

TestRecorder.TestCase.prototype.poke = function(o) {
    this.items[this.items.length - 1] = o;
    chrome.runtime.sendMessage({action: "poke", obj: o});
}


//---------------------------------------------------------------------------
//Event types -- whenever an interesting event happens (an action or a check)
//it is recorded as one of the object types defined below. All events have a
//'type' attribute that marks the type of the event (one of the values in the
//EventTypes enumeration) and different attributes to capture the pertinent 
//information at the time of the event.
//---------------------------------------------------------------------------

if (typeof(TestRecorder.EventTypes) == "undefined") {
    TestRecorder.EventTypes = {};
}

TestRecorder.EventTypes.OpenUrl = 0;
TestRecorder.EventTypes.Click = 1;
TestRecorder.EventTypes.Change = 2;
TestRecorder.EventTypes.Comment = 3;
TestRecorder.EventTypes.Submit = 4;
TestRecorder.EventTypes.CheckPageTitle = 5;
TestRecorder.EventTypes.CheckPageLocation = 6;
TestRecorder.EventTypes.CheckTextPresent = 7;
TestRecorder.EventTypes.CheckValue = 8;
TestRecorder.EventTypes.CheckValueContains = 9;
TestRecorder.EventTypes.CheckText = 10;
TestRecorder.EventTypes.CheckHref = 11;
TestRecorder.EventTypes.CheckEnabled = 12;
TestRecorder.EventTypes.CheckDisabled = 13;
TestRecorder.EventTypes.CheckSelectValue = 14;
TestRecorder.EventTypes.CheckSelectOptions = 15;
TestRecorder.EventTypes.CheckImageSrc = 16;
TestRecorder.EventTypes.PageLoad = 17;
TestRecorder.EventTypes.ScreenShot = 18;
TestRecorder.EventTypes.ElementScreenShot = 24;
TestRecorder.EventTypes.MoveCursorToElement = 25;
TestRecorder.EventTypes.PrintElementText = 26;
TestRecorder.EventTypes.SaveElementText = 27;
TestRecorder.EventTypes.ExplicitWait = 28;
TestRecorder.EventTypes.FetchElementText = 29;
TestRecorder.EventTypes.SelectElementOption = 30;
TestRecorder.EventTypes.CancelLastStep = 31;
TestRecorder.EventTypes.NoteDownElement = 32;
TestRecorder.EventTypes.InspectElement = 33;
TestRecorder.EventTypes.Cancel = 99;
TestRecorder.EventTypes.MouseDown = 19;
TestRecorder.EventTypes.MouseUp = 20;
TestRecorder.EventTypes.MouseDrag = 21;
TestRecorder.EventTypes.MouseDrop = 22;
TestRecorder.EventTypes.KeyPress = 23;

TestRecorder.ElementInfo = function(element) {
    this.action = element.action;
    this.method = element.method;
    this.href = element.href;
    this.tagName = element.tagName;
    this.selector = this.getCleanCSSSelector(element);
    this.value = element.value;
    this.checked = element.checked;
    this.name = element.name;
    this.type = element.type;
    if (this.type)
        this.type = this.type.toLowerCase();
    if (element.form)
        this.form = {id: element.form.id, name: element.form.getAttribute('name')};
    this.src = element.src;
    this.id = element.id;
    this.title = element.title;
    this.selectedIndex = element.selectedIndex;
    this.options = [];
    if (element.selectedIndex) {
        for (var i=0; i < element.options.length; i++) {
            var o = element.options[i];
            this.options[i] = {text:o.text, value:o.value};
        }
    }
    this.label = this.findLabelText(element);
}

TestRecorder.ElementInfo.prototype.findLabelText = function(element) {
    var label = this.findContainingLabel(element)
    var text;
    if (!label) {
        label = this.findReferencingLabel(element);
    }
    if (label) {
        text = label.innerHTML;
        // remove newlines
        text = text.replace('\n', ' ');
        // remove tags
        text = text.replace(/<[^>]*>/g, ' ');
        // remove non-alphanumeric prefixes or suffixes
        text = text.replace(/^\W*/mg, '')
        text = text.replace(/\W*$/mg, '')
        // remove extra whitespace
        text = text.replace(/^\s*/, '').replace(/\s*$/, '').replace(/\s+/g, ' ');
    }

    return text;
}

TestRecorder.ElementInfo.prototype.findReferencingLabel = function(element) {
    var labels = window.document.getElementsByTagName('label')
    for (var i = 0; i < labels.length; i++) {
        if (labels[i].attributes['for'] &&
                labels[i].attributes['for'].value == element.id)
            return labels[i]
    }
}

TestRecorder.ElementInfo.prototype.findContainingLabel = function(element) {
    var parent = element.parentNode;
    if (!parent)
        return undefined;
    if (parent.tagName && parent.tagName.toLowerCase() == 'label')
        return parent;
    else
        return this.findContainingLabel(parent);
}

TestRecorder.ElementInfo.prototype.getCleanCSSSelector = function(element) {
    if(!element) return;
    var selector = element.tagName ? element.tagName.toLowerCase() : '';
    if(selector == '' || selector == 'html') return '';

    var tmp_selector = '';
    var accuracy = document.querySelectorAll(selector).length;
    if(element.id) {
        selector = "#" + element.id.replace(/\./g, '\\.');
        accuracy = document.querySelectorAll(selector).length
        if(accuracy==1) return selector;
    }
    if(element.className) {
        tmp_selector = '.' + element.className.trim().replace(/ /g,".");
        if(document.querySelectorAll(tmp_selector).length < accuracy) {
            selector = tmp_selector;
            accuracy = document.querySelectorAll(selector).length
            if(accuracy==1) return selector;
        }
    }
    var parent = element.parentNode;
    var parent_selector = this.getCleanCSSSelector(parent);

    if(parent_selector) {

        // resolve sibling ambiguity
        var matching_sibling = 0;
        var matching_nodes = document.querySelectorAll(parent_selector + ' > ' + selector);
        for(var i=0; i<matching_nodes.length;i++) {
            if(matching_nodes[i].parentNode == parent) matching_sibling++;
        }
        if(matching_sibling > 1) {
            var index = 1;
            for (var sibling = element.previousElementSibling; sibling; sibling = sibling.previousElementSibling) index++;
            selector = selector + ':nth-child(' + index + ')';
        }
        
        // remove useless intermediary parent
        selector_array = parent_selector.split(' ');
        if(selector_array.length>1) {
            for(var i=1;i<selector_array.length;i++) {
                tmp_selector = selector_array.slice(0,i).join(' ') + ' ' + selector;
                if(document.querySelectorAll(tmp_selector).length == 1) {
                    selector = tmp_selector;
                    break;
                }
            }
        } 

        // improve accuracy if still not correct
        accuracy = document.querySelectorAll(selector).length
        if(accuracy>1) {
            tmp_selector = parent_selector + " " + selector;
            if(document.querySelectorAll(tmp_selector).length==1) {
                selector = tmp_selector;
            } else {
                selector = parent_selector + " > " + selector;
            }
        }
    }

    return selector;
}

TestRecorder.DocumentEvent = function(type, target) {
    this.type = type;
    this.url = target.URL;
    this.title = target.title;
}

TestRecorder.ElementEvent = function(type, target, text) {
    this.type = type;
    this.info = new TestRecorder.ElementInfo(target);
    this.text = text ? text : recorder.strip(contextmenu.innertext(target));
}

TestRecorder.CommentEvent = function(text) {
    this.type = TestRecorder.EventTypes.Comment;
    this.text = text;
}

TestRecorder.KeyEvent = function(target, text) {
    this.type = TestRecorder.EventTypes.KeyPress;
    this.info = new TestRecorder.ElementInfo(target);
    this.text = text;
}

TestRecorder.MouseEvent = function(type, target, x, y) {
    this.type = type;
    this.info = new TestRecorder.ElementInfo(target);
    this.x = x;
    this.y = y;
    this.text = recorder.strip(contextmenu.innertext(target));
}

TestRecorder.ScreenShotEvent = function() {
    this.type = TestRecorder.EventTypes.ScreenShot;
}

TestRecorder.ElementScreenShotEvent = function() {
    this.type = TestRecorder.EventTypes.ElementScreenShot;
}

TestRecorder.InspectElement = function() {
    this.type = TestRecorder.EventTypes.InspectElement;
}

TestRecorder.NoteDownElement = function() {
    this.type = TestRecorder.EventTypes.NoteDownElement;
}

TestRecorder.MoveCursorToElementEvent = function() {
    this.type = TestRecorder.EventTypes.MoveCursorToElement;
}

TestRecorder.FetchElementTextEvent = function() {
    this.type = TestRecorder.EventTypes.FetchElementText;
}

TestRecorder.SelectElementOptionEvent = function() {
    this.type = TestRecorder.EventTypes.SelectElementOption;
}

TestRecorder.PrintElementTextEvent = function() {
    this.type = TestRecorder.EventTypes.PrintElementText;
}

TestRecorder.SaveElementTextEvent = function() {
    this.type = TestRecorder.EventTypes.SaveElementText;
}

TestRecorder.ExplicitWaitEvent = function() {
    this.type = TestRecorder.EventTypes.ExplicitWait;
}

TestRecorder.CancelLastStepEvent = function() {
    this.type = TestRecorder.EventTypes.CancelLastStep;
}

TestRecorder.CancelEvent = function() {
    this.type = TestRecorder.EventTypes.Cancel;
}

TestRecorder.OpenURLEvent = function(url) {
    this.type = TestRecorder.EventTypes.OpenUrl;
    this.url = url;
    this.width = window.innerWidth;
    this.height = window.innerHeight;
}

TestRecorder.PageLoadEvent = function(url) {
    this.type = TestRecorder.EventTypes.OpenUrl;
    this.url = url;
//    this.viaBack = back
}



//---------------------------------------------------------------------------
//ContextMenu -- this class is responsible for managing the right-click 
//context menu that shows appropriate checks for targeted elements.

//All methods and attributes are private to this implementation.
//---------------------------------------------------------------------------

TestRecorder.ContextMenu = function() {
    this.selected = null;
    this.target = null;
    this.window = null;
    this.visible = false;
    this.over = false;
    this.menu = null;
}

contextmenu = new TestRecorder.ContextMenu();


TestRecorder.ContextMenu.prototype.build = function(t, x, y) {
    var d = recorder.window.document;
    var b = d.getElementsByTagName("body").item(0);
    var menu = d.createElement("div");

    // Needed to deal with various cross-browser insanities...
    menu.setAttribute("style", "backgroundColor:#ffffff;color:#000000;border:1px solid #000000;padding:2px;position:absolute;display:none;top:" + y + "px;left:" + x + "px;border:1px;z-index:10000;");

    menu.style.backgroundColor="#ffffff";
    menu.style.color="#000000";
    menu.style.border = "1px solid #000000";
    menu.style.padding="2px";
    menu.style.position = "absolute";
    menu.style.display = "none";
    menu.style.zIndex = "10000";
    menu.style.top = y.toString();
    menu.style.left = x.toString();
    menu.onmouseover=contextmenu.onmouseover;
    menu.onmouseout=contextmenu.onmouseout;

    var selected = TestRecorder.Browser.getSelection(recorder.window).toString();

//    if (t.width && t.height) {
//        menu.appendChild(this.item("Check Image Src", this.checkImgSrc));
//    }
//
//    else if (t.type == "text" || t.type == "textarea") {
//        menu.appendChild(this.item("Check Text Value", this.checkValue));
//        menu.appendChild(this.item("Check Enabled", this.checkEnabled));
//        menu.appendChild(this.item("Check Disabled", this.checkDisabled));
//    }
//
//    else if (selected && (selected != "")) {
//        this.selected = recorder.strip(selected);
//        menu.appendChild(this.item("Check Text Appears On Page", 
//                this.checkTextPresent));
//    }
//
//    else if (t.href) {
//        menu.appendChild(this.item("Check Link Text", this.checkText));
//        menu.appendChild(this.item("Check Link Href", this.checkHref));
//    }
//
//    else if (t.selectedIndex || t.type == "option") {
//        var name = "Check Selected Value";
//        if (t.type != "select-one") {
//            name = name + "s";
//        }
//        menu.appendChild(this.item(name, this.checkSelectValue));
//        menu.appendChild(this.item("Check Select Options",
//                this.checkSelectOptions));
//        menu.appendChild(this.item("Check Enabled", this.checkEnabled));
//        menu.appendChild(this.item("Check Disabled", this.checkDisabled));
//    }
//
//    else if (t.type == "button" || t.type == "submit") {
//        menu.appendChild(this.item("Check Button Text", this.checkText));
//        menu.appendChild(this.item("Check Button Value", this.checkValue));
//        menu.appendChild(this.item("Check Enabled", this.checkEnabled));
//        menu.appendChild(this.item("Check Disabled", this.checkDisabled));
//    }
//
//    else if (t.value) {
//        menu.appendChild(this.item("Check Value", this.checkValue));
//        menu.appendChild(this.item("Check Enabled", this.checkEnabled));
//        menu.appendChild(this.item("Check Disabled", this.checkDisabled));
//    }
//
//    else {
//        menu.appendChild(this.item("Check Page Location", this.checkPageLocation));
//        menu.appendChild(this.item("Check Page Title", this.checkPageTitle));
//        menu.appendChild(this.item("Screenshot", this.doScreenShot));
//    }

    menu.appendChild(this.item("Inspect element", this.doInspectElement));
    menu.appendChild(this.item("Note down element", this.doNoteDownElement));
    menu.appendChild(this.item("Read element text", this.doFetchElementText));
    menu.appendChild(this.item("Show element text", this.doPrintElementText));
    menu.appendChild(this.item("Save element text", this.doSaveElementText));
    menu.appendChild(this.item("Save webpage screenshot", this.doScreenShot));
    menu.appendChild(this.item("Save element screenshot", this.doElementScreenShot));
    menu.appendChild(this.item("Hover cursor at element", this.doMoveCursorToElement));
    menu.appendChild(this.item("Wait for a few seconds", this.doExplicitWait));
    menu.appendChild(this.item("Close shortcuts menu", this.cancel));
//    menu.appendChild(this.item("Cancel the last step", this.doCancelLastStep));
  
    b.insertBefore(menu, b.firstChild);
    return menu;
}

TestRecorder.ContextMenu.prototype.item = function(text, func) {
    var doc = recorder.window.document;
    var div = doc.createElement("div");
    var txt = doc.createTextNode(text);
    div.setAttribute("style", "padding:6px;border:1px solid #ffffff;");
    div.style.border = "1px solid #ffffff";
    div.style.padding = "6px";
    div.appendChild(txt);
    div.onmouseover = this.onitemmouseover;
    div.onmouseout = this.onitemmouseout;
    div.onclick = func;
    return div;
}

TestRecorder.ContextMenu.prototype.show = function(e) {
    if (this.menu) {
        this.hide();
    }
    var wnd = recorder.window;
    var doc = wnd.document;
    this.target = e.target();
    TestRecorder.Browser.captureEvent(wnd, "mousedown", this.onmousedown);

    var wh = TestRecorder.Browser.windowHeight(wnd);
    var ww = TestRecorder.Browser.windowWidth(wnd);
    var x = e.posX();
    var y = e.posY();
    if ((ww >= 0) && ((ww - x) < 100)) {
        x = x - 100;
    } 
    if ((wh >= 0) && ((wh - y) < 100)) {
        y = y - 100;
    } 
    var menu = this.build(e.target(), x, y);
    this.menu = menu;
    menu.style.display = "";
    this.visible = true;
    return;
}

TestRecorder.ContextMenu.prototype.hide = function() {
    var wnd = recorder.window;
    TestRecorder.Browser.releaseEvent(wnd, "mousedown", this.onmousedown);
    var d = wnd.document;
    var b = d.getElementsByTagName("body").item(0);
    this.menu.style.display = "none" ;
    b.removeChild(this.menu);
    this.target = null;
    this.visible = false;
    this.over = false;
    this.menu = null;
}

TestRecorder.ContextMenu.prototype.onitemmouseover = function(e) {
    this.style.backgroundColor = "#efefef";
    this.style.border = "1px solid #c0c0c0";
    return true;
}

TestRecorder.ContextMenu.prototype.onitemmouseout = function(e) {
    this.style.backgroundColor = "#ffffff";
    this.style.border = "1px solid #ffffff";
    return true;
}

TestRecorder.ContextMenu.prototype.onmouseover = function(e) {
    contextmenu.over = true;
}

TestRecorder.ContextMenu.prototype.onmouseout = function(e) {
    contextmenu.over = false;
}

TestRecorder.ContextMenu.prototype.onmousedown = function(e) {
    if(contextmenu.visible) {
        if (contextmenu.over == false) {
            contextmenu.hide();
            return true;
        }
        return true ;
    }
    return false;
}

TestRecorder.ContextMenu.prototype.record = function(o) {
    recorder.testcase.append(o);
    recorder.log(o.type);
    contextmenu.hide();
}

TestRecorder.ContextMenu.prototype.checkPageTitle = function() {
    var doc = recorder.window.document;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.DocumentEvent(et.CheckPageTitle, doc);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doScreenShot = function() {
    var e = new TestRecorder.ScreenShotEvent();
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doElementScreenShot = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.ElementScreenShot, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doInspectElement = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.InspectElement, t);
    contextmenu.record(e);
  
    // pop-up to show identifier of element inspected
    var inspected_selector = e.info.selector;
    if (inspected_selector.charAt(0) == '#') {inspected_selector = inspected_selector.substring(1);}
    alert("The identifier for this element is ▹\n\n" + inspected_selector);
}

TestRecorder.ContextMenu.prototype.doNoteDownElement = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.NoteDownElement, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doMoveCursorToElement = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.MoveCursorToElement, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doFetchElementText = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.FetchElementText, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doSelectElementOption = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.SelectElementOption, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doPrintElementText = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.PrintElementText, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doSaveElementText = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.SaveElementText, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doExplicitWait = function() {
    var e = new TestRecorder.ExplicitWaitEvent();
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.doCancelLastStep = function() {
    var e = new TestRecorder.CancelLastStepEvent();
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.cancel = function() {
    contextmenu.hide();
    var e = new TestRecorder.CancelEvent();
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkPageLocation = function() {
    var doc = recorder.window.document;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.DocumentEvent(et.CheckPageLocation, doc);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkValue = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckValue, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkValueContains = function() {
    var s = contextmenu.selected;
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckValueContains, t, s);
    contextmenu.selected = null;
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.innertext = function(e) {
    var doc = recorder.window.document;
    if (document.createRange) {
        var r = recorder.window.document.createRange();
        r.selectNodeContents(e);
        return r.toString();
    } else {
        return e.innerText;
    }
}

TestRecorder.ContextMenu.prototype.checkText = function() {
    var t = contextmenu.target;
    var s = "";
    if (t.type == "button" || t.type == "submit") {
        s = t.value;
    }
    else {
        s = contextmenu.innertext(t);
    }
    s = recorder.strip(s);
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckText, t, s);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkTextPresent = function() {
    var t = contextmenu.target;
    var s = contextmenu.selected;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckTextPresent, t, s);
    contextmenu.selected = null;
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkHref = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckHref, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkEnabled = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckEnabled, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkDisabled = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckDisabled, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkSelectValue = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckSelectValue, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkSelectOptions = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckSelectOptions, t);
    contextmenu.record(e);
}

TestRecorder.ContextMenu.prototype.checkImgSrc = function() {
    var t = contextmenu.target;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.ElementEvent(et.CheckImageSrc, t);
    contextmenu.record(e);
}


//---------------------------------------------------------------------------
//Recorder -- a controller class that manages the recording of web browser
//activities to produce a test case.

//Instance Methods:

//start() -- start recording browser events.

//stop() -- stop recording browser events.

//reset() -- reset the recorder and initialize a new test case.

//---------------------------------------------------------------------------

TestRecorder.Recorder = function() {
    this.testcase = new TestRecorder.TestCase();
    this.logfunc = null;
    this.window = null;
    this.active = false;
}

//The recorder is a singleton -- there is no real reason to have more than
//one instance, and many of its methods are event handlers which need a
//stable reference to the instance.

recorder = new TestRecorder.Recorder();
recorder.logfunc = function(msg) {console.log(msg);};

TestRecorder.Recorder.prototype.start = function() {
    this.window = window;
    this.captureEvents();

    // OVERRIDE stopPropagation
    var actualCode = '(' + function() {
        var overloadStopPropagation = Event.prototype.stopPropagation;
        Event.prototype.stopPropagation = function(){
            console.log(this);
            overloadStopPropagation.apply(this, arguments);
        };
    } + ')();';
    var script = document.createElement('script');
    script.textContent = actualCode;
    (document.head||document.documentElement).appendChild(script);
    script.parentNode.removeChild(script);
    
    this.active = true;
    this.log("recorder started");
}

TestRecorder.Recorder.prototype.stop = function() {
    this.releaseEvents();
    this.active = false;
    this.log("recorder stopped");
    return;
}

TestRecorder.Recorder.prototype.open = function(url) {
    var e = new TestRecorder.OpenURLEvent(url);
    this.testcase.append(e);
    this.log("open url: " + url);
}

TestRecorder.Recorder.prototype.pageLoad = function() {
    var doc = recorder.window.document;
    var et = TestRecorder.EventTypes;
    var e = new TestRecorder.DocumentEvent(et.PageLoad, doc);
    this.testcase.append(e);
    this.log("page loaded url: " + e.url);
}

TestRecorder.Recorder.prototype.captureEvents = function() {
    var wnd = this.window;
    TestRecorder.Browser.captureEvent(wnd, "contextmenu", this.oncontextmenu);
    TestRecorder.Browser.captureEvent(wnd, "drag", this.ondrag);
    TestRecorder.Browser.captureEvent(wnd, "mousedown", this.onmousedown);
    TestRecorder.Browser.captureEvent(wnd, "mouseup", this.onmouseup);
    TestRecorder.Browser.captureEvent(wnd, "click", this.onclick);
    TestRecorder.Browser.captureEvent(wnd, "change", this.onchange);
    TestRecorder.Browser.captureEvent(wnd, "keypress", this.onkeypress);
    TestRecorder.Browser.captureEvent(wnd, "select", this.onselect);
    TestRecorder.Browser.captureEvent(wnd, "submit", this.onsubmit);
}

TestRecorder.Recorder.prototype.releaseEvents = function() {
    var wnd = this.window;
    TestRecorder.Browser.releaseEvent(wnd, "contextmenu", this.oncontextmenu);
    TestRecorder.Browser.releaseEvent(wnd, "drag", this.ondrag);
    TestRecorder.Browser.releaseEvent(wnd, "mousedown", this.onmousedown);
    TestRecorder.Browser.releaseEvent(wnd, "mouseup", this.onmouseup);
    TestRecorder.Browser.releaseEvent(wnd, "click", this.onclick);
    TestRecorder.Browser.releaseEvent(wnd, "change", this.onchange);
    TestRecorder.Browser.releaseEvent(wnd, "keypress", this.onkeypress);
    TestRecorder.Browser.releaseEvent(wnd, "select", this.onselect);
    TestRecorder.Browser.releaseEvent(wnd, "submit", this.onsubmit);
}



TestRecorder.Recorder.prototype.clickaction = function(e) {
    // This method is called by our low-level event handler when the mouse 
    // is clicked in normal mode. Its job is decide whether the click is
    // something we care about. If so, we record the event in the test case.
    //
    // If the context menu is visible, then the click is either over the 
    // menu (selecting a check) or out of the menu (cancelling it) so we 
    // always discard clicks that happen when the menu is visible.
    if (!contextmenu.visible) {
        var et = TestRecorder.EventTypes;
        var t = e.target();
        if (t.href || (t.type && t.type == "submit"))
        {
            this.testcase.append(new TestRecorder.ElementEvent(et.Click,e.target()));
        }
//        else if (t.selectedIndex || t.type == "option")
//        {
//            this.testcase.append(new TestRecorder.ElementEvent(et.SelectElementOption,e.target()));
//        }
        else {
            recorder.testcase.append(
                    new TestRecorder.MouseEvent(
                            TestRecorder.EventTypes.Click, e.target(), e.posX(), e.posY()
                    ));
        }
    }
}

TestRecorder.Recorder.prototype.addComment = function(text) {
    this.testcase.append(new TestRecorder.CommentEvent(text));
}

TestRecorder.Recorder.prototype.check = function(e) {
    // This method is called by our low-level event handler when the mouse 
    // is clicked in check mode. Its job is decide whether the click is
    // something we care about. If so, we record the check in the test case.
    contextmenu.show(e);
    var target = e.target();
    if (target.type) {
        var type = target.type.toLowerCase();
        if (type == "submit" || type == "button" || type == "image") {
            recorder.log('check button == "' + target.value + '"');
        }
    }
    else if (target.href) {
        if (target.innerText) {
            var text = recorder.strip(target.innerText);
            recorder.log('check link == "' + target.text + '"');
        }
    }
}

TestRecorder.Recorder.prototype.onpageload = function() {
    if (this.active) {
        // This must be called each time a new document is fully loaded into the
        // testing target frame to ensure that events are captured for the page.
        recorder.captureEvents();

        // if a new page has loaded, but there doesn't seem to be a reason why, 
        // then we need to record the fact or the information will be lost
        if (this.testcase.peek()) {
            var last_event_type = this.testcase.peek().type;
            if (last_event_type != TestRecorder.EventTypes.OpenUrl && 
                    last_event_type != TestRecorder.EventTypes.Click && 
                    last_event_type != TestRecorder.EventTypes.Submit) {
                this.open(this.window.location.toString());
            }
        }

        // record the fact that a page load happened
        if (this.window)
            this.pageLoad();
    }
}

TestRecorder.Recorder.prototype.onchange = function(e) {
    var e = new TestRecorder.Event(e);
    var et = TestRecorder.EventTypes;
    var t = e.target();
    if (t.selectedIndex || t.type == "option")
    {
        var v = new TestRecorder.ElementEvent(et.SelectElementOption, e.target());
        recorder.testcase.append(v);
        recorder.log("value changed: " + e.target().value);
    }
    else
    {
        var v = new TestRecorder.ElementEvent(et.Change, e.target());
        recorder.testcase.append(v);
        recorder.log("value changed: " + e.target().value);
    }
}

TestRecorder.Recorder.prototype.onselect = function(e) {
    var e = new TestRecorder.Event(e);
    recorder.log("select: " + e.target());
//    var e = new TestRecorder.Event(e);
//    var et = TestRecorder.EventTypes;
//    var v = new TestRecorder.ElementEvent(et.SelectElementOption, e.target());
//    recorder.testcase.append(v);
//    recorder.log("select: " + e.target().value);
}

TestRecorder.Recorder.prototype.onsubmit = function(e) {
    var e = new TestRecorder.Event(e);
    var et = TestRecorder.EventTypes;
    // We want to save the form element as the event target
    var t = e.target();
    while (t.parentNode && t.tagName != "FORM") {
        t = t.parentNode;
    }
    var v = new TestRecorder.ElementEvent(et.Submit, t);
    recorder.testcase.append(v);
    recorder.log("submit: " + e.target());
}

TestRecorder.Recorder.prototype.ondrag = function(e) {
    var e = new TestRecorder.Event(e);
    recorder.testcase.append(
            new TestRecorder.MouseEvent(
                    TestRecorder.EventTypes.MouseDrag, e.target(), e.posX(), e.posY()
            ));
}
TestRecorder.Recorder.prototype.onmousedown = function(e) {
    if(!contextmenu.visible) {
        var e = new TestRecorder.Event(e);
        if (e.button() == TestRecorder.Event.LeftButton) {
            recorder.testcase.append(
                new TestRecorder.MouseEvent(
                        TestRecorder.EventTypes.MouseDown, e.target(), e.posX(), e.posY()
                ));
        }
    }
}
TestRecorder.Recorder.prototype.onmouseup = function(e) {
    if(!contextmenu.visible) {
        var e = new TestRecorder.Event(e);
        if (e.button() == TestRecorder.Event.LeftButton) {
            recorder.testcase.append(
                    new TestRecorder.MouseEvent(
                            TestRecorder.EventTypes.MouseUp, e.target(), e.posX(), e.posY()
                    ));
        }
    }
}
//The dance here between onclick and oncontextmenu requires a bit of 
//explanation. IE and Moz/Firefox have wildly different behaviors when 
//a right-click occurs. IE6 fires only an oncontextmenu event; Firefox 
//gets an onclick event first followed by an oncontextment event. So 
//to do the right thing here, we need to silently consume oncontextmenu
//on Firefox, and reroute oncontextmenu to look like a click event for 
//IE. In both cases, we need to prevent the default action for cmenu.

TestRecorder.Recorder.prototype.onclick = function(e) {
    var e = new TestRecorder.Event(e);

    if (e.shiftkey()) {
        recorder.check(e);
        e.stopPropagation();
        e.preventDefault();
        return false;
    }

    if (e.button() == TestRecorder.Event.RightButton) {
        recorder.check(e);
        return true;
    } else if (e.button() == TestRecorder.Event.LeftButton) {
        recorder.clickaction(e);
        return true;
    }
    e.stopPropagation();
    e.preventDefault();
    return false;
}

TestRecorder.Recorder.prototype.oncontextmenu = function(e) {
    var e = new TestRecorder.Event(e);
    recorder.check(e);
    e.stopPropagation();
    e.preventDefault();
    return false;
}

TestRecorder.Recorder.prototype.onkeypress = function(e) {
    var e = new TestRecorder.Event(e);
//    if (e.shiftkey() && (e.keychar() == 'C')) {
//        // TODO show comment box here
//    }
//    if (e.shiftkey() && (e.keychar() == 'S')) {
//        recorder.testcase.append(new TestRecorder.ScreenShotEvent());
//        e.stopPropagation();
//        e.preventDefault();
//        return false;
//    }
    
    var last = recorder.testcase.peek();
    if(last.type == TestRecorder.EventTypes.KeyPress) {
        last.text = last.text + e.keychar();
        recorder.testcase.poke(last);
    } else {
        recorder.testcase.append(
            new TestRecorder.KeyEvent(e.target(), e.keychar())
        );
    }
    return true;
}

TestRecorder.Recorder.prototype.strip = function(s) {
//    return s.replace('\n', ' ').replace(/^\s*/, "").replace(/\s*$/, "");
//  change code to replace with [whitespace] as it will break element text match if simply strip
    return s.replace(/^\s+/, "[whitespace]").replace(/\s+$/, "[whitespace]");
}

TestRecorder.Recorder.prototype.log = function(text) {
    if (this.logfunc) {
        this.logfunc(text);
    }
}

chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
    if (request.action == "start") {
        recorder.start();
        sendResponse({});
    }
    if (request.action == "stop") {
        recorder.stop();
        sendResponse({});
    }
    if (request.action == "open") {
        recorder.open(request.url);
        sendResponse({});
    }
    if (request.action == "addComment") {
        recorder.addComment(request.text);
        sendResponse({});
    }
});

//get current status from background
chrome.runtime.sendMessage({action: "get_status"}, function(response) {
    if (response.active) {
        recorder.start();
    }
});
