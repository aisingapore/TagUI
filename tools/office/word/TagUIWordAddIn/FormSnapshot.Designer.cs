
namespace TagUIWordAddIn
{
    partial class FormSnapshot
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
            this.button1 = new System.Windows.Forms.Button();
            this.label1 = new System.Windows.Forms.Label();
            this.checkBoxDelay = new System.Windows.Forms.CheckBox();
            this.SuspendLayout();
            // 
            // button1
            // 
            this.button1.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Stretch;
            this.button1.Image = global::TagUIWordAddIn.Properties.Resources.Snapshot;
            this.button1.Location = new System.Drawing.Point(180, 21);
            this.button1.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.button1.Name = "button1";
            this.button1.Size = new System.Drawing.Size(76, 68);
            this.button1.TabIndex = 0;
            this.button1.TextImageRelation = System.Windows.Forms.TextImageRelation.ImageBeforeText;
            this.button1.UseVisualStyleBackColor = true;
            this.button1.Click += new System.EventHandler(this.button1_Click);
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.Location = new System.Drawing.Point(11, 108);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(416, 17);
            this.label1.TabIndex = 1;
            this.label1.Text = "Click snapshot icon, then drag mouse to capture area on screen.";
            // 
            // checkBoxDelay
            // 
            this.checkBoxDelay.AutoSize = true;
            this.checkBoxDelay.Location = new System.Drawing.Point(71, 134);
            this.checkBoxDelay.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxDelay.Name = "checkBoxDelay";
            this.checkBoxDelay.Size = new System.Drawing.Size(293, 21);
            this.checkBoxDelay.TabIndex = 4;
            this.checkBoxDelay.Text = "Optional 5s delay (e.g. for dropdown lists)";
            this.checkBoxDelay.UseVisualStyleBackColor = true;
            // 
            // FormSnapshot
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(431, 163);
            this.Controls.Add(this.checkBoxDelay);
            this.Controls.Add(this.label1);
            this.Controls.Add(this.button1);
            this.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.Name = "FormSnapshot";
            this.ShowIcon = false;
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "Snapshot";
            this.TopMost = true;
            this.FormClosed += new System.Windows.Forms.FormClosedEventHandler(this.FormSnapshot_FormClosed);
            this.Load += new System.EventHandler(this.FormSnapshot_Load);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Button button1;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.CheckBox checkBoxDelay;
    }
}