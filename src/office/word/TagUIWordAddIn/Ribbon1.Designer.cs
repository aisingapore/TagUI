
namespace TagUIWordAddIn
{
    partial class Ribbon1 : Microsoft.Office.Tools.Ribbon.RibbonBase
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        public Ribbon1()
            : base(Globals.Factory.GetRibbonFactory())
        {
            InitializeComponent();
        }

        /// <summary> 
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Component Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(Ribbon1));
            this.tab1 = this.Factory.CreateRibbonTab();
            this.group1 = this.Factory.CreateRibbonGroup();
            this.buttonRun = this.Factory.CreateRibbonButton();
            this.buttonDeploy = this.Factory.CreateRibbonButton();
            this.group4 = this.Factory.CreateRibbonGroup();
            this.toggleButtonShowTaskPane = this.Factory.CreateRibbonToggleButton();
            this.group3 = this.Factory.CreateRibbonGroup();
            this.buttonSnapshot = this.Factory.CreateRibbonButton();
            this.buttonLive = this.Factory.CreateRibbonButton();
            this.group2 = this.Factory.CreateRibbonGroup();
            this.buttonClick = this.Factory.CreateRibbonButton();
            this.buttonRclick = this.Factory.CreateRibbonButton();
            this.buttonDclick = this.Factory.CreateRibbonButton();
            this.buttonType = this.Factory.CreateRibbonButton();
            this.buttonSelect = this.Factory.CreateRibbonButton();
            this.buttonHover = this.Factory.CreateRibbonButton();
            this.buttonRead = this.Factory.CreateRibbonButton();
            this.buttonShow = this.Factory.CreateRibbonButton();
            this.buttonSave = this.Factory.CreateRibbonButton();
            this.buttonEcho = this.Factory.CreateRibbonButton();
            this.buttonAsk = this.Factory.CreateRibbonButton();
            this.buttonSnap = this.Factory.CreateRibbonButton();
            this.buttonLoad = this.Factory.CreateRibbonButton();
            this.buttonDump = this.Factory.CreateRibbonButton();
            this.buttonWrite = this.Factory.CreateRibbonButton();
            this.group6 = this.Factory.CreateRibbonGroup();
            this.buttonIf = this.Factory.CreateRibbonButton();
            this.buttonLoop = this.Factory.CreateRibbonButton();
            this.buttonBreak = this.Factory.CreateRibbonButton();
            this.group5 = this.Factory.CreateRibbonGroup();
            this.buttonLiveStep = this.Factory.CreateRibbonButton();
            this.buttonExcel = this.Factory.CreateRibbonButton();
            this.buttonTelegram = this.Factory.CreateRibbonButton();
            this.buttonKeyboard = this.Factory.CreateRibbonButton();
            this.buttonMouse = this.Factory.CreateRibbonButton();
            this.buttonTagui = this.Factory.CreateRibbonButton();
            this.buttonWait = this.Factory.CreateRibbonButton();
            this.buttonTimeout = this.Factory.CreateRibbonButton();
            this.buttonTable = this.Factory.CreateRibbonButton();
            this.buttonFrame = this.Factory.CreateRibbonButton();
            this.buttonPopup = this.Factory.CreateRibbonButton();
            this.buttonVision = this.Factory.CreateRibbonButton();
            this.buttonRunStep = this.Factory.CreateRibbonButton();
            this.buttonApi = this.Factory.CreateRibbonButton();
            this.buttonDom = this.Factory.CreateRibbonButton();
            this.buttonJs = this.Factory.CreateRibbonButton();
            this.buttonPy = this.Factory.CreateRibbonButton();
            this.buttonR = this.Factory.CreateRibbonButton();
            this.group7 = this.Factory.CreateRibbonGroup();
            this.buttonCount = this.Factory.CreateRibbonButton();
            this.buttonExist = this.Factory.CreateRibbonButton();
            this.buttonPresent = this.Factory.CreateRibbonButton();
            this.buttonClipboard = this.Factory.CreateRibbonButton();
            this.buttonCsvRow = this.Factory.CreateRibbonButton();
            this.buttonTimer = this.Factory.CreateRibbonButton();
            this.buttonUrl = this.Factory.CreateRibbonButton();
            this.buttonTitle = this.Factory.CreateRibbonButton();
            this.buttonText = this.Factory.CreateRibbonButton();
            this.buttonDelChars = this.Factory.CreateRibbonButton();
            this.buttonGetText = this.Factory.CreateRibbonButton();
            this.buttonGetEnv = this.Factory.CreateRibbonButton();
            this.group8 = this.Factory.CreateRibbonGroup();
            this.buttonSettings = this.Factory.CreateRibbonButton();
            this.buttonUpdate = this.Factory.CreateRibbonButton();
            this.buttonUsageGuide = this.Factory.CreateRibbonButton();
            this.tab1.SuspendLayout();
            this.group1.SuspendLayout();
            this.group4.SuspendLayout();
            this.group3.SuspendLayout();
            this.group2.SuspendLayout();
            this.group6.SuspendLayout();
            this.group5.SuspendLayout();
            this.group7.SuspendLayout();
            this.group8.SuspendLayout();
            this.SuspendLayout();
            // 
            // tab1
            // 
            this.tab1.Groups.Add(this.group1);
            this.tab1.Groups.Add(this.group4);
            this.tab1.Groups.Add(this.group3);
            this.tab1.Groups.Add(this.group2);
            this.tab1.Groups.Add(this.group6);
            this.tab1.Groups.Add(this.group5);
            this.tab1.Groups.Add(this.group7);
            this.tab1.Groups.Add(this.group8);
            this.tab1.Label = "TagUI RPA";
            this.tab1.Name = "tab1";
            // 
            // group1
            // 
            this.group1.Items.Add(this.buttonRun);
            this.group1.Items.Add(this.buttonDeploy);
            this.group1.Label = "Run / Deploy";
            this.group1.Name = "group1";
            // 
            // buttonRun
            // 
            this.buttonRun.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonRun.Image = ((System.Drawing.Image)(resources.GetObject("buttonRun.Image")));
            this.buttonRun.Label = "Run";
            this.buttonRun.Name = "buttonRun";
            this.buttonRun.ShowImage = true;
            this.buttonRun.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonRun_Click);
            // 
            // buttonDeploy
            // 
            this.buttonDeploy.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonDeploy.Image = ((System.Drawing.Image)(resources.GetObject("buttonDeploy.Image")));
            this.buttonDeploy.Label = "Deploy";
            this.buttonDeploy.Name = "buttonDeploy";
            this.buttonDeploy.ShowImage = true;
            this.buttonDeploy.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonDeploy_Click);
            // 
            // group4
            // 
            this.group4.Items.Add(this.toggleButtonShowTaskPane);
            this.group4.Label = "Options / Output";
            this.group4.Name = "group4";
            // 
            // toggleButtonShowTaskPane
            // 
            this.toggleButtonShowTaskPane.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.toggleButtonShowTaskPane.Image = ((System.Drawing.Image)(resources.GetObject("toggleButtonShowTaskPane.Image")));
            this.toggleButtonShowTaskPane.Label = "Show Task Pane";
            this.toggleButtonShowTaskPane.Name = "toggleButtonShowTaskPane";
            this.toggleButtonShowTaskPane.ShowImage = true;
            this.toggleButtonShowTaskPane.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.toggleButtonShowTaskPane_Click);
            // 
            // group3
            // 
            this.group3.Items.Add(this.buttonSnapshot);
            this.group3.Items.Add(this.buttonLive);
            this.group3.Label = "Helper Tools";
            this.group3.Name = "group3";
            // 
            // buttonSnapshot
            // 
            this.buttonSnapshot.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonSnapshot.Image = ((System.Drawing.Image)(resources.GetObject("buttonSnapshot.Image")));
            this.buttonSnapshot.Label = "Snapshot";
            this.buttonSnapshot.Name = "buttonSnapshot";
            this.buttonSnapshot.ShowImage = true;
            this.buttonSnapshot.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonSnapshot_Click);
            // 
            // buttonLive
            // 
            this.buttonLive.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonLive.Image = ((System.Drawing.Image)(resources.GetObject("buttonLive.Image")));
            this.buttonLive.Label = "Live Mode";
            this.buttonLive.Name = "buttonLive";
            this.buttonLive.ShowImage = true;
            this.buttonLive.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonLive_Click);
            // 
            // group2
            // 
            this.group2.Items.Add(this.buttonClick);
            this.group2.Items.Add(this.buttonRclick);
            this.group2.Items.Add(this.buttonDclick);
            this.group2.Items.Add(this.buttonType);
            this.group2.Items.Add(this.buttonSelect);
            this.group2.Items.Add(this.buttonHover);
            this.group2.Items.Add(this.buttonRead);
            this.group2.Items.Add(this.buttonShow);
            this.group2.Items.Add(this.buttonSave);
            this.group2.Items.Add(this.buttonEcho);
            this.group2.Items.Add(this.buttonAsk);
            this.group2.Items.Add(this.buttonSnap);
            this.group2.Items.Add(this.buttonLoad);
            this.group2.Items.Add(this.buttonDump);
            this.group2.Items.Add(this.buttonWrite);
            this.group2.Label = "Basic Steps";
            this.group2.Name = "group2";
            // 
            // buttonClick
            // 
            this.buttonClick.Label = "click";
            this.buttonClick.Name = "buttonClick";
            this.buttonClick.ScreenTip = "click";
            this.buttonClick.SuperTip = "click on an element";
            this.buttonClick.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonClick_Click);
            // 
            // buttonRclick
            // 
            this.buttonRclick.Label = "rclick";
            this.buttonRclick.Name = "buttonRclick";
            this.buttonRclick.ScreenTip = "rclick";
            this.buttonRclick.SuperTip = "right-click on an element";
            this.buttonRclick.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonRclick_Click);
            // 
            // buttonDclick
            // 
            this.buttonDclick.Label = "dclick";
            this.buttonDclick.Name = "buttonDclick";
            this.buttonDclick.ScreenTip = "dclick";
            this.buttonDclick.SuperTip = "double-click on an element";
            this.buttonDclick.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonDclick_Click);
            // 
            // buttonType
            // 
            this.buttonType.Label = "type";
            this.buttonType.Name = "buttonType";
            this.buttonType.ScreenTip = "type";
            this.buttonType.SuperTip = "enter text into element";
            this.buttonType.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonType_Click);
            // 
            // buttonSelect
            // 
            this.buttonSelect.Label = "select";
            this.buttonSelect.Name = "buttonSelect";
            this.buttonSelect.ScreenTip = "select";
            this.buttonSelect.SuperTip = "choose dropdown option";
            this.buttonSelect.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonSelect_Click);
            // 
            // buttonHover
            // 
            this.buttonHover.Label = "hover";
            this.buttonHover.Name = "buttonHover";
            this.buttonHover.ScreenTip = "hover";
            this.buttonHover.SuperTip = "move cursor to element";
            this.buttonHover.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonHover_Click);
            // 
            // buttonRead
            // 
            this.buttonRead.Label = "read";
            this.buttonRead.Name = "buttonRead";
            this.buttonRead.ScreenTip = "read";
            this.buttonRead.SuperTip = "fetch element text to variable";
            this.buttonRead.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonRead_Click);
            // 
            // buttonShow
            // 
            this.buttonShow.Label = "show";
            this.buttonShow.Name = "buttonShow";
            this.buttonShow.ScreenTip = "show";
            this.buttonShow.SuperTip = "\tprint element text to output";
            this.buttonShow.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonShow_Click);
            // 
            // buttonSave
            // 
            this.buttonSave.Label = "save";
            this.buttonSave.Name = "buttonSave";
            this.buttonSave.ScreenTip = "save";
            this.buttonSave.SuperTip = "save element text to file";
            this.buttonSave.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonSave_Click);
            // 
            // buttonEcho
            // 
            this.buttonEcho.Label = "echo";
            this.buttonEcho.Name = "buttonEcho";
            this.buttonEcho.ScreenTip = "echo";
            this.buttonEcho.SuperTip = "print text/variables to output";
            this.buttonEcho.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonEcho_Click);
            // 
            // buttonAsk
            // 
            this.buttonAsk.Label = "ask";
            this.buttonAsk.Name = "buttonAsk";
            this.buttonAsk.ScreenTip = "ask";
            this.buttonAsk.SuperTip = "ask user for input";
            this.buttonAsk.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonAsk_Click);
            // 
            // buttonSnap
            // 
            this.buttonSnap.Label = "snap";
            this.buttonSnap.Name = "buttonSnap";
            this.buttonSnap.ScreenTip = "snap";
            this.buttonSnap.SuperTip = "save screenshot to file";
            this.buttonSnap.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonSnap_Click);
            // 
            // buttonLoad
            // 
            this.buttonLoad.Label = "load";
            this.buttonLoad.Name = "buttonLoad";
            this.buttonLoad.ScreenTip = "load";
            this.buttonLoad.SuperTip = "load file content to variable";
            this.buttonLoad.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonLoad_Click);
            // 
            // buttonDump
            // 
            this.buttonDump.Label = "dump";
            this.buttonDump.Name = "buttonDump";
            this.buttonDump.ScreenTip = "dump";
            this.buttonDump.SuperTip = "save text/variables to file";
            this.buttonDump.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonDump_Click);
            // 
            // buttonWrite
            // 
            this.buttonWrite.Label = "write";
            this.buttonWrite.Name = "buttonWrite";
            this.buttonWrite.ScreenTip = "write";
            this.buttonWrite.SuperTip = "append text/variables to file";
            this.buttonWrite.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonWrite_Click);
            // 
            // group6
            // 
            this.group6.Items.Add(this.buttonIf);
            this.group6.Items.Add(this.buttonLoop);
            this.group6.Items.Add(this.buttonBreak);
            this.group6.Label = "Conditions";
            this.group6.Name = "group6";
            // 
            // buttonIf
            // 
            this.buttonIf.Label = "if condition";
            this.buttonIf.Name = "buttonIf";
            this.buttonIf.ScreenTip = "if condition";
            this.buttonIf.SuperTip = "examples: if day equals to \"Friday\" / if menu contains \"fruits\" / if A more than " +
    "B and C lesser than 10";
            this.buttonIf.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonIf_Click);
            // 
            // buttonLoop
            // 
            this.buttonLoop.Label = "for loop";
            this.buttonLoop.Name = "buttonLoop";
            this.buttonLoop.ScreenTip = "for loop";
            this.buttonLoop.SuperTip = "loop steps n times";
            this.buttonLoop.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonLoop_Click);
            // 
            // buttonBreak
            // 
            this.buttonBreak.Label = "break";
            this.buttonBreak.Name = "buttonBreak";
            this.buttonBreak.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonBreak_Click);
            // 
            // group5
            // 
            this.group5.Items.Add(this.buttonLiveStep);
            this.group5.Items.Add(this.buttonExcel);
            this.group5.Items.Add(this.buttonTelegram);
            this.group5.Items.Add(this.buttonKeyboard);
            this.group5.Items.Add(this.buttonMouse);
            this.group5.Items.Add(this.buttonTagui);
            this.group5.Items.Add(this.buttonWait);
            this.group5.Items.Add(this.buttonTimeout);
            this.group5.Items.Add(this.buttonTable);
            this.group5.Items.Add(this.buttonFrame);
            this.group5.Items.Add(this.buttonPopup);
            this.group5.Items.Add(this.buttonVision);
            this.group5.Items.Add(this.buttonRunStep);
            this.group5.Items.Add(this.buttonApi);
            this.group5.Items.Add(this.buttonDom);
            this.group5.Items.Add(this.buttonJs);
            this.group5.Items.Add(this.buttonPy);
            this.group5.Items.Add(this.buttonR);
            this.group5.Label = "Pro Steps";
            this.group5.Name = "group5";
            // 
            // buttonLiveStep
            // 
            this.buttonLiveStep.Label = "live";
            this.buttonLiveStep.Name = "buttonLiveStep";
            this.buttonLiveStep.ScreenTip = "live";
            this.buttonLiveStep.SuperTip = "run steps interactively and immediately see the output";
            this.buttonLiveStep.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonLiveStep_Click);
            // 
            // buttonExcel
            // 
            this.buttonExcel.Label = "excel";
            this.buttonExcel.Name = "buttonExcel";
            this.buttonExcel.ScreenTip = "excel";
            this.buttonExcel.SuperTip = "perform read, write, copy, delete actions on Excel files using standard Excel for" +
    "mula like this one [workbook]sheet!range";
            this.buttonExcel.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonExcel_Click);
            // 
            // buttonTelegram
            // 
            this.buttonTelegram.Label = "telegram";
            this.buttonTelegram.Name = "buttonTelegram";
            this.buttonTelegram.ScreenTip = "telegram";
            this.buttonTelegram.SuperTip = "send a Telegram notification, for example, to update on automation completion or " +
    "exception";
            this.buttonTelegram.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonTelegram_Click);
            // 
            // buttonKeyboard
            // 
            this.buttonKeyboard.Label = "keyboard";
            this.buttonKeyboard.Name = "buttonKeyboard";
            this.buttonKeyboard.ScreenTip = "keyboard";
            this.buttonKeyboard.SuperTip = resources.GetString("buttonKeyboard.SuperTip");
            this.buttonKeyboard.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonKeyboard_Click);
            // 
            // buttonMouse
            // 
            this.buttonMouse.Label = "mouse";
            this.buttonMouse.Name = "buttonMouse";
            this.buttonMouse.ScreenTip = "mouse";
            this.buttonMouse.SuperTip = "send mouse event to screen";
            this.buttonMouse.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonMouse_Click);
            // 
            // buttonTagui
            // 
            this.buttonTagui.Label = "tagui";
            this.buttonTagui.Name = "buttonTagui";
            this.buttonTagui.ScreenTip = "tagui";
            this.buttonTagui.SuperTip = "run another TagUI flow";
            this.buttonTagui.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonTagui_Click);
            // 
            // buttonWait
            // 
            this.buttonWait.Label = "wait";
            this.buttonWait.Name = "buttonWait";
            this.buttonWait.ScreenTip = "wait";
            this.buttonWait.SuperTip = "explicitly wait for some time";
            this.buttonWait.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonWait_Click);
            // 
            // buttonTimeout
            // 
            this.buttonTimeout.Label = "timeout";
            this.buttonTimeout.Name = "buttonTimeout";
            this.buttonTimeout.ScreenTip = "timeout";
            this.buttonTimeout.SuperTip = "change auto-wait timeout";
            this.buttonTimeout.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonTimeout_Click);
            // 
            // buttonTable
            // 
            this.buttonTable.Label = "table";
            this.buttonTable.Name = "buttonTable";
            this.buttonTable.ScreenTip = "table";
            this.buttonTable.SuperTip = "save basic html table to csv";
            this.buttonTable.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonTable_Click);
            // 
            // buttonFrame
            // 
            this.buttonFrame.Label = "frame";
            this.buttonFrame.Name = "buttonFrame";
            this.buttonFrame.ScreenTip = "frame";
            this.buttonFrame.SuperTip = "next step or block in frame/subframe";
            this.buttonFrame.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonFrame_Click);
            // 
            // buttonPopup
            // 
            this.buttonPopup.Label = "popup";
            this.buttonPopup.Name = "buttonPopup";
            this.buttonPopup.ScreenTip = "popup";
            this.buttonPopup.SuperTip = "next step or block in new tab";
            this.buttonPopup.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonPopup_Click);
            // 
            // buttonVision
            // 
            this.buttonVision.Label = "vision";
            this.buttonVision.Name = "buttonVision";
            this.buttonVision.ScreenTip = "vision";
            this.buttonVision.SuperTip = "run custom SikuliX commands";
            this.buttonVision.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonVision_Click);
            // 
            // buttonRunStep
            // 
            this.buttonRunStep.Label = "run";
            this.buttonRunStep.Name = "buttonRunStep";
            this.buttonRunStep.ScreenTip = "run";
            this.buttonRunStep.SuperTip = "run OS command & save to run_result variable";
            this.buttonRunStep.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonRunStep_Click);
            // 
            // buttonApi
            // 
            this.buttonApi.Label = "api";
            this.buttonApi.Name = "buttonApi";
            this.buttonApi.ScreenTip = "api";
            this.buttonApi.SuperTip = "call API & save response to api_result variable";
            this.buttonApi.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonApi_Click);
            // 
            // buttonDom
            // 
            this.buttonDom.Label = "dom";
            this.buttonDom.Name = "buttonDom";
            this.buttonDom.ScreenTip = "dom";
            this.buttonDom.SuperTip = "run code in dom & save to dom_result variable";
            this.buttonDom.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonDom_Click);
            // 
            // buttonJs
            // 
            this.buttonJs.Label = "js";
            this.buttonJs.Name = "buttonJs";
            this.buttonJs.ScreenTip = "js";
            this.buttonJs.SuperTip = "treat as JavaScript code explicitly";
            this.buttonJs.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonJs_Click);
            // 
            // buttonPy
            // 
            this.buttonPy.Label = "py";
            this.buttonPy.Name = "buttonPy";
            this.buttonPy.ScreenTip = "py";
            this.buttonPy.SuperTip = "run python code & save to py_result variable";
            this.buttonPy.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonPy_Click);
            // 
            // buttonR
            // 
            this.buttonR.Label = "r";
            this.buttonR.Name = "buttonR";
            this.buttonR.ScreenTip = "r";
            this.buttonR.SuperTip = "run R statements & save to r_result";
            this.buttonR.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonR_Click);
            // 
            // group7
            // 
            this.group7.Items.Add(this.buttonCount);
            this.group7.Items.Add(this.buttonExist);
            this.group7.Items.Add(this.buttonPresent);
            this.group7.Items.Add(this.buttonClipboard);
            this.group7.Items.Add(this.buttonCsvRow);
            this.group7.Items.Add(this.buttonTimer);
            this.group7.Items.Add(this.buttonUrl);
            this.group7.Items.Add(this.buttonTitle);
            this.group7.Items.Add(this.buttonText);
            this.group7.Items.Add(this.buttonDelChars);
            this.group7.Items.Add(this.buttonGetText);
            this.group7.Items.Add(this.buttonGetEnv);
            this.group7.Label = "Helper Functions";
            this.group7.Name = "group7";
            // 
            // buttonCount
            // 
            this.buttonCount.Label = "count()";
            this.buttonCount.Name = "buttonCount";
            this.buttonCount.ScreenTip = "count(\'element\')";
            this.buttonCount.SuperTip = "return number of elements matching element identifier";
            this.buttonCount.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonCount_Click);
            // 
            // buttonExist
            // 
            this.buttonExist.Label = "exist()";
            this.buttonExist.Name = "buttonExist";
            this.buttonExist.ScreenTip = "exist(\'element\')";
            this.buttonExist.SuperTip = "wait until timeout for an element to exist and return true or false depending on " +
    "whether it exists";
            this.buttonExist.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonExist_Click);
            // 
            // buttonPresent
            // 
            this.buttonPresent.Label = "present()";
            this.buttonPresent.Name = "buttonPresent";
            this.buttonPresent.ScreenTip = "present(\'element\')";
            this.buttonPresent.SuperTip = "return true or false whether element identifier is present";
            this.buttonPresent.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonPresent_Click);
            // 
            // buttonClipboard
            // 
            this.buttonClipboard.Label = "clipboard()";
            this.buttonClipboard.Name = "buttonClipboard";
            this.buttonClipboard.ScreenTip = "clipboard()";
            this.buttonClipboard.SuperTip = "return clipboard text (eg to access text copied with keyboard step), alternativel" +
    "y use clipboard(‘text’) to copy some text to clipboard";
            this.buttonClipboard.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonClipboard_Click);
            // 
            // buttonCsvRow
            // 
            this.buttonCsvRow.Label = "csv_row()";
            this.buttonCsvRow.Name = "buttonCsvRow";
            this.buttonCsvRow.ScreenTip = "csv_row(row_array)";
            this.buttonCsvRow.SuperTip = "return formatted string for writing to csv file, eg using write step";
            this.buttonCsvRow.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonCsvRow_Click);
            // 
            // buttonTimer
            // 
            this.buttonTimer.Label = "timer()";
            this.buttonTimer.Name = "buttonTimer";
            this.buttonTimer.ScreenTip = "timer()";
            this.buttonTimer.SuperTip = "return time elapsed between calls";
            this.buttonTimer.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonTimer_Click);
            // 
            // buttonUrl
            // 
            this.buttonUrl.Label = "url()";
            this.buttonUrl.Name = "buttonUrl";
            this.buttonUrl.ScreenTip = "url()";
            this.buttonUrl.SuperTip = "return page URL of current web page";
            this.buttonUrl.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonUrl_Click);
            // 
            // buttonTitle
            // 
            this.buttonTitle.Label = "title()";
            this.buttonTitle.Name = "buttonTitle";
            this.buttonTitle.ScreenTip = "title()";
            this.buttonTitle.SuperTip = "return title of current web page";
            this.buttonTitle.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonTitle_Click);
            // 
            // buttonText
            // 
            this.buttonText.Label = "text()";
            this.buttonText.Name = "buttonText";
            this.buttonText.ScreenTip = "text()";
            this.buttonText.SuperTip = "return text of current web page";
            this.buttonText.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonText_Click);
            // 
            // buttonDelChars
            // 
            this.buttonDelChars.Label = "del_chars()";
            this.buttonDelChars.Name = "buttonDelChars";
            this.buttonDelChars.ScreenTip = "del_chars()";
            this.buttonDelChars.SuperTip = "clean data by removing provided character(s) from given text and returning the re" +
    "sult";
            this.buttonDelChars.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonDelChars_Click);
            // 
            // buttonGetText
            // 
            this.buttonGetText.Label = "get_text()";
            this.buttonGetText.Name = "buttonGetText";
            this.buttonGetText.ScreenTip = "get_text()";
            this.buttonGetText.SuperTip = "extract text between 2 provided anchors from given text. Optional 4th parameter f" +
    "or occurrence during multiple matches (for example 3 to tell the function to ret" +
    "urn the 3rd match found)";
            this.buttonGetText.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonGetText_Click);
            // 
            // buttonGetEnv
            // 
            this.buttonGetEnv.Label = "get_env()";
            this.buttonGetEnv.Name = "buttonGetEnv";
            this.buttonGetEnv.ScreenTip = "get_env()";
            this.buttonGetEnv.SuperTip = "return the value of given environment variable from the operating system";
            this.buttonGetEnv.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonGetEnv_Click);
            // 
            // group8
            // 
            this.group8.Items.Add(this.buttonSettings);
            this.group8.Items.Add(this.buttonUpdate);
            this.group8.Items.Add(this.buttonUsageGuide);
            this.group8.Label = "Help";
            this.group8.Name = "group8";
            // 
            // buttonSettings
            // 
            this.buttonSettings.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonSettings.Image = ((System.Drawing.Image)(resources.GetObject("buttonSettings.Image")));
            this.buttonSettings.Label = "Settings";
            this.buttonSettings.Name = "buttonSettings";
            this.buttonSettings.ShowImage = true;
            this.buttonSettings.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonSettings_Click);
            // 
            // buttonUpdate
            // 
            this.buttonUpdate.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonUpdate.Image = ((System.Drawing.Image)(resources.GetObject("buttonUpdate.Image")));
            this.buttonUpdate.Label = "Update TagUI";
            this.buttonUpdate.Name = "buttonUpdate";
            this.buttonUpdate.ShowImage = true;
            this.buttonUpdate.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonUpdate_Click);
            // 
            // buttonUsageGuide
            // 
            this.buttonUsageGuide.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonUsageGuide.Image = ((System.Drawing.Image)(resources.GetObject("buttonUsageGuide.Image")));
            this.buttonUsageGuide.Label = "Usage Guide";
            this.buttonUsageGuide.Name = "buttonUsageGuide";
            this.buttonUsageGuide.ShowImage = true;
            this.buttonUsageGuide.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonUsageGuide_Click);
            // 
            // Ribbon1
            // 
            this.Name = "Ribbon1";
            this.RibbonType = "Microsoft.Word.Document";
            this.Tabs.Add(this.tab1);
            this.tab1.ResumeLayout(false);
            this.tab1.PerformLayout();
            this.group1.ResumeLayout(false);
            this.group1.PerformLayout();
            this.group4.ResumeLayout(false);
            this.group4.PerformLayout();
            this.group3.ResumeLayout(false);
            this.group3.PerformLayout();
            this.group2.ResumeLayout(false);
            this.group2.PerformLayout();
            this.group6.ResumeLayout(false);
            this.group6.PerformLayout();
            this.group5.ResumeLayout(false);
            this.group5.PerformLayout();
            this.group7.ResumeLayout(false);
            this.group7.PerformLayout();
            this.group8.ResumeLayout(false);
            this.group8.PerformLayout();
            this.ResumeLayout(false);

        }

        #endregion

        internal Microsoft.Office.Tools.Ribbon.RibbonTab tab1;
        internal Microsoft.Office.Tools.Ribbon.RibbonToggleButton toggleButtonShowTaskPane;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonSnapshot;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group3;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group4;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group1;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonRun;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonDeploy;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group2;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonClick;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonType;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonRead;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonIf;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonLoop;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonRclick;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonDclick;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonSelect;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonShow;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonSave;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonSnap;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonLoad;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonEcho;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonDump;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonWrite;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonAsk;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group5;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonTagui;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonKeyboard;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonMouse;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonTable;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonWait;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonFrame;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonPopup;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonApi;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonRunStep;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonDom;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonJs;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group6;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonR;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonPy;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonVision;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonTimeout;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group7;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonCsvRow;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonPresent;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonExist;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonCount;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonClipboard;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonUrl;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonTitle;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonText;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonTimer;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonHover;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group8;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonUsageGuide;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonUpdate;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonSettings;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonLive;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonBreak;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonDelChars;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonGetText;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonGetEnv;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonLiveStep;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonExcel;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonTelegram;
    }

    partial class ThisRibbonCollection
    {
        internal Ribbon1 Ribbon1
        {
            get { return this.GetRibbon<Ribbon1>(); }
        }
    }
}
