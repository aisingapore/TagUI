using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Drawing.Imaging;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace TagUIWordAddIn
{
    public partial class FormSnapshot : Form
    {
        public FormSnapshot()
        {
            InitializeComponent();
        }

        private void FormSnapshot_Load(object sender, EventArgs e)
        {
            Globals.Ribbons.Ribbon1.buttonSnapshot.Enabled = false;
        }
        private void button1_Click(object sender, EventArgs e)
        {
            this.Hide();
            FormSnapshotOverlay1 f1 = new FormSnapshotOverlay1();
            if (checkBoxDelay.Checked)
            {
                System.Threading.Thread.Sleep(5000);
                FormSnapshotOverlay2 f2 = new FormSnapshotOverlay2();
                f1.Owner = f2;
               
                f2.Show();
                f1.Show();
                f1.Closed += (s, args) =>
                {
                    this.Close();
                    f2.Close();

                };
            }
            else
            {
                f1.Show();
                f1.Closed += (s, args) =>
                {
                    this.Close();
                };
            }
            f1.Closed += (s, args) =>
            {
                this.Close();               
            };
        }

        private void FormSnapshot_FormClosed(object sender, FormClosedEventArgs e)
        {
            Globals.Ribbons.Ribbon1.buttonSnapshot.Enabled = true;
        }
    }
}
