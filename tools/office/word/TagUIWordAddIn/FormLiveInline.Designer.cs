
namespace TagUIWordAddIn
{
    partial class FormLiveInline
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
            this.buttonSend = new System.Windows.Forms.Button();
            this.buttonCopySelected = new System.Windows.Forms.Button();
            this.buttonAll = new System.Windows.Forms.Button();
            this.textBoxLiveInlineInput = new System.Windows.Forms.TextBox();
            this.textBoxLiveInlineOutput = new System.Windows.Forms.TextBox();
            this.SuspendLayout();
            // 
            // buttonSend
            // 
            this.buttonSend.Location = new System.Drawing.Point(595, 27);
            this.buttonSend.Name = "buttonSend";
            this.buttonSend.Size = new System.Drawing.Size(75, 24);
            this.buttonSend.TabIndex = 0;
            this.buttonSend.Text = "Send";
            this.buttonSend.UseVisualStyleBackColor = true;
            // 
            // buttonCopySelected
            // 
            this.buttonCopySelected.Location = new System.Drawing.Point(26, 245);
            this.buttonCopySelected.Name = "buttonCopySelected";
            this.buttonCopySelected.Size = new System.Drawing.Size(255, 24);
            this.buttonCopySelected.TabIndex = 1;
            this.buttonCopySelected.Text = "Copy Selected Line(s) to Clipboard";
            this.buttonCopySelected.UseVisualStyleBackColor = true;
            // 
            // buttonAll
            // 
            this.buttonAll.Location = new System.Drawing.Point(287, 245);
            this.buttonAll.Name = "buttonAll";
            this.buttonAll.Size = new System.Drawing.Size(294, 24);
            this.buttonAll.TabIndex = 2;
            this.buttonAll.Text = "Copy All to Clipboard and End Live Mode";
            this.buttonAll.UseVisualStyleBackColor = true;
            // 
            // textBoxLiveInlineInput
            // 
            this.textBoxLiveInlineInput.Location = new System.Drawing.Point(26, 28);
            this.textBoxLiveInlineInput.Name = "textBoxLiveInlineInput";
            this.textBoxLiveInlineInput.Size = new System.Drawing.Size(555, 22);
            this.textBoxLiveInlineInput.TabIndex = 3;
            // 
            // textBoxLiveInlineOutput
            // 
            this.textBoxLiveInlineOutput.Location = new System.Drawing.Point(26, 71);
            this.textBoxLiveInlineOutput.Multiline = true;
            this.textBoxLiveInlineOutput.Name = "textBoxLiveInlineOutput";
            this.textBoxLiveInlineOutput.ReadOnly = true;
            this.textBoxLiveInlineOutput.ScrollBars = System.Windows.Forms.ScrollBars.Vertical;
            this.textBoxLiveInlineOutput.Size = new System.Drawing.Size(555, 164);
            this.textBoxLiveInlineOutput.TabIndex = 4;
            // 
            // FormLiveInline
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(702, 283);
            this.Controls.Add(this.textBoxLiveInlineOutput);
            this.Controls.Add(this.textBoxLiveInlineInput);
            this.Controls.Add(this.buttonAll);
            this.Controls.Add(this.buttonCopySelected);
            this.Controls.Add(this.buttonSend);
            this.Name = "FormLiveInline";
            this.ShowIcon = false;
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "Live Mode";
            this.TopMost = true;
            this.FormClosing += new System.Windows.Forms.FormClosingEventHandler(this.FormLiveInline_FormClosing);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Button buttonSend;
        private System.Windows.Forms.Button buttonCopySelected;
        private System.Windows.Forms.Button buttonAll;
        private System.Windows.Forms.TextBox textBoxLiveInlineInput;
        private System.Windows.Forms.TextBox textBoxLiveInlineOutput;
    }
}