using System;
using System.Collections.Generic;
using System.Drawing;
using System.Linq;
using System.Windows.Forms;
using System.Diagnostics;
using System.IO;
using DocumentFormat.OpenXml.Packaging;
using DocumentFormat.OpenXml.Wordprocessing;
using DocumentFormat.OpenXml.Spreadsheet;
using Excel = Microsoft.Office.Interop.Excel;
using Word = Microsoft.Office.Interop.Word;
using System.Drawing.Imaging;
using Run = DocumentFormat.OpenXml.Wordprocessing.Run;
using CheckBox = System.Windows.Forms.CheckBox;
using Text = DocumentFormat.OpenXml.Wordprocessing.Text;
using DocumentFormat.OpenXml;
using Microsoft.Office.Interop.Word;
using Microsoft.Office.Core;
using Microsoft.Office.Tools.Word;
namespace TagUIWordAddIn
{
    public partial class TagUITaskPane : UserControl
    {

        public TagUITaskPane()
        {
            InitializeComponent();
            ToolTip ToolTip1 = new System.Windows.Forms.ToolTip();
            ToolTip1.AutoPopDelay = 30000;
            ToolTip1.SetToolTip(pictureBox1, "Datatables run a workflow multiple times with different inputs.\n\nTo use it, browse and select an.xlsx or.csv file.\nIf a .xlsx file is selected, specify the sheet and optional cell range (e.g. A1:B4).\n\nTagUI will run the current workflow once for each row in the datatable (except the header).\nWithin the flow, TagUI can use the datatable header as variables and the values will be from that run’s row.");
            ToolTip ToolTip2 = new System.Windows.Forms.ToolTip();
            ToolTip1.SetToolTip(pictureBox2, "Object repositories store variables for use in flows.\nThey help to separate your flows from your personal data (like login information for web flows), and allow you to share common information between multiple flows for easy updating.\n\nTo use it, browse and select an.xlsx or.csv file.If a.xlsx file is selected, specify the sheet.");
            ToolTip1.AutoPopDelay = 30000;
        }
        //checkbox form validations
        private void checkBoxDatatableCSV_CheckedChanged(object sender, EventArgs e)
        {
            if (((CheckBox)sender).Checked)
            {
                buttonBrowse.Enabled = true;
            }
            else
            {
                buttonBrowse.Enabled = false;
            }
        }

        private void checkBoxObjRepo_CheckedChanged(object sender, EventArgs e)
        {
            if (((CheckBox)sender).Checked)
            {
                buttonObjRepo.Enabled = true;
            }
            else
            {
                buttonObjRepo.Enabled = false;
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
        private void buttonBrowse_Click(object sender, EventArgs e)
        {
            BrowseFile(textBoxDatatableCSV, comboBoxDatatableWs);
        }
        private void buttonObjRepo_Click(object sender, EventArgs e)
        {
            BrowseFile(textBoxObjRepo, comboBoxObjRepo);
        }

        private void BrowseFile(TextBox textBox, ComboBox comboBox)
        {
            OpenFileDialog openFileDialog1 = new OpenFileDialog
            {
                InitialDirectory = @"C:\Users\" + Environment.GetEnvironmentVariable("USERPROFILE"),
                Title = "Browse Files",
                CheckFileExists = true,
                CheckPathExists = true,
                DefaultExt = "docx",
                Filter = "Microsoft Excel Worksheet (.xlsx)|*.xlsx|Comma Separated Values File (.csv)|*.csv",
                FilterIndex = 1,
                RestoreDirectory = true,
                ReadOnlyChecked = false,
                ShowReadOnly = true
            };
            if (openFileDialog1.ShowDialog() == DialogResult.OK)
            {
                string filePath = openFileDialog1.FileName;
                textBox.Text = filePath;
                string fileType = GetExcelFileType(filePath);
                if (fileType == "xlsx")
                {
                    comboBox.Enabled = true;
                }
                else
                {
                    comboBox.Text = "";
                    comboBox.Enabled = false;
                }
            }
        }
        private void comboBoxDatatableWs_Click(object sender, EventArgs e)
        {
            Init(textBoxDatatableCSV, comboBoxDatatableWs);
            textBoxRange.Enabled = true;
        }

        private void comboBoxObjRepo_Click(object sender, EventArgs e)
        {
            Init(textBoxObjRepo, comboBoxObjRepo);
        }

        private void Init(TextBox textBox, ComboBox comboBox)
        {
            List<Item> items = new List<Item>();
            int index = 0;
            string filePath = textBox.Text;
            try
            {
                var results = GetAllWorksheets(filePath);
                foreach (Sheet item in results)
                {
                    items.Add(new Item() { Text = item.Name, Value = item.Name });
                    index++;
                }
                comboBox.DataSource = items;
                comboBox.DisplayMember = "Text";
                comboBox.ValueMember = "Value";
            }
            catch (Exception ex)
            {
                string errorMessage = "";
                if (ex.Message == "File contains corrupted data.") errorMessage = "Unable to read from workbook. Workbook may be password protected, or contains corrupted data.";
                ErrorDialog f1 = new ErrorDialog();
                f1.Show("Error", errorMessage, ex.ToString());
            }
        }

        private string GetExcelFileType(string filePath)
        {
            string fileType = "";
            if (filePath.Substring(filePath.Length - 4, 4) == "xlsx")
            {
                fileType = "xlsx";
            }
            else fileType = "csv";
            return fileType;
        }
        public static Sheets GetAllWorksheets(string fileName)
        {
            Sheets theSheets = null;
            using (SpreadsheetDocument document =
                SpreadsheetDocument.Open(fileName, false))
            {
                WorkbookPart wbPart = document.WorkbookPart;
                theSheets = wbPart.Workbook.Sheets;
            }
            return theSheets;
        }
        public class Item
        {
            public Item() { }
            public string Value { set; get; }
            public string Text { set; get; }
        }


        private void textBoxRange_Enter(object sender, EventArgs e)
        {
            textBoxRange.Text = "";
        }
        private void TagUITaskPane_SizeChanged(object sender, EventArgs e)
        {
            textBoxDatatableCSV.Width = this.Size.Width - 114;
            comboBoxDatatableWs.Width = this.Size.Width - 114;
            textBoxRange.Width = this.Size.Width - 114;
            buttonBrowse.Location = new System.Drawing.Point(this.Size.Width - 75, textBoxDatatableCSV.Location.Y);
            textBoxObjRepo.Width = this.Size.Width - 114;
            comboBoxObjRepo.Width = this.Size.Width - 114;
            buttonObjRepo.Location = new System.Drawing.Point(this.Size.Width - 75, textBoxObjRepo.Location.Y);
            textBoxParam.Width = this.Size.Width - 114;
            textBoxOutput.Width = this.Size.Width - 14;
            textBoxOutput.Height = this.Size.Height - 500;
        }

    }
}