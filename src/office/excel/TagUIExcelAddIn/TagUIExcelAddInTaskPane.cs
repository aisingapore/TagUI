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
            ////string flowFileType = FlowFileType(flowFilePath);
            //if (flowFileType != "invalid")
            //{
            //    Process.Start(flowFilePath);
            //}
            //else
            //{
            //    MessageBox.Show("Please select a valid flow file for editing", "Oops!");
            //}
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
    }
}