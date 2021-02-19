using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Diagnostics;
using System.IO;

namespace TagUIWordAddIn
{
    public partial class TagUITaskPane : UserControl
    {
        public TagUITaskPane()
        {
            InitializeComponent();
        }
        //checkbox form validations
        private void checkBoxDatatableCSV_CheckedChanged(object sender, EventArgs e)
        {
            if (((CheckBox)sender).Checked)
            {
                textBoxDatatableCSV.Enabled = true;
            }
            else
            {
                textBoxDatatableCSV.Enabled = false;
            }
        }
        private void checkBoxInputs_CheckedChanged(object sender, EventArgs e)
        {
            if (((CheckBox)sender).Checked)
            {
                textBoxParam.Enabled = true;
            }
            else
            {
                textBoxParam.Enabled = false;
            }
        }
        private void labelDocumentation_Click(object sender, EventArgs e)
        {
            Process.Start("IExplore.exe", "https://tagui.readthedocs.io/en/latest/index.html");
        }
        private void buttonRun_Click(object sender, EventArgs e)
        {
            string fileLoc = Globals.ThisAddIn.Application.ActiveDocument.FullName;
            if (fileLoc.Contains(@"\"))
            {
                string tagFilePath = getTagFilePathSavedDoc(fileLoc);
                RunFlow(tagFilePath, false);
            }
            else
            {
                string appDataFolder = System.Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
                System.IO.Directory.CreateDirectory(appDataFolder); //create new TagUI folder in appData folder it it does not exist
                string tagFilePath = appDataFolder + "WorkFlow.tag";
                RunFlow(tagFilePath, false);
            }
        }
        private void buttonDeploy_Click(object sender, EventArgs e)
        {
            string fileLoc = Globals.ThisAddIn.Application.ActiveDocument.FullName;
            if (fileLoc.Contains(@"\"))
            {
                string tagFilePath = getTagFilePathSavedDoc(fileLoc);
                RunFlow(tagFilePath, true);
            }
            else
            {
                MessageBox.Show("Document must be saved before deploying", "Oops!");
                try
                {
                    Globals.ThisAddIn.Application.ActiveDocument.Save();
                }
                catch { }
            }
        }
        private string getTagFilePathSavedDoc(string fileLoc)
        {
            string[] fileLocArr = fileLoc.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = fileLoc.Substring(0, fileLoc.Length - lengthToCut);
            string[] fileLocArr2 = fileLocArr[fileLocArr.Length - 1].Split('.');
            string fileName = fileLocArr2[0];
            string tagFilePath = folderPath + fileName + ".tag";
            return tagFilePath;
        }
        private void RunFlow(string tagFilePath, bool deploy)
        {
            CreateTagFile(tagFilePath);
            string runOptions = AddRunOption(deploy);
            RunCommand(tagFilePath, runOptions);
        }

        private void CreateTagFile(string tagFilePath)
        {
            Globals.ThisAddIn.Application.ActiveDocument.Range(
        Globals.ThisAddIn.Application.ActiveDocument.Content.Start,
        Globals.ThisAddIn.Application.ActiveDocument.Content.End).Select(); //select and get all text from document
            string textFromDoc = (Globals.ThisAddIn.Application.Selection.Text).Replace("\r\n", "\n").Replace("\r", "\n").Replace("\n", "\r\n");  //change generated .tag file line ending to CRLF format instead of default macintosh
            File.WriteAllText(tagFilePath, textFromDoc);
            return;
        }
        private string AddRunOption(bool deploy)
        {
            string runOptions = "";
            if (checkBoxNoBrowser.Checked == true) runOptions += " -n";
            if (checkBoxReport.Checked == true) runOptions += " -r";
            if (checkBoxQuiet.Checked == true) runOptions += " -q";
            if (checkBoxDatatableCSV.Checked == true) runOptions += " " + textBoxDatatableCSV.Text;
            if (checkBoxInputs.Checked == true) runOptions += " " + textBoxParam.Text;
            if (deploy) runOptions += " -d";
            return runOptions;
        }
        private void RunCommand(string tagFilePath, string runOptions)
        {
            string cmdCommand = "/C tagui " + tagFilePath + runOptions;
            Process.Start("cmd.exe", cmdCommand);
        }
    }
}