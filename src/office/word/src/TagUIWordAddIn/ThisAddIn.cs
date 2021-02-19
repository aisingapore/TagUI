using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Xml.Linq;
using Word = Microsoft.Office.Interop.Word;
using Office = Microsoft.Office.Core;
using Microsoft.Office.Tools.Word;

namespace TagUIWordAddIn
{
    public partial class ThisAddIn
    {
        private TagUITaskPane tagUITaskPane1;
        private Microsoft.Office.Tools.CustomTaskPane myCustomTaskPane;
        private void ThisAddIn_Startup(object sender, System.EventArgs e)
        {
            tagUITaskPane1 = new TagUITaskPane();
            myCustomTaskPane = this.CustomTaskPanes.Add(tagUITaskPane1, "TagUI RPA");
            myCustomTaskPane.Visible = true;
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
    }
}
