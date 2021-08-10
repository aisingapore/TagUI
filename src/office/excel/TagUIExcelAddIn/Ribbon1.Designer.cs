
namespace TagUIExcelAddIn
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
            this.group2 = this.Factory.CreateRibbonGroup();
            this.toggleButton1 = this.Factory.CreateRibbonToggleButton();
            this.group5 = this.Factory.CreateRibbonGroup();
            this.buttonViewReport = this.Factory.CreateRibbonButton();
            this.group4 = this.Factory.CreateRibbonGroup();
            this.buttonUpdate = this.Factory.CreateRibbonButton();
            this.buttonUsageGuide = this.Factory.CreateRibbonButton();
            this.tab1.SuspendLayout();
            this.group1.SuspendLayout();
            this.group2.SuspendLayout();
            this.group5.SuspendLayout();
            this.group4.SuspendLayout();
            this.SuspendLayout();
            // 
            // tab1
            // 
            this.tab1.Groups.Add(this.group1);
            this.tab1.Groups.Add(this.group2);
            this.tab1.Groups.Add(this.group5);
            this.tab1.Groups.Add(this.group4);
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
            // group2
            // 
            this.group2.Items.Add(this.toggleButton1);
            this.group2.Label = "Options / Output";
            this.group2.Name = "group2";
            // 
            // toggleButton1
            // 
            this.toggleButton1.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.toggleButton1.Image = global::TagUIExcelAddIn.Properties.Resources.Taskpane;
            this.toggleButton1.Label = "Show Task Pane";
            this.toggleButton1.Name = "toggleButton1";
            this.toggleButton1.ShowImage = true;
            this.toggleButton1.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.toggleButton1_Click);
            // 
            // group5
            // 
            this.group5.Items.Add(this.buttonViewReport);
            this.group5.Label = "Report";
            this.group5.Name = "group5";
            // 
            // buttonViewReport
            // 
            this.buttonViewReport.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonViewReport.Image = global::TagUIExcelAddIn.Properties.Resources.Report;
            this.buttonViewReport.Label = "Pull Latest";
            this.buttonViewReport.Name = "buttonViewReport";
            this.buttonViewReport.ShowImage = true;
            this.buttonViewReport.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonViewReport_Click);
            // 
            // group4
            // 
            this.group4.Items.Add(this.buttonUpdate);
            this.group4.Items.Add(this.buttonUsageGuide);
            this.group4.Label = "Help";
            this.group4.Name = "group4";
            // 
            // buttonUpdate
            // 
            this.buttonUpdate.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonUpdate.Image = global::TagUIExcelAddIn.Properties.Resources.Update;
            this.buttonUpdate.Label = "Update TagUI";
            this.buttonUpdate.Name = "buttonUpdate";
            this.buttonUpdate.ShowImage = true;
            this.buttonUpdate.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonUpdate_Click);
            // 
            // buttonUsageGuide
            // 
            this.buttonUsageGuide.ControlSize = Microsoft.Office.Core.RibbonControlSize.RibbonControlSizeLarge;
            this.buttonUsageGuide.Image = global::TagUIExcelAddIn.Properties.Resources.UsageGuide;
            this.buttonUsageGuide.Label = "Usage Guide";
            this.buttonUsageGuide.Name = "buttonUsageGuide";
            this.buttonUsageGuide.ShowImage = true;
            this.buttonUsageGuide.Click += new Microsoft.Office.Tools.Ribbon.RibbonControlEventHandler(this.buttonUsageGuide_Click);
            // 
            // Ribbon1
            // 
            this.Name = "Ribbon1";
            this.RibbonType = "Microsoft.Excel.Workbook";
            this.Tabs.Add(this.tab1);
            this.Load += new Microsoft.Office.Tools.Ribbon.RibbonUIEventHandler(this.Ribbon1_Load);
            this.tab1.ResumeLayout(false);
            this.tab1.PerformLayout();
            this.group1.ResumeLayout(false);
            this.group1.PerformLayout();
            this.group2.ResumeLayout(false);
            this.group2.PerformLayout();
            this.group5.ResumeLayout(false);
            this.group5.PerformLayout();
            this.group4.ResumeLayout(false);
            this.group4.PerformLayout();
            this.ResumeLayout(false);

        }

        #endregion

        internal Microsoft.Office.Tools.Ribbon.RibbonTab tab1;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group1;
        internal Microsoft.Office.Tools.Ribbon.RibbonToggleButton toggleButton1;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonRun;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonDeploy;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group2;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonUsageGuide;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group4;
        internal Microsoft.Office.Tools.Ribbon.RibbonGroup group5;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonViewReport;
        internal Microsoft.Office.Tools.Ribbon.RibbonButton buttonUpdate;
    }

    partial class ThisRibbonCollection
    {
        internal Ribbon1 Ribbon1
        {
            get { return this.GetRibbon<Ribbon1>(); }
        }
    }
}
