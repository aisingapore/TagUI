using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace TagUIWordAddIn
{
    public partial class ErrorDialog : Form
    {
        public ErrorDialog()
        {
            InitializeComponent();
            checkBoxDetails.Location = new Point(this.checkBoxDetails.Location.X, this.labelError.Location.Y + this.labelError.Height + 37);
            buttonOk.Location = new Point(this.buttonOk.Location.X, this.labelError.Location.Y + this.labelError.Height + 37);
            textBoxErrorDetails.Location = new Point(this.checkBoxDetails.Location.X, this.checkBoxDetails.Location.Y + this.checkBoxDetails.Height + 17);
        }
        public DialogResult Show(string title, string messageText, string detailsText)
        {
            Text = title;
            labelError.Text = messageText;
            textBoxErrorDetails.Text = detailsText;
            return (ShowDialog());
        }

        private void checkBoxDetails_CheckedChanged(object sender, EventArgs e)
        {
            if (checkBoxDetails.Checked)
            {
                checkBoxDetails.Text = " ▲ Details";
                textBoxErrorDetails.Visible = true;
            }
            else
            {
                checkBoxDetails.Text = " ▼ Details";
                textBoxErrorDetails.Visible = false;
                this.Height = this.MinimumSize.Height;
            }
        }

        private void buttonOk_Click(object sender, EventArgs e)
        {
            this.Close();
        }

    }
}
