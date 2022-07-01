
namespace TagUIWordAddIn
{
    partial class FormLive
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
            this.textBoxLiveInput = new System.Windows.Forms.TextBox();
            this.textBoxLiveOutput = new System.Windows.Forms.TextBox();
            this.buttonSend = new System.Windows.Forms.Button();
            this.buttonCopySelected = new System.Windows.Forms.Button();
            this.buttonAll = new System.Windows.Forms.Button();
            this.SuspendLayout();
            // 
            // textBoxLiveInput
            // 
            this.textBoxLiveInput.Location = new System.Drawing.Point(26, 28);
            this.textBoxLiveInput.Name = "textBoxLiveInput";
            this.textBoxLiveInput.Size = new System.Drawing.Size(555, 22);
            this.textBoxLiveInput.TabIndex = 0;
            // 
            // textBoxLiveOutput
            // 
            this.textBoxLiveOutput.Location = new System.Drawing.Point(26, 71);
            this.textBoxLiveOutput.Multiline = true;
            this.textBoxLiveOutput.Name = "textBoxLiveOutput";
            this.textBoxLiveOutput.ReadOnly = true;
            this.textBoxLiveOutput.ScrollBars = System.Windows.Forms.ScrollBars.Vertical;
            this.textBoxLiveOutput.Size = new System.Drawing.Size(555, 164);
            this.textBoxLiveOutput.TabIndex = 1;
            // 
            // buttonSend
            // 
            this.buttonSend.Location = new System.Drawing.Point(598, 27);
            this.buttonSend.Name = "buttonSend";
            this.buttonSend.Size = new System.Drawing.Size(75, 24);
            this.buttonSend.TabIndex = 2;
            this.buttonSend.Text = "Send";
            this.buttonSend.UseVisualStyleBackColor = true;
            this.buttonSend.Click += new System.EventHandler(this.buttonSend_Click);
            // 
            // buttonCopySelected
            // 
            this.buttonCopySelected.Location = new System.Drawing.Point(26, 245);
            this.buttonCopySelected.Name = "buttonCopySelected";
            this.buttonCopySelected.Size = new System.Drawing.Size(255, 24);
            this.buttonCopySelected.TabIndex = 4;
            this.buttonCopySelected.Text = "Copy Selected Line(s) to Clipboard";
            this.buttonCopySelected.UseVisualStyleBackColor = true;
            this.buttonCopySelected.Click += new System.EventHandler(this.buttonCopySelected_Click);
            // 
            // buttonAll
            // 
            this.buttonAll.Location = new System.Drawing.Point(287, 245);
            this.buttonAll.Name = "buttonAll";
            this.buttonAll.Size = new System.Drawing.Size(294, 24);
            this.buttonAll.TabIndex = 5;
            this.buttonAll.Text = "Copy All to Clipboard and End Live Mode";
            this.buttonAll.UseVisualStyleBackColor = true;
            this.buttonAll.Click += new System.EventHandler(this.buttonAll_Click);
            // 
            // FormLive
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(702, 283);
            this.Controls.Add(this.buttonAll);
            this.Controls.Add(this.buttonCopySelected);
            this.Controls.Add(this.buttonSend);
            this.Controls.Add(this.textBoxLiveOutput);
            this.Controls.Add(this.textBoxLiveInput);
            this.Name = "FormLive";
            this.ShowIcon = false;
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "Live Mode";
            this.TopMost = true;
            this.FormClosing += new System.Windows.Forms.FormClosingEventHandler(this.FormLive_FormClosing);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.TextBox textBoxLiveInput;
        private System.Windows.Forms.TextBox textBoxLiveOutput;
        private System.Windows.Forms.Button buttonSend;
        private System.Windows.Forms.Button buttonCopySelected;
        private System.Windows.Forms.Button buttonAll;
    }
}