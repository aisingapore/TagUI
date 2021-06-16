
namespace TagUIWordAddIn
{
    partial class FormUpdate
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
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(FormUpdate));
            this.button1 = new System.Windows.Forms.Button();
            this.labelUpdateTerms = new System.Windows.Forms.Label();
            this.SuspendLayout();
            // 
            // button1
            // 
            this.button1.Location = new System.Drawing.Point(233, 92);
            this.button1.Name = "button1";
            this.button1.Size = new System.Drawing.Size(107, 52);
            this.button1.TabIndex = 0;
            this.button1.Text = "Update";
            this.button1.UseVisualStyleBackColor = true;
            this.button1.Click += new System.EventHandler(this.button1_Click);
            // 
            // labelUpdateTerms
            // 
            this.labelUpdateTerms.AutoSize = true;
            this.labelUpdateTerms.Location = new System.Drawing.Point(22, 24);
            this.labelUpdateTerms.MaximumSize = new System.Drawing.Size(600, 0);
            this.labelUpdateTerms.Name = "labelUpdateTerms";
            this.labelUpdateTerms.Size = new System.Drawing.Size(550, 40);
            this.labelUpdateTerms.TabIndex = 1;
            this.labelUpdateTerms.Text = "This pulls the latest TagUI update from the server and overwrites the existing ve" +
    "rsion. Internet access is required. Click on the Update button to start.";
            // 
            // FormUpdate
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(9F, 20F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(609, 185);
            this.Controls.Add(this.labelUpdateTerms);
            this.Controls.Add(this.button1);
            this.Icon = ((System.Drawing.Icon)(resources.GetObject("$this.Icon")));
            this.Name = "FormUpdate";
            this.Text = "Update TagUI";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Button button1;
        private System.Windows.Forms.Label labelUpdateTerms;
    }
}