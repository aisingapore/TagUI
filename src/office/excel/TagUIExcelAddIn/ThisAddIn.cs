using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Xml.Linq;
using Excel = Microsoft.Office.Interop.Excel;
using Office = Microsoft.Office.Core;
using Microsoft.Office.Tools.Excel;

namespace TagUIExcelAddIn
{
    public partial class ThisAddIn
    {
        private TagUIExcelAddInTaskPane taskPaneControl1;
        private Microsoft.Office.Tools.CustomTaskPane taskPaneValue;
        private void ThisAddIn_Startup(object sender, System.EventArgs e)
        {
            taskPaneControl1 = new TagUIExcelAddInTaskPane();
            taskPaneValue = this.CustomTaskPanes.Add(taskPaneControl1, " ");
            taskPaneValue.VisibleChanged += new EventHandler(taskPaneValue_VisibleChanged);
        }

        private void ThisAddIn_Shutdown(object sender, System.EventArgs e)
        {
        }
        private void taskPaneValue_VisibleChanged(object sender, System.EventArgs e)
        {
               Globals.Ribbons.Ribbon1.toggleButton1.Checked =
                 taskPaneValue.Visible;
        }
        public Microsoft.Office.Tools.CustomTaskPane TaskPane
        {
            get
            {
                return taskPaneValue;
            }
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
    }
}
