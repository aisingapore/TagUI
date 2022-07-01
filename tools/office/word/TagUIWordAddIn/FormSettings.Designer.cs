
namespace TagUIWordAddIn
{
    partial class FormSettings
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
            this.textBoxTaguiSrcPath = new System.Windows.Forms.TextBox();
            this.labelTaguiSrcPath = new System.Windows.Forms.Label();
            this.buttonSave = new System.Windows.Forms.Button();
            this.buttonAutofill = new System.Windows.Forms.Button();
            this.buttonBrowse = new System.Windows.Forms.Button();
            this.SuspendLayout();
            // 
            // textBoxTaguiSrcPath
            // 
            this.textBoxTaguiSrcPath.Location = new System.Drawing.Point(144, 12);
            this.textBoxTaguiSrcPath.Name = "textBoxTaguiSrcPath";
            this.textBoxTaguiSrcPath.ReadOnly = true;
            this.textBoxTaguiSrcPath.Size = new System.Drawing.Size(386, 22);
            this.textBoxTaguiSrcPath.TabIndex = 0;
            // 
            // labelTaguiSrcPath
            // 
            this.labelTaguiSrcPath.AutoSize = true;
            this.labelTaguiSrcPath.Location = new System.Drawing.Point(12, 15);
            this.labelTaguiSrcPath.Name = "labelTaguiSrcPath";
            this.labelTaguiSrcPath.Size = new System.Drawing.Size(126, 17);
            this.labelTaguiSrcPath.TabIndex = 1;
            this.labelTaguiSrcPath.Text = "TagUI SRC Folder:";
            // 
            // buttonSave
            // 
            this.buttonSave.Location = new System.Drawing.Point(289, 87);
            this.buttonSave.Name = "buttonSave";
            this.buttonSave.Size = new System.Drawing.Size(143, 23);
            this.buttonSave.TabIndex = 2;
            this.buttonSave.Text = "Save and Close";
            this.buttonSave.UseVisualStyleBackColor = true;
            this.buttonSave.Click += new System.EventHandler(this.buttonSave_Click);
            // 
            // buttonAutofill
            // 
            this.buttonAutofill.Location = new System.Drawing.Point(619, 11);
            this.buttonAutofill.Name = "buttonAutofill";
            this.buttonAutofill.Size = new System.Drawing.Size(75, 23);
            this.buttonAutofill.TabIndex = 3;
            this.buttonAutofill.Text = "Autofill";
            this.buttonAutofill.UseVisualStyleBackColor = true;
            this.buttonAutofill.Click += new System.EventHandler(this.buttonAutofill_Click);
            // 
            // buttonBrowse
            // 
            this.buttonBrowse.Location = new System.Drawing.Point(538, 12);
            this.buttonBrowse.Name = "buttonBrowse";
            this.buttonBrowse.Size = new System.Drawing.Size(75, 23);
            this.buttonBrowse.TabIndex = 4;
            this.buttonBrowse.Text = "Browse";
            this.buttonBrowse.UseVisualStyleBackColor = true;
            this.buttonBrowse.Click += new System.EventHandler(this.buttonBrowse_Click);
            // 
            // FormSettings
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(716, 122);
            this.Controls.Add(this.buttonBrowse);
            this.Controls.Add(this.buttonAutofill);
            this.Controls.Add(this.buttonSave);
            this.Controls.Add(this.labelTaguiSrcPath);
            this.Controls.Add(this.textBoxTaguiSrcPath);
            this.Name = "FormSettings";
            this.ShowIcon = false;
            this.StartPosition = System.Windows.Forms.FormStartPosition.CenterScreen;
            this.Text = "TagUI Settings";
            this.FormClosed += new System.Windows.Forms.FormClosedEventHandler(this.FormSettings_FormClosed);
            this.Load += new System.EventHandler(this.FormSettings_Load);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.TextBox textBoxTaguiSrcPath;
        private System.Windows.Forms.Label labelTaguiSrcPath;
        private System.Windows.Forms.Button buttonSave;
        private System.Windows.Forms.Button buttonAutofill;
        private System.Windows.Forms.Button buttonBrowse;
    }
}