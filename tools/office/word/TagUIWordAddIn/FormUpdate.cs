using Microsoft.Office.Interop.Word;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using Word = Microsoft.Office.Interop.Word;

namespace TagUIWordAddIn
{
    public partial class FormUpdate : Form
    {
        public FormUpdate()
        {
            InitializeComponent();
        }

        private void FormUpdate_Load(object sender, EventArgs e)
        {
            Globals.Ribbons.Ribbon1.buttonUpdate.Enabled = false;
        }
        private void buttonUpdate_Click(object sender, EventArgs e)
        {
            Word.Application xlApp = Globals.ThisAddIn.Application;
            xlApp.System.Cursor = WdCursorType.wdCursorWait;
            buttonUpdate.Visible = false;
            labelUpdateTerms.Text = "Updating in progress...";

            string appDataFolder = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
            string taguiSrcPathTempString = appDataFolder + @"\TagUI\textBoxTaguiSrcPath.txt";
            string setTaguiPath = "";
            if (File.Exists(taguiSrcPathTempString))
            {
                string taguiSrcPath = File.ReadAllText(taguiSrcPathTempString);
                setTaguiPath = "set path=" + taguiSrcPath + ";%path% & ";
            }
            string cmdCommand = @"/C " + setTaguiPath + "end_processes & tagui update";
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
                        RedirectStandardError = true,
                        CreateNoWindow = true
                    }
                };
                process.SynchronizingObject = labelUpdateTerms;
                process.EnableRaisingEvents = true;
                process.Start();
                process.BeginOutputReadLine();
                process.BeginErrorReadLine();
                process.OutputDataReceived += new DataReceivedEventHandler((_sender, _e) =>
                {
                    if (!String.IsNullOrEmpty(_e.Data))
                    {
                        labelUpdateTerms.Text = _e.Data;
                    }
                });
                string errorMessage = "";
                process.ErrorDataReceived += new DataReceivedEventHandler((_sender, _e) =>
                {
                    
                    if (!String.IsNullOrEmpty(_e.Data))
                    {
                        errorMessage += _e.Data + "\n";
                    }
                });
                process.Exited += (s, evt) =>
                {
                    process?.Dispose();
                    if (errorMessage != "")
                    {
                        string displayErrorMessage = "";
                        string[] errorArr = errorMessage.Split('\n');
                        string firstTwoErrorMessages = errorArr[0] + errorArr[1];
                        if (firstTwoErrorMessages == "'tagui' is not recognized as an internal or external command,operable program or batch file.") displayErrorMessage = "Incomplete TagUI installation. Go to Settings in ribbon to set your TagUI SRC Path.";
                        else if (errorArr.Length > 2) displayErrorMessage = errorMessage;
                        labelUpdateTerms.Text = displayErrorMessage;
                    }
                    xlApp.System.Cursor = WdCursorType.wdCursorNormal;
                    buttonOk.Visible = true;
                };
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }

        private void buttonOk_Click(object sender, EventArgs e)
        {
            this.Close();
        }

        private void FormUpdate_FormClosed(object sender, FormClosedEventArgs e)
        {
            Globals.Ribbons.Ribbon1.buttonUpdate.Enabled = true;
        }

    }
}
