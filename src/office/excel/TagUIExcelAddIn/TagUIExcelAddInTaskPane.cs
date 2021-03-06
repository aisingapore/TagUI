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
using Excel = Microsoft.Office.Interop.Excel;
using CheckBox = System.Windows.Forms.CheckBox;
using DocumentFormat.OpenXml.Packaging;
using DocumentFormat.OpenXml.Wordprocessing;

namespace TagUIExcelAddIn
{
    public partial class TagUIExcelAddInTaskPane : UserControl
    {
        public TagUIExcelAddInTaskPane()
        {
            InitializeComponent();
        }
        private void buttonSelect_Click(object sender, EventArgs e)
        {
            OpenFileDialog openFileDialog1 = new OpenFileDialog
            {
                InitialDirectory = @"C:\Users\" + Environment.GetEnvironmentVariable("USERPROFILE"),
                Title = "Browse Flow Files",
                CheckFileExists = true,
                CheckPathExists = true,
                DefaultExt = "docx",
                Filter = "Word Document (.docx)|*.docx|TagUI Flow File (.tag)|*.tag",
                FilterIndex = 1,
                RestoreDirectory = true,
                ReadOnlyChecked = false,
                ShowReadOnly = true
            };
            if (openFileDialog1.ShowDialog() == DialogResult.OK)
            {
                textBoxFlowFile.Text = openFileDialog1.FileName;
            }
        }
        private void buttonEditFlow_Click(object sender, EventArgs e)
        {
            string flowFilePath = textBoxFlowFile.Text;
            string flowFileType = FlowFileType(flowFilePath);
            if (flowFileType != "invalid")
            {
                Process.Start(flowFilePath);
            }
            else
            {
                MessageBox.Show("Please select a valid flow file for editing", "Oops!");
            }
        }
        private void checkBoxObjectRepository_CheckedChanged(object sender, EventArgs e)
        {
            if (((CheckBox)sender).Checked)
            {
                comboBoxObjectRepository.Enabled = true;
            }
            else
            {
                comboBoxObjectRepository.Enabled = false;
            }

        }
        private void checkBoxDatatable_CheckedChanged(object sender, EventArgs e)
        {
            if (((CheckBox)sender).Checked)
            {
                comboBoxAllSheets.Enabled = true;
            }
            else
            {
                comboBoxAllSheets.Enabled = false;
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
        private void comboBoxAllSheets_Click(object sender, EventArgs e)
        {
            Init(comboBoxAllSheets);
        }
        private void comboBoxObjectRepository_Click(object sender, EventArgs e)
        {

            Init(comboBoxObjectRepository);
        }
        private void Init(ComboBox comboBox)
        {
            List<Item> items = new List<Item>();
            int index = 0;
            foreach (Excel.Worksheet displayWorksheet in Globals.ThisAddIn.Application.Worksheets)
            {
                items.Add(new Item() { Text = displayWorksheet.Name, Value = displayWorksheet.Name });
                index++;
            }
            comboBox.DataSource = items;
            comboBox.DisplayMember = "Text";
            comboBox.ValueMember = "Value";
        }
        public class Item
        {
            public Item() { }
            public string Value { set; get; }
            public string Text { set; get; }
        }
        private void buttonRun_Click(object sender, EventArgs e)
        {
            RunFlow(false);
        }
        private void buttonDeploy_Click(object sender, EventArgs e)
        {
            string fileLoc = Globals.ThisAddIn.Application.ActiveWorkbook.FullName;
            if (fileLoc.Contains(@"\"))
            {
                string tagFilePath = textBoxFlowFile.Text;
                RunFlow(true);
            }
            else
            {
                MessageBox.Show("Workbook must be saved before deploying", "Oops!");
                try
                {
                    Globals.ThisAddIn.Application.ActiveWorkbook.Save();
                }
                catch { }
            }
        }
        private void RunFlow(bool deploy)
        {
            string flowFilePath = textBoxFlowFile.Text;
            string fileType = FlowFileType(flowFilePath);
            if (fileType != "invalid")
            {
                string tagFilePath = flowFilePath;
                if (fileType == ".docx")
                {
                    if (!ValidateFlowFileIsClosed(flowFilePath))
                    {
                        MessageBox.Show("Flow file must be saved and closed before running/deploying", "Oops!");
                        return;
                    }
                    tagFilePath = CreateTagFile(flowFilePath);
                }
                if (checkBoxObjectRepository.Checked)
                {

                    string sheetName = comboBoxObjectRepository.Text;
                    string objRepoCsvFilePath = GetObjRepoCsvFilePath(flowFilePath);
                    if (ValidateSheetStillExists(sheetName))
                    {
                        CreateCsvFile(objRepoCsvFilePath, sheetName);
                    }
                    else
                    {
                        MessageBox.Show("Worksheet " + sheetName + " does not exist", "Oops!");
                        return;
                    }
                }
                string datatableCsvFilePath = "";
                if (checkBoxDatatable.Checked)
                {

                    string sheetName = comboBoxAllSheets.Text;
                    if (ValidateSheetStillExists(sheetName))
                    {
                        datatableCsvFilePath = GetCsvFilePath();
                        CreateCsvFile(datatableCsvFilePath, sheetName);
                    }
                    else
                    {
                        MessageBox.Show("Worksheet " + sheetName + " does not exist", "Oops!");
                        return;
                    }
                }
                string runOptions = AddRunOption(deploy, datatableCsvFilePath);
                RunCommand(tagFilePath, runOptions);
            }
            else
            {
                MessageBox.Show("Please select a valid flow file to run/deploy", "Oops!");
            }
        }
        private string GetObjRepoCsvFilePath(string flowFilePath)
        {
            string[] fileLocArr = flowFilePath.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = flowFilePath.Substring(0, flowFilePath.Length - lengthToCut);
            string objRepoCsvFilePath = folderPath + "tagui_local.csv";
            return objRepoCsvFilePath;
        }
        private string FlowFileType(string flowFilePath)
        {
            string flowFileType = "";
            if (!File.Exists(flowFilePath))
            {
                flowFileType = "invalid";
            }
            else
            {
                try
                {
                    if (flowFilePath.Substring(flowFilePath.Length - 4, 4) == ".tag")
                    {
                        flowFileType = ".tag";
                    }
                    else if (flowFilePath.Substring(flowFilePath.Length - 5, 5) == ".docx")
                    {
                        flowFileType = ".docx";
                    }
                    else
                    {
                        flowFileType = "invalid";
                    }
                }
                catch
                {
                    flowFileType = "invalid";
                }
            }
            return flowFileType;
        }
        private string CreateTagFile(string flowFilePath)
        {
            string[] fileLocArr = flowFilePath.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = flowFilePath.Substring(0, flowFilePath.Length - lengthToCut);
            string[] fileLocArr2 = fileLocArr[fileLocArr.Length - 1].Split('.');
            string fileName = fileLocArr2[0];
            string tagFilePath = folderPath + fileName.Replace(" ", "_") + ".tag";
            WordprocessingDocument wordprocessingDocument =
                    WordprocessingDocument.Open(flowFilePath, true);
            string textFromDoc = "";
            var paragraphs = wordprocessingDocument.MainDocumentPart.RootElement.Descendants<Paragraph>();
            foreach (var paragraph in paragraphs)
            {
                textFromDoc += paragraph.InnerText + "\r\n";
            }
            wordprocessingDocument.Close();
            File.WriteAllText(tagFilePath, textFromDoc);
            return tagFilePath;
        }
        private string AddRunOption(bool deploy, string datatableCsvFilePath)
        {
            string runOptions = "";
            if (checkBoxNoBrowser.Checked) runOptions += " -n";
            if (checkBoxReport.Checked) runOptions += " -r";
            if (checkBoxQuiet.Checked) runOptions += " -q";
            if (checkBoxDatatable.Checked) runOptions += " " + datatableCsvFilePath;
            if (checkBoxInputs.Checked) runOptions += " " + textBoxParam.Text;
            if (deploy) runOptions += " -d";
            return runOptions;
        }
        private string GetCsvFilePath()
        {
            string fileLoc = Globals.ThisAddIn.Application.ActiveWorkbook.FullName;
            string csvFilePath;
            if (fileLoc.Contains(@"\"))
            {
                csvFilePath = GetCsvFilePathSavedDoc(fileLoc);
            }
            else
            {
                string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
                Directory.CreateDirectory(appDataFolder); //create new TagUI folder in appData folder it it does not exist
                csvFilePath = appDataFolder + "DataTable.csv";
            }
            return csvFilePath;
        }

        private void CreateCsvFile(string csvFilePath, string sheetName)
        {
            Excel.Worksheet EW = Globals.ThisAddIn.Application.ActiveWorkbook.Worksheets[sheetName] as Excel.Worksheet;
            Excel.Workbook newWorkbook = Globals.ThisAddIn.Application.Workbooks.Add();
            EW.Copy(newWorkbook.Sheets[1]);
            string newWbSheetName = sheetName;
            if (sheetName == "Sheet1") newWbSheetName = sheetName + " (2)";
            Globals.ThisAddIn.Application.DisplayAlerts = false; //to overwrite existing file without promopting user
            newWorkbook.Worksheets[newWbSheetName].SaveAs(csvFilePath, Excel.XlFileFormat.xlCSV, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing);
            newWorkbook.Close(true);
        }
        private string GetCsvFilePathSavedDoc(string fileLoc)
        {
            string[] fileLocArr = fileLoc.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = fileLoc.Substring(0, fileLoc.Length - lengthToCut);
            string[] fileLocArr2 = fileLocArr[fileLocArr.Length - 1].Split('.');
            string fileName = fileLocArr2[0];
            string csvFilePath = folderPath + fileName.Replace(" ", "_") + "_" + comboBoxAllSheets.Text.Replace(" ", "_") + ".csv";
            return csvFilePath;
        }
        private void RunCommand(string tagFilePath, string runOptions)
        {
            string cmdCommand = "/C tagui " + tagFilePath + runOptions;
            Process.Start("cmd.exe", cmdCommand);
        }
        ///// validations /////
        private bool ValidateFlowFileIsClosed(string flowFilePath)
        {
            bool isClosed;
            try
            {
                WordprocessingDocument wordprocessingDocument =
                    WordprocessingDocument.Open(flowFilePath, false);
                wordprocessingDocument.Close();
                isClosed = true;
            }
            catch { isClosed = false; }
            return isClosed;
        }
        private bool ValidateSheetStillExists(string sheetName)
        {
            bool sheetExists;
            try
            {
                Excel.Worksheet EW = Globals.ThisAddIn.Application.ActiveWorkbook.Worksheets[sheetName] as Excel.Worksheet;
                sheetExists = true;
            }
            catch
            {
                sheetExists = false;
            }
            return sheetExists;
        }
    }
}