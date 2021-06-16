using Microsoft.Office.Core;
using Microsoft.Office.Tools.Word;

using Microsoft.Office.Tools;
using System;
using System.Windows.Forms;
using Office = Microsoft.Office.Core;

using Word = Microsoft.Office.Interop.Word;
using CustomTaskPane = Microsoft.Office.Tools.CustomTaskPane;
using System.Linq;

namespace TagUIWordAddIn
{
    public partial class ThisAddIn
    {
        private TagUITaskPane taskPaneControl1;
        private Microsoft.Office.Tools.CustomTaskPane taskPaneValue;
        private void ThisAddIn_Startup(object sender, System.EventArgs e)
        {
            Application.DocumentBeforeSave += new Word.ApplicationEvents4_DocumentBeforeSaveEventHandler(Application_DocumentBeforeSave);
            Application.DocumentOpen += new Word.ApplicationEvents4_DocumentOpenEventHandler(Application_DocumentOpen);
            ((Word.ApplicationEvents4_Event)Application).NewDocument += new Word.ApplicationEvents4_NewDocumentEventHandler(Application_NewDocument);
            Application.WindowDeactivate += new Word.ApplicationEvents4_WindowDeactivateEventHandler(Application_WindowDeactivate);
            Application.WindowActivate += new Word.ApplicationEvents4_WindowActivateEventHandler(Application_WindowActivate);
            Application.DocumentBeforeClose += new Word.ApplicationEvents4_DocumentBeforeCloseEventHandler(Application_DocumentBeforeClose);
            Application.DocumentChange += new Word.ApplicationEvents4_DocumentChangeEventHandler(Application_DocumentChange);

            RemoveOrphanedTaskPanes();
            try
            {
                AddTaskPane(Globals.ThisAddIn.Application.ActiveDocument);
                GetLastRunOptions();
            } catch { }
        }
        void Application_DocumentChange()
        {
            RemoveOrphanedTaskPanes();
        }
        void Application_DocumentBeforeClose(Word.Document Doc, ref bool Cancel)
        {
             RemoveOrphanedTaskPanes();
        }
        void Application_DocumentBeforeSave(Word.Document Doc, ref bool SaveAsUI, ref bool Cancel)
        {
            DirtyTheDoc();
            SetDocProperties();
            SetDocProperties();
        }
        void DirtyTheDoc()
        {
            Word.Range rng = Globals.ThisAddIn.Application.ActiveDocument.Content;
            object collapseStart = Word.WdCollapseDirection.wdCollapseStart;
            rng.Collapse(ref collapseStart);
            rng.Text = " ";
            rng.Delete();
        }
        void Application_WindowActivate(Word.Document Doc, Word.Window Wn)
        {
            RemoveOrphanedTaskPanes();
            try
            {
                DocumentProperties prps;
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
                CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
                if (ctp != null && Wn.Active == true)
                {
                    ctp.Visible = true;
                }
            } catch { }
        }
        void Application_WindowDeactivate(Word.Document Doc, Word.Window Wn)
        {
            RemoveOrphanedTaskPanes();
        }
        void Application_NewDocument(Word.Document Doc)
        {
            RemoveOrphanedTaskPanes();
            AddTaskPane(Doc);
        }
        void Application_DocumentOpen(Word.Document Doc)
        {
            RemoveOrphanedTaskPanes();
            AddTaskPane(Doc);
            GetLastRunOptions();
        }
        void AddTaskPane(Word.Document Doc)
        {
            taskPaneControl1 = new TagUITaskPane();
            taskPaneControl1.Name = Doc.Name;
            taskPaneValue = this.CustomTaskPanes.Add(taskPaneControl1, " ", Doc.ActiveWindow);

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            if (ReadDocumentProperty("customTaskPaneName") != null)
            {
                prps["customTaskPaneName"].Delete();
            }
            prps.Add("customTaskPaneName", false, MsoDocProperties.msoPropertyTypeString, taskPaneControl1.Name);
           
            taskPaneValue.VisibleChanged += new EventHandler(taskPaneValue_VisibleChanged);
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
        private void taskPaneValue_VisibleChanged(object sender, System.EventArgs e)
        {
            Globals.Ribbons.Ribbon1.toggleButton1.Checked =
                  taskPaneValue.Visible;
        }
        public CustomTaskPane TaskPane
        {
            get
            {
                return taskPaneValue;
            }
        }


        public void SetDocProperties()
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            

            string[] customDocArr = {
                "checkBoxNoBrowser",
                "checkBoxReport",
                "checkBoxQuiet",
                "checkBoxDatatableCSV",
                "textBoxDatatableCSV",
                "comboBoxDatatableWs",
                "textBoxRange",
                "checkBoxObjRepo",
                "textBoxObjRepo",
                "comboBoxObjRepo",
                "checkBoxInputs",
                "textBoxParam" };
            foreach (string property in customDocArr)
            {
                if (ReadDocumentProperty(property) != null)
                {
                    prps[property].Delete();
                }
            }
            CheckBox checkBoxNoBrowser = taskPaneControl1.Controls["checkBoxNoBrowser"] as CheckBox;
            if (checkBoxNoBrowser.Checked)
            {
                prps.Add("checkBoxNoBrowser", false, MsoDocProperties.msoPropertyTypeString, "true");
            }
            CheckBox checkBoxReport = taskPaneControl1.Controls["checkBoxReport"] as CheckBox;
            if (checkBoxReport.Checked && ReadDocumentProperty("checkBoxReport") == null)
            {
                prps.Add("checkBoxReport", false, MsoDocProperties.msoPropertyTypeString, "true");
            }
            CheckBox checkBoxQuiet = taskPaneControl1.Controls["checkBoxQuiet"] as CheckBox;
            if (checkBoxQuiet.Checked)
            {
                prps.Add("checkBoxQuiet", false, MsoDocProperties.msoPropertyTypeString, "true");
            }
            CheckBox checkBoxDatatableCSV = taskPaneControl1.Controls["checkBoxDatatableCSV"] as CheckBox;
            if (checkBoxDatatableCSV.Checked)
            {
                prps.Add("checkBoxDatatableCSV", false, MsoDocProperties.msoPropertyTypeString, "true");
            }
            TextBox textBoxDatatableCSV = taskPaneControl1.Controls["textBoxDatatableCSV"] as TextBox;
            if (textBoxDatatableCSV.Text != "")
            {
                prps.Add("textBoxDatatableCSV", false, MsoDocProperties.msoPropertyTypeString, textBoxDatatableCSV.Text);
            }
            ComboBox comboBoxDatatableWs = taskPaneControl1.Controls["comboBoxDatatableWs"] as ComboBox;
            if (comboBoxDatatableWs.Text != "")
            {
                prps.Add("comboBoxDatatableWs", false, MsoDocProperties.msoPropertyTypeString, comboBoxDatatableWs.Text);
            }
            TextBox textBoxRange = taskPaneControl1.Controls["textBoxRange"] as TextBox;
            if (textBoxRange.Text != "")
            {
                prps.Add("textBoxRange", false, MsoDocProperties.msoPropertyTypeString, textBoxRange.Text);
            }
            CheckBox checkBoxObjRepo = taskPaneControl1.Controls["checkBoxObjRepo"] as CheckBox;
            if (checkBoxObjRepo.Checked)
            {
                prps.Add("checkBoxObjRepo", false, MsoDocProperties.msoPropertyTypeString, "true");
            }
            TextBox textBoxObjRepo = taskPaneControl1.Controls["textBoxObjRepo"] as TextBox;
            if (textBoxObjRepo.Text != "")
            {
                prps.Add("textBoxObjRepo", false, MsoDocProperties.msoPropertyTypeString, textBoxObjRepo.Text);
            }
            ComboBox comboBoxObjRepo = taskPaneControl1.Controls["comboBoxObjRepo"] as ComboBox;
            if (comboBoxObjRepo.Text != "")
            {
                prps.Add("comboBoxObjRepo", false, MsoDocProperties.msoPropertyTypeString, comboBoxObjRepo.Text);
            }
            CheckBox checkBoxInputs = taskPaneControl1.Controls["checkBoxInputs"] as CheckBox;
            if (checkBoxInputs.Checked)
            {
                prps.Add("checkBoxInputs", false, MsoDocProperties.msoPropertyTypeString, "true");
            }
            TextBox textBoxParam = taskPaneControl1.Controls["textBoxParam"] as TextBox;
            if (textBoxParam.Text != "")
            {
                prps.Add("textBoxParam", false, MsoDocProperties.msoPropertyTypeString, textBoxParam.Text);
            }
        }

        private string ReadDocumentProperty(string propertyName)
        {
            Office.DocumentProperties properties;
            properties = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;

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
            if (GetDocumentProperties("checkBoxNoBrowser") == "true")
            {
                CheckBox checkBoxReport = taskPaneControl1.Controls["checkBoxNoBrowser"] as CheckBox;
                checkBoxReport.Checked = true;
            }

            if (GetDocumentProperties("checkBoxReport") == "true")
            {
                CheckBox checkBoxReport = taskPaneControl1.Controls["checkBoxReport"] as CheckBox;
                checkBoxReport.Checked = true;
            }

            if (GetDocumentProperties("checkBoxQuiet") == "true")
            {
                CheckBox checkBoxQuiet = taskPaneControl1.Controls["checkBoxQuiet"] as CheckBox;
                checkBoxQuiet.Checked = true;
            }

            if (GetDocumentProperties("checkBoxDatatableCSV") == "true")
            {
                CheckBox checkBoxDatatableCSV = taskPaneControl1.Controls["checkBoxDatatableCSV"] as CheckBox;
                checkBoxDatatableCSV.Checked = true;
                TextBox textBoxDatatableCSV = taskPaneControl1.Controls["textBoxDatatableCSV"] as TextBox;
                textBoxDatatableCSV.Text = GetDocumentProperties("textBoxDatatableCSV");
                ComboBox comboBoxDatatableWs = taskPaneControl1.Controls["comboBoxDatatableWs"] as ComboBox;
                comboBoxDatatableWs.Text = GetDocumentProperties("comboBoxDatatableWs");
            }
            if (GetDocumentProperties("checkBoxObjRepo") == "true")
            {
                CheckBox checkBoxObjRepo = taskPaneControl1.Controls["checkBoxObjRepo"] as CheckBox;
                checkBoxObjRepo.Checked = true;
                TextBox textBoxObjRepo = taskPaneControl1.Controls["textBoxObjRepo"] as TextBox;
                textBoxObjRepo.Text = GetDocumentProperties("textBoxObjRepo");
                ComboBox comboBoxObjRepo = taskPaneControl1.Controls["comboBoxObjRepo"] as ComboBox;
                comboBoxObjRepo.Text = GetDocumentProperties("comboBoxObjRepo");
                TextBox textBoxRange = taskPaneControl1.Controls["textBoxRange"] as TextBox;
                textBoxRange.Text = GetDocumentProperties("textBoxRange");
            }

            if (GetDocumentProperties("checkBoxInputs") == "true")
            {
                CheckBox checkBoxInputs = taskPaneControl1.Controls["checkBoxInputs"] as CheckBox;
                checkBoxInputs.Checked = true;
                TextBox textBoxParam = taskPaneControl1.Controls["textBoxParam"] as TextBox;
                textBoxParam.Text = GetDocumentProperties("textBoxParam");
            }
        }
        string GetDocumentProperties(string propertyName)
        {
            string propertyValue = "";
            DocumentProperties prps;
            try
            {
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;

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
    }
}
