
namespace TagUIWordAddIn
{
    partial class ErrorDialog
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.buttonOk = new System.Windows.Forms.Button();
            this.checkBoxDetails = new System.Windows.Forms.CheckBox();
            this.textBoxErrorDetails = new System.Windows.Forms.TextBox();
            this.labelError = new System.Windows.Forms.Label();
            this.SuspendLayout();
            // 
            // buttonOk
            // 
            this.buttonOk.Location = new System.Drawing.Point(96, 43);
            this.buttonOk.Name = "buttonOk";
            this.buttonOk.Size = new System.Drawing.Size(75, 27);
            this.buttonOk.TabIndex = 0;
            this.buttonOk.Text = "OK";
            this.buttonOk.UseVisualStyleBackColor = true;
            this.buttonOk.Click += new System.EventHandler(this.buttonOk_Click);
            // 
            // checkBoxDetails
            // 
            this.checkBoxDetails.Appearance = System.Windows.Forms.Appearance.Button;
            this.checkBoxDetails.AutoSize = true;
            this.checkBoxDetails.Location = new System.Drawing.Point(15, 43);
            this.checkBoxDetails.Name = "checkBoxDetails";
            this.checkBoxDetails.Size = new System.Drawing.Size(79, 27);
            this.checkBoxDetails.TabIndex = 1;
            this.checkBoxDetails.Text = " ▼ Details";
            this.checkBoxDetails.UseVisualStyleBackColor = true;
            this.checkBoxDetails.CheckedChanged += new System.EventHandler(this.checkBoxDetails_CheckedChanged);
            // 
            // textBoxErrorDetails
            // 
            this.textBoxErrorDetails.Location = new System.Drawing.Point(15, 87);
            this.textBoxErrorDetails.Multiline = true;
            this.textBoxErrorDetails.Name = "textBoxErrorDetails";
            this.textBoxErrorDetails.ReadOnly = true;
            this.textBoxErrorDetails.ScrollBars = System.Windows.Forms.ScrollBars.Vertical;
            this.textBoxErrorDetails.Size = new System.Drawing.Size(700, 100);
            this.textBoxErrorDetails.TabIndex = 2;
            this.textBoxErrorDetails.Visible = false;
            // 
            // labelError
            // 
            this.labelError.AutoSize = true;
            this.labelError.Location = new System.Drawing.Point(15, 14);
            this.labelError.MaximumSize = new System.Drawing.Size(700, 0);
            this.labelError.Name = "labelError";
            this.labelError.Size = new System.Drawing.Size(46, 17);
            this.labelError.TabIndex = 3;
            this.labelError.Text = "label1";
            // 
            // ErrorDialog
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.AutoSize = true;
            this.ClientSize = new System.Drawing.Size(730, 82);
            this.Controls.Add(this.labelError);
            this.Controls.Add(this.textBoxErrorDetails);
            this.Controls.Add(this.checkBoxDetails);
            this.Controls.Add(this.buttonOk);
            this.FormBorderStyle = System.Windows.Forms.FormBorderStyle.FixedDialog;
            this.MaximizeBox = false;
            this.Name = "ErrorDialog";
            this.Padding = new System.Windows.Forms.Padding(0, 0, 0, 27);
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "Error";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Button buttonOk;
        private System.Windows.Forms.CheckBox checkBoxDetails;
        private System.Windows.Forms.TextBox textBoxErrorDetails;
        private System.Windows.Forms.Label labelError;
    }
}