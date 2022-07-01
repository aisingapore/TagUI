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
    public partial class SnapshotBar : Form
    {
        public SnapshotBar()
        {
            InitializeComponent();
        }

        private void button1_Click(object sender, EventArgs e)
        {
            this.Hide();
            Form1 f1 = new Form1();
            if (checkBoxDelay.Checked)
            {
                System.Threading.Thread.Sleep(5000);
                Form2 f2 = new Form2();
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
    }
}
