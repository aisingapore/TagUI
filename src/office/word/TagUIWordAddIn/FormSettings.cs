using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace TagUIWordAddIn
{
    public partial class FormSettings : Form
    {
        public static string appDataFolder = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
        public static string taguiSrcPathTempString = appDataFolder + @"\TagUI\textBoxTaguiSrcPath.txt";
        public FormSettings()
        {
            InitializeComponent();
        }

        private void FormSettings_Load(object sender, EventArgs e)
        {
            Globals.Ribbons.Ribbon1.buttonSettings.Enabled = false;
            if (File.Exists(taguiSrcPathTempString))
            {
                string taguiSrcPath = File.ReadAllText(taguiSrcPathTempString);
                textBoxTaguiSrcPath.Text = taguiSrcPath;
            }
        }

        private void buttonSave_Click(object sender, EventArgs e)
        {
            using (StreamWriter writer = new StreamWriter(taguiSrcPathTempString, false))
            {
                string taguiSrcPath = textBoxTaguiSrcPath.Text;
                writer.Write(taguiSrcPath);
            }
            this.Close();
        }

        private void buttonAutofill_Click(object sender, EventArgs e)
        {
            string allPaths = Environment.GetEnvironmentVariable("Path").ToLower() + ";" + Environment.GetEnvironmentVariable("Path", EnvironmentVariableTarget.User).ToLower();
            string[] arr = allPaths.Split(';');

            // string taguiFolderPath = arr.Where(s => s.Contains("tagui")).FirstOrDefault();
            bool taguiFolderPathExists = Array.Exists(arr, s => s.Contains("tagui"));
            if (taguiFolderPathExists) textBoxTaguiSrcPath.Text = arr.Where(s => s.Contains("tagui")).FirstOrDefault();
            else MessageBox.Show("Unable to automatically find TagUI installation location, please specify your TagUI SRC folder by clicking on the browse button.");
        }

        private void buttonBrowse_Click(object sender, EventArgs e)
        {
            FolderBrowserDialog folderDlg = new FolderBrowserDialog();
            folderDlg.ShowNewFolderButton = true;
            DialogResult result = folderDlg.ShowDialog();
            if (result == DialogResult.OK)
            {
                textBoxTaguiSrcPath.Text = folderDlg.SelectedPath;
                Environment.SpecialFolder root = folderDlg.RootFolder;
            }
        }

        private void FormSettings_FormClosed(object sender, FormClosedEventArgs e)
        {
            Globals.Ribbons.Ribbon1.buttonSettings.Enabled = true;
        }
    }
}
