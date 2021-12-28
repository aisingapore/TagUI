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
    public partial class FormLive : Form
    {
        Process process;
        StreamWriter myStreamWriter;
        String inputText;
        String finalString = "";
        public FormLive()
        {
            InitializeComponent();

            this.textBoxLiveInput.KeyDown += new System.Windows.Forms.KeyEventHandler(this.OnKeyDownHandler);

            string appDataFolder = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
            string taguiSrcPathTempString = appDataFolder + @"\TagUI\textBoxTaguiSrcPath.txt";
            string setTaguiPath = "";
            if (File.Exists(taguiSrcPathTempString))
            {
                string taguiSrcPath = System.IO.File.ReadAllText(taguiSrcPathTempString);
                setTaguiPath = "set path=" + taguiSrcPath + ";%path% & ";
            }
            string cmdCommand = @"/C " + setTaguiPath + "end_processes & tagui live";
            process = new Process
            {
                StartInfo = new ProcessStartInfo
                {
                    FileName = "cmd.exe",
                    Arguments = cmdCommand,
                    UseShellExecute = false,
                    RedirectStandardOutput = true,
                    RedirectStandardError = true,
                    RedirectStandardInput = true,
                    CreateNoWindow = true
                }
            };
            process.SynchronizingObject = this.textBoxLiveOutput;
            process.EnableRaisingEvents = true;
            process.Start();
            myStreamWriter = process.StandardInput;
            process.BeginOutputReadLine();
            process.OutputDataReceived += new DataReceivedEventHandler((_sender, _e) =>
            {
                if (!String.IsNullOrEmpty(_e.Data))
                {
                    try
                    {
                        this.textBoxLiveOutput.AppendText("» " + _e.Data + Environment.NewLine);
                    } catch
                    {

                    }
                    if (!_e.Data.Contains("START - automation started") && !_e.Data.Contains("LIVE MODE - type done to quit")) finalString += "// " + _e.Data + "\n";
                }
            });
            process.Exited += (s, evt) =>
            {
                process?.Dispose();
            };
        }

        private void OnKeyDownHandler(object sender, KeyEventArgs e)
        {

            if (e.KeyCode == Keys.Enter)
            {
                Send();
            }

        }


        private void textBoxLiveInput_Enter(object sender, EventArgs e)
        {
            Send();
        }

        private void buttonSend_Click(object sender, EventArgs e)
        {
            Send();
        }
        void Send()
        {
            inputText = this.textBoxLiveInput.Text;
            myStreamWriter.WriteLine(inputText);
            this.textBoxLiveInput.Text = "";
            this.textBoxLiveOutput.AppendText(inputText + Environment.NewLine);
            finalString += inputText + "\n";
        }
        private void buttonCopySelected_Click(object sender, EventArgs e)
        {
            try
            {
                Clipboard.SetText(textBoxLiveOutput.SelectedText);
            }
            catch { }
        }
        private void buttonAll_Click(object sender, EventArgs e)
        {
            Clipboard.SetText(finalString);
            this.Close();
        }

        private void FormLive_FormClosing(object sender, FormClosingEventArgs e)
        {
            myStreamWriter.WriteLine("done");
        }
    }
}
