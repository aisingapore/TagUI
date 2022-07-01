using Microsoft.Office.Interop.Word;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Diagnostics;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using Word = Microsoft.Office.Interop.Word;
using Excel = Microsoft.Office.Interop.Excel;
using Microsoft.Office.Interop.Excel;

namespace TagUIExcelAddIn
{
    public partial class FormUpdate : Form
    {
        public FormUpdate()
        {
            InitializeComponent();
        }
        private void buttonOk_Click(object sender, EventArgs e)
        {
            this.Close();
        }

        private void buttonUpdate_Click(object sender, EventArgs e)
        {

            Excel.Application xlApp = Globals.ThisAddIn.Application;
            xlApp.Cursor = XlMousePointer.xlWait;
            buttonUpdate.Visible = false;
            labelUpdateTerms.Text = "Updating in progress...";
            string cmdCommand = "/C tagui update";
            try
            {
                var process = new Process
                {
                    StartInfo = new ProcessStartInfo
                    {
                        FileName = "cmd.exe",
                        Arguments = cmdCommand,
                        UseShellExecute = false,
                        RedirectStandardOutput = true,
                        CreateNoWindow = true
                    }
                };
                process.SynchronizingObject = labelUpdateTerms;
                process.EnableRaisingEvents = true;
                process.Start();
                process.BeginOutputReadLine();
                process.OutputDataReceived += new DataReceivedEventHandler((_sender, _e) =>
                {
                    if (!String.IsNullOrEmpty(_e.Data))
                    {
                        labelUpdateTerms.Text = _e.Data;
                    }
                });
                process.Exited += (s, evt) =>
                {
                    process?.Dispose();
                    xlApp.Cursor = XlMousePointer.xlDefault;
                    buttonOk.Visible = true;
                };
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }
    }
}
