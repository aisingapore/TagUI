using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Xml.Linq;
using Excel = Microsoft.Office.Interop.Excel;
using Office = Microsoft.Office.Core;
using Microsoft.Office.Tools.Excel;
using Microsoft.Office.Core;
using Microsoft.Office.Tools;
using System.Windows.Forms;
using CustomTaskPane = Microsoft.Office.Tools.CustomTaskPane;


namespace TagUIExcelAddIn
{
    public partial class ThisAddIn
    {
        private TagUIExcelAddInTaskPane myUserControl1;
        private Microsoft.Office.Tools.CustomTaskPane taskPaneValue;

        private void ThisAddIn_Startup(object sender, System.EventArgs e)
        {
        //    myUserControl1 = new TagUIExcelAddInTaskPane();
        //    taskPaneValue = this.CustomTaskPanes.Add(myUserControl1, "");
        //    taskPaneValue.VisibleChanged +=
        //        new EventHandler(taskPaneValue_VisibleChanged);


            Application.WorkbookBeforeSave += new Excel.AppEvents_WorkbookBeforeSaveEventHandler(Application_WorkbookBeforeSave);
            Application.WorkbookOpen += new Excel.AppEvents_WorkbookOpenEventHandler(Application_WorkbookOpen);
            ((Excel.AppEvents_Event)Application).NewWorkbook += new Excel.AppEvents_NewWorkbookEventHandler(Application_NewWorkbook);
            Application.WindowDeactivate += new Excel.AppEvents_WindowDeactivateEventHandler(Application_WindowDeactivate);
            Application.WindowActivate += new Excel.AppEvents_WindowActivateEventHandler(Application_WindowActivate);
            Application.WorkbookBeforeClose += new Excel.AppEvents_WorkbookBeforeCloseEventHandler(Application_DocumentBeforeClose);

            RemoveOrphanedTaskPanes();
        }
        void Application_WorkbookBeforeSave(Excel.Workbook wb, bool SaveAsUI, ref bool Cancel)
        {
            SetDocProperties();
        }
        void Application_WorkbookOpen(Excel.Workbook wb)
        {
            RemoveOrphanedTaskPanes();
            AddTaskPane(wb);
            GetLastRunOptions();
        }
        void Application_NewWorkbook(Excel.Workbook wb)
        {
            RemoveOrphanedTaskPanes();
            AddTaskPane(wb);
        }
        void Application_WindowActivate(Excel.Workbook wb, Excel.Window Wn)
        {
            RemoveOrphanedTaskPanes();
            try
            {
                DocumentProperties prps;
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
                CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
                if (ctp != null && Globals.Ribbons.Ribbon1.toggleButton1.Checked)
                {
                    ctp.Visible = true;
                }
            }
            catch { }
        }
        void Application_WindowDeactivate(Excel.Workbook wb, Excel.Window Wn)
        {
            RemoveOrphanedTaskPanes();
        }
        void Application_NewDocument(Excel.Workbook wb)
        {
            RemoveOrphanedTaskPanes();
            AddTaskPane(wb);
        }

        private void RemoveOrphanedTaskPanes()
        {
            for (int i = Globals.ThisAddIn.CustomTaskPanes.Count; i > 0; i--)
            {
                CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes[i - 1];
                if (ctp.Window == null)
                {
                    this.CustomTaskPanes.Remove(ctp);
                }
                try
                {
                    ctp.Visible = false;
                }
                catch { }
            }
        }

        void AddTaskPane(Excel.Workbook wb)
        {
            myUserControl1 = new TagUIExcelAddInTaskPane();
            myUserControl1.Name = wb.Name;
            taskPaneValue = this.CustomTaskPanes.Add(myUserControl1, " ", wb.Application.ActiveWindow);

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            if (ReadDocumentProperty("customTaskPaneName") != null)
            {
                prps["customTaskPaneName"].Delete();
            }
            prps.Add("customTaskPaneName", false, MsoDocProperties.msoPropertyTypeString, myUserControl1.Name);
            taskPaneValue.VisibleChanged += new EventHandler(taskPaneValue_VisibleChanged);
        }


        void Application_DocumentBeforeClose(Excel.Workbook wb, ref bool Cancel)
        {
            RemoveOrphanedTaskPanes();
        }
        private string ReadDocumentProperty(string propertyName)
        {
            Office.DocumentProperties properties;
            properties = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;

            foreach (Office.DocumentProperty prop in properties)
            {
                if (prop.Name == propertyName)
                {
                    return prop.Value.ToString();
                }
            }
            return null;
        }
        void GetLastRunOptions()
        {
            if (GetDocumentProperties("textBoxFlowFile") != null)
            {
                TextBox textBoxFlowFile = myUserControl1.Controls["textBoxFlowFile"] as TextBox;
                textBoxFlowFile.Text = GetDocumentProperties("textBoxFlowFile");
            }
            if (GetDocumentProperties("checkBoxNoBrowser") == "true")
            {
                CheckBox checkBoxReport = myUserControl1.Controls["checkBoxNoBrowser"] as CheckBox;
                checkBoxReport.Checked = true;
            }

            if (GetDocumentProperties("checkBoxReport") == "true")
            {
                CheckBox checkBoxReport = myUserControl1.Controls["checkBoxReport"] as CheckBox;
                checkBoxReport.Checked = true;
            }

            if (GetDocumentProperties("checkBoxQuiet") == "true")
            {
                CheckBox checkBoxQuiet = myUserControl1.Controls["checkBoxQuiet"] as CheckBox;
                checkBoxQuiet.Checked = true;
            }

            if (GetDocumentProperties("checkBoxDatatable") == "true")
            {
                CheckBox checkBoxDatatableCSV = myUserControl1.Controls["checkBoxDatatable"] as CheckBox;
                checkBoxDatatableCSV.Checked = true;
                ComboBox comboBoxDatatableWs = myUserControl1.Controls["comboBoxAllSheets"] as ComboBox;
                comboBoxDatatableWs.Text = GetDocumentProperties("comboBoxAllSheets");
                TextBox textBoxRange = myUserControl1.Controls["textBoxRange"] as TextBox;
                textBoxRange.Text = GetDocumentProperties("textBoxRange");
            }
            if (GetDocumentProperties("checkBoxObjectRepository") == "true")
            {
                CheckBox checkBoxObjRepo = myUserControl1.Controls["checkBoxObjectRepository"] as CheckBox;
                checkBoxObjRepo.Checked = true;
                ComboBox comboBoxObjRepo = myUserControl1.Controls["comboBoxObjectRepository"] as ComboBox;
                comboBoxObjRepo.Text = GetDocumentProperties("comboBoxObjectRepository");
            }

            if (GetDocumentProperties("checkBoxInputs") == "true")
            {
                CheckBox checkBoxInputs = myUserControl1.Controls["checkBoxInputs"] as CheckBox;
                checkBoxInputs.Checked = true;
                TextBox textBoxParam = myUserControl1.Controls["textBoxParam"] as TextBox;
                textBoxParam.Text = GetDocumentProperties("textBoxParam");
            }
            if (GetDocumentProperties("checkBoxCsvOutput") == "true")
            {
                CheckBox checkBoxCsvOutput = myUserControl1.Controls["checkBoxCsvOutput"] as CheckBox;
                checkBoxCsvOutput.Checked = true;
            }
        }
        string GetDocumentProperties(string propertyName)
        {
            string propertyValue = "";
            DocumentProperties prps;
            try
            {
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;

                if (prps[propertyName].Value.ToString() != null)
                {
                    propertyValue = prps[propertyName].Value.ToString();
                }
            }
            catch
            {

            }
            return propertyValue;
        }

        public void SetDocProperties()
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;

            string[] customDocArr = {
                "textBoxFlowFile",
                "checkBoxNoBrowser",
                "checkBoxReport",
                "checkBoxQuiet",
                "checkBoxDatatable",
                "comboBoxAllSheets",
                "textBoxRange",
                "checkBoxObjectRepository",
                "comboBoxObjectRepository",
                "checkBoxInputs",
                "textBoxParam",
                "checkBoxCsvOutput"
            };
            foreach (string property in customDocArr)
            {
                if (ReadDocumentProperty(property) != null)
                {
                    prps[property].Delete();
                }
            }
            TextBox textBoxFlowFile = myUserControl1.Controls["textBoxFlowFile"] as TextBox;
            if(textBoxFlowFile != null)
            {
                if (textBoxFlowFile.Text != "")
                {
                    prps.Add("textBoxFlowFile", false, MsoDocProperties.msoPropertyTypeString, textBoxFlowFile.Text);
                }
                CheckBox checkBoxNoBrowser = myUserControl1.Controls["checkBoxNoBrowser"] as CheckBox;
                if (checkBoxNoBrowser.Checked)
                {
                    prps.Add("checkBoxNoBrowser", false, MsoDocProperties.msoPropertyTypeString, "true");
                }
                CheckBox checkBoxReport = myUserControl1.Controls["checkBoxReport"] as CheckBox;
                if (checkBoxReport.Checked && ReadDocumentProperty("checkBoxReport") == null)
                {
                    prps.Add("checkBoxReport", false, MsoDocProperties.msoPropertyTypeString, "true");
                }
                CheckBox checkBoxQuiet = myUserControl1.Controls["checkBoxQuiet"] as CheckBox;
                if (checkBoxQuiet.Checked)
                {
                    prps.Add("checkBoxQuiet", false, MsoDocProperties.msoPropertyTypeString, "true");
                }
                CheckBox checkBoxDatatableCSV = myUserControl1.Controls["checkBoxDatatable"] as CheckBox;
                if (checkBoxDatatableCSV.Checked)
                {
                    prps.Add("checkBoxDatatable", false, MsoDocProperties.msoPropertyTypeString, "true");
                }
                ComboBox comboBoxDatatableWs = myUserControl1.Controls["comboBoxAllSheets"] as ComboBox;
                if (comboBoxDatatableWs.Text != "")
                {
                    prps.Add("comboBoxAllSheets", false, MsoDocProperties.msoPropertyTypeString, comboBoxDatatableWs.Text);
                }
                TextBox textBoxRange = myUserControl1.Controls["textBoxRange"] as TextBox;
                if (textBoxRange.Text != "")
                {
                    prps.Add("textBoxRange", false, MsoDocProperties.msoPropertyTypeString, textBoxRange.Text);
                }
                CheckBox checkBoxObjRepo = myUserControl1.Controls["checkBoxObjectRepository"] as CheckBox;
                if (checkBoxObjRepo.Checked)
                {
                    prps.Add("checkBoxObjectRepository", false, MsoDocProperties.msoPropertyTypeString, "true");
                }
                ComboBox comboBoxObjRepo = myUserControl1.Controls["comboBoxObjectRepository"] as ComboBox;
                if (comboBoxObjRepo.Text != "")
                {
                    prps.Add("comboBoxObjectRepository", false, MsoDocProperties.msoPropertyTypeString, comboBoxObjRepo.Text);
                }
                CheckBox checkBoxInputs = myUserControl1.Controls["checkBoxInputs"] as CheckBox;
                if (checkBoxInputs.Checked)
                {
                    prps.Add("checkBoxInputs", false, MsoDocProperties.msoPropertyTypeString, "true");
                }
                TextBox textBoxParam = myUserControl1.Controls["textBoxParam"] as TextBox;
                if (textBoxParam.Text != "")
                {
                    prps.Add("textBoxParam", false, MsoDocProperties.msoPropertyTypeString, textBoxParam.Text);
                }
                CheckBox checkBoxCsvOutput = myUserControl1.Controls["checkBoxCsvOutput"] as CheckBox;
                if (checkBoxCsvOutput.Checked)
                {
                    prps.Add("checkBoxCsvOutput", false, MsoDocProperties.msoPropertyTypeString, "true");
                }
            }
            
        }

        private void ThisAddIn_Shutdown(object sender, System.EventArgs e)
        {
        }

        #region VSTO generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InternalStartup()
        {
            this.Startup += new System.EventHandler(ThisAddIn_Startup);
            this.Shutdown += new System.EventHandler(ThisAddIn_Shutdown);
        }

        #endregion
        public CustomTaskPane TaskPane
        {
            get
            {
                return taskPaneValue;
            }
        }
        private void taskPaneValue_VisibleChanged(object sender, System.EventArgs e)
        {
            Globals.Ribbons.Ribbon1.toggleButton1.Checked =
                taskPaneValue.Visible;
        }
    }
}

