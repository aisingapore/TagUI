using System;
using System.Collections.Generic;
using System.Windows.Forms;
using System.Diagnostics;
using Excel = Microsoft.Office.Interop.Excel;
using CheckBox = System.Windows.Forms.CheckBox;
using System.IO;

namespace TagUIExcelAddIn
{
    public partial class TagUIExcelAddInTaskPane : UserControl
    {
        public TagUIExcelAddInTaskPane()
        {
            InitializeComponent();
            ToolTip ToolTip1 = new System.Windows.Forms.ToolTip();
            ToolTip1.AutoPopDelay = 30000;
            ToolTip1.SetToolTip(pictureBox1, "Datatables run a workflow multiple times with different inputs.\n\nTo use it, select a sheet and specify an optional cell range (e.g. A1:B4) where the data resides.\n\nTagUI will run the current workflow once for each row in the datatable (except the header).\nWithin the flow, TagUI can use the datatable header as variables and the values will be from that run’s row.");
            ToolTip ToolTip2 = new System.Windows.Forms.ToolTip();
            ToolTip1.SetToolTip(pictureBox2, "Object repositories store variables for use in flows.\nThey help to separate your flows from your personal data (like login information for web flows), and allow you to share common information between multiple flows for easy updating.");
            ToolTip1.AutoPopDelay = 30000;
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
            Process.Start(flowFilePath);
        }
        private void textBoxFlowFile_TextChanged(object sender, EventArgs e)
        {
            if (textBoxFlowFile.Text != "")
            {
                buttonEditFlow.Enabled = true;
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
                textBoxRange.Enabled = true;
            }
            else
            {
                comboBoxAllSheets.Enabled = false;
                textBoxRange.Enabled = false;
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

        private void textBoxRange_Click(object sender, EventArgs e)
        {
            textBoxRange.Text = "";
            Excel.Worksheet ws = Globals.ThisAddIn.Application.ActiveSheet;
            ws.SelectionChange += ws_SelectionChange;
        }
        void ws_SelectionChange(Excel.Range Target)
        {
            textBoxRange.Text = Target.Address.Replace("$", "");
            Excel.Worksheet ws = Globals.ThisAddIn.Application.ActiveSheet;
            ws.SelectionChange -= ws_SelectionChange;
            this.Focus();
        }
        private void TagUIExcelAddInTaskPane_SizeChanged(object sender, EventArgs e)
        {
            textBoxFlowFile.Width = this.Size.Width - 19;
            comboBoxAllSheets.Width = this.Size.Width - 44;
            textBoxRange.Width = this.Size.Width - 44;
            comboBoxObjectRepository.Width = this.Size.Width - 44;
            textBoxParam.Width = this.Size.Width - 44;
            textBoxOutput.Width = this.Size.Width - 19;
            textBoxOutput.Height = this.Size.Height - 500;
        }

        private void comboBoxAllSheets_SelectedIndexChanged(object sender, EventArgs e)
        {
            textBoxRange.Enabled = true;
        }
    }
}