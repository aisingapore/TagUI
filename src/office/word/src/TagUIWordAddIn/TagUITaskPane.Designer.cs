
namespace TagUIWordAddIn
{
    partial class TagUITaskPane
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

        #region Component Designer generated code

        /// <summary> 
        /// Required method for Designer support - do not modify 
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.labelRunOptions = new System.Windows.Forms.Label();
            this.checkBoxNoBrowser = new System.Windows.Forms.CheckBox();
            this.checkBoxReport = new System.Windows.Forms.CheckBox();
            this.checkBoxQuiet = new System.Windows.Forms.CheckBox();
            this.checkBoxDatatableCSV = new System.Windows.Forms.CheckBox();
            this.checkBoxInputs = new System.Windows.Forms.CheckBox();
            this.textBoxDatatableCSV = new System.Windows.Forms.TextBox();
            this.textBoxParam = new System.Windows.Forms.TextBox();
            this.labelDocumentation = new System.Windows.Forms.Label();
            this.buttonDeploy = new System.Windows.Forms.Button();
            this.buttonRun = new System.Windows.Forms.Button();
            this.SuspendLayout();
            // 
            // labelRunOptions
            // 
            this.labelRunOptions.AutoSize = true;
            this.labelRunOptions.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelRunOptions.Location = new System.Drawing.Point(12, 23);
            this.labelRunOptions.Name = "labelRunOptions";
            this.labelRunOptions.Size = new System.Drawing.Size(109, 20);
            this.labelRunOptions.TabIndex = 6;
            this.labelRunOptions.Text = "Run Options";
            // 
            // checkBoxNoBrowser
            // 
            this.checkBoxNoBrowser.AutoSize = true;
            this.checkBoxNoBrowser.Location = new System.Drawing.Point(16, 56);
            this.checkBoxNoBrowser.Name = "checkBoxNoBrowser";
            this.checkBoxNoBrowser.Size = new System.Drawing.Size(117, 24);
            this.checkBoxNoBrowser.TabIndex = 9;
            this.checkBoxNoBrowser.Text = "No Browser";
            this.checkBoxNoBrowser.UseVisualStyleBackColor = true;
            // 
            // checkBoxReport
            // 
            this.checkBoxReport.AutoSize = true;
            this.checkBoxReport.Location = new System.Drawing.Point(16, 94);
            this.checkBoxReport.Name = "checkBoxReport";
            this.checkBoxReport.Size = new System.Drawing.Size(124, 24);
            this.checkBoxReport.TabIndex = 10;
            this.checkBoxReport.Text = "Save Report";
            this.checkBoxReport.UseVisualStyleBackColor = true;
            // 
            // checkBoxQuiet
            // 
            this.checkBoxQuiet.AutoSize = true;
            this.checkBoxQuiet.Location = new System.Drawing.Point(16, 132);
            this.checkBoxQuiet.Name = "checkBoxQuiet";
            this.checkBoxQuiet.Size = new System.Drawing.Size(117, 24);
            this.checkBoxQuiet.TabIndex = 11;
            this.checkBoxQuiet.Text = "Quiet Mode";
            this.checkBoxQuiet.UseVisualStyleBackColor = true;
            // 
            // checkBoxDatatableCSV
            // 
            this.checkBoxDatatableCSV.AutoSize = true;
            this.checkBoxDatatableCSV.Location = new System.Drawing.Point(16, 170);
            this.checkBoxDatatableCSV.Name = "checkBoxDatatableCSV";
            this.checkBoxDatatableCSV.Size = new System.Drawing.Size(175, 24);
            this.checkBoxDatatableCSV.TabIndex = 12;
            this.checkBoxDatatableCSV.Text = "CSV Datatable File:";
            this.checkBoxDatatableCSV.UseVisualStyleBackColor = true;
            this.checkBoxDatatableCSV.CheckedChanged += new System.EventHandler(this.checkBoxDatatableCSV_CheckedChanged);
            // 
            // checkBoxInputs
            // 
            this.checkBoxInputs.AutoSize = true;
            this.checkBoxInputs.Location = new System.Drawing.Point(16, 238);
            this.checkBoxInputs.Name = "checkBoxInputs";
            this.checkBoxInputs.Size = new System.Drawing.Size(183, 24);
            this.checkBoxInputs.TabIndex = 13;
            this.checkBoxInputs.Text = "Parameters p1 to p8:";
            this.checkBoxInputs.UseVisualStyleBackColor = true;
            this.checkBoxInputs.CheckedChanged += new System.EventHandler(this.checkBoxInputs_CheckedChanged);
            // 
            // textBoxDatatableCSV
            // 
            this.textBoxDatatableCSV.Enabled = false;
            this.textBoxDatatableCSV.Location = new System.Drawing.Point(44, 200);
            this.textBoxDatatableCSV.Name = "textBoxDatatableCSV";
            this.textBoxDatatableCSV.Size = new System.Drawing.Size(174, 26);
            this.textBoxDatatableCSV.TabIndex = 16;
            // 
            // textBoxParam
            // 
            this.textBoxParam.Enabled = false;
            this.textBoxParam.Location = new System.Drawing.Point(44, 268);
            this.textBoxParam.Name = "textBoxParam";
            this.textBoxParam.Size = new System.Drawing.Size(174, 26);
            this.textBoxParam.TabIndex = 20;
            // 
            // labelDocumentation
            // 
            this.labelDocumentation.ForeColor = System.Drawing.SystemColors.Highlight;
            this.labelDocumentation.Location = new System.Drawing.Point(12, 386);
            this.labelDocumentation.Name = "labelDocumentation";
            this.labelDocumentation.Size = new System.Drawing.Size(118, 20);
            this.labelDocumentation.TabIndex = 21;
            this.labelDocumentation.Text = "See Docs     ";
            this.labelDocumentation.Click += new System.EventHandler(this.labelDocumentation_Click);
            // 
            // buttonDeploy
            // 
            this.buttonDeploy.Location = new System.Drawing.Point(124, 330);
            this.buttonDeploy.Name = "buttonDeploy";
            this.buttonDeploy.Size = new System.Drawing.Size(94, 40);
            this.buttonDeploy.TabIndex = 23;
            this.buttonDeploy.Text = "Deploy";
            this.buttonDeploy.UseVisualStyleBackColor = true;
            this.buttonDeploy.Click += new System.EventHandler(this.buttonDeploy_Click);
            // 
            // buttonRun
            // 
            this.buttonRun.Location = new System.Drawing.Point(16, 330);
            this.buttonRun.Name = "buttonRun";
            this.buttonRun.Size = new System.Drawing.Size(94, 40);
            this.buttonRun.TabIndex = 24;
            this.buttonRun.Text = "Run";
            this.buttonRun.UseVisualStyleBackColor = true;
            this.buttonRun.Click += new System.EventHandler(this.buttonRun_Click);
            // 
            // TagUITaskPane
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(9F, 20F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.Controls.Add(this.buttonRun);
            this.Controls.Add(this.buttonDeploy);
            this.Controls.Add(this.labelDocumentation);
            this.Controls.Add(this.textBoxParam);
            this.Controls.Add(this.textBoxDatatableCSV);
            this.Controls.Add(this.checkBoxInputs);
            this.Controls.Add(this.checkBoxDatatableCSV);
            this.Controls.Add(this.checkBoxQuiet);
            this.Controls.Add(this.checkBoxReport);
            this.Controls.Add(this.checkBoxNoBrowser);
            this.Controls.Add(this.labelRunOptions);
            this.Name = "TagUITaskPane";
            this.Size = new System.Drawing.Size(250, 431);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion
        private System.Windows.Forms.Label labelRunOptions;
        private System.Windows.Forms.CheckBox checkBoxNoBrowser;
        private System.Windows.Forms.CheckBox checkBoxReport;
        private System.Windows.Forms.CheckBox checkBoxQuiet;
        private System.Windows.Forms.CheckBox checkBoxDatatableCSV;
        private System.Windows.Forms.CheckBox checkBoxInputs;
        private System.Windows.Forms.TextBox textBoxDatatableCSV;
        private System.Windows.Forms.TextBox textBoxParam;
        private System.Windows.Forms.Label labelDocumentation;
        private System.Windows.Forms.Button buttonDeploy;
        private System.Windows.Forms.Button buttonRun;
    }
}
