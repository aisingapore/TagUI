using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

using Word = Microsoft.Office.Interop.Word;

namespace TagUIWordAddIn
{
    public partial class FormLiveInline : Form
    {
        public bool formClosing = false;
        public FormLiveInline()
        {
            InitializeComponent();
            Ribbon1.FormLiveInline = this;
            Ribbon1.textBox2 = textBoxLiveInlineInput;
            Ribbon1.FormLiveInline.Controls["buttonSend"].Click += Ribbon1.OnButtonSendClick;
            Ribbon1.FormLiveInline.Controls["buttonCopySelected"].Click += Ribbon1.OnButtonCopySelectedClick;
            Ribbon1.FormLiveInline.Controls["buttonAll"].Click += Ribbon1.OnButtonAllClick;
            this.textBoxLiveInlineInput.KeyDown += new System.Windows.Forms.KeyEventHandler(this.OnKeyDownHandler);
            this.Closing += new CancelEventHandler(this.FormLiveInline_FormClosing);
            formClosing = false;
            this.textBoxLiveInlineInput.Select();
        }
        private void OnKeyDownHandler(object sender, KeyEventArgs e)
        {

            if (e.KeyCode == Keys.Enter)
            {
                Ribbon1.OnButtonSendClick(sender, e);
            }

        }

        private void FormLiveInline_FormClosing(object sender, CancelEventArgs e)
        {
            Ribbon1.textBox2.Text = "done";
            if (formClosing == false)
            {
                formClosing = true;
                e.Cancel = true;
                Ribbon1.OnButtonSendClick(sender, e);
            }
        }
    }
}
