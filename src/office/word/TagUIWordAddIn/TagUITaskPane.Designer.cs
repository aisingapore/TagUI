
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
            System.ComponentModel.ComponentResourceManager resources = new System.ComponentModel.ComponentResourceManager(typeof(TagUITaskPane));
            this.labelRunOptions = new System.Windows.Forms.Label();
            this.checkBoxNoBrowser = new System.Windows.Forms.CheckBox();
            this.checkBoxReport = new System.Windows.Forms.CheckBox();
            this.checkBoxQuiet = new System.Windows.Forms.CheckBox();
            this.checkBoxDatatableCSV = new System.Windows.Forms.CheckBox();
            this.checkBoxInputs = new System.Windows.Forms.CheckBox();
            this.textBoxDatatableCSV = new System.Windows.Forms.TextBox();
            this.textBoxParam = new System.Windows.Forms.TextBox();
            this.labelOutput = new System.Windows.Forms.Label();
            this.buttonBrowse = new System.Windows.Forms.Button();
            this.textBoxOutput = new System.Windows.Forms.TextBox();
            this.comboBoxDatatableWs = new System.Windows.Forms.ComboBox();
            this.comboBoxObjRepo = new System.Windows.Forms.ComboBox();
            this.buttonObjRepo = new System.Windows.Forms.Button();
            this.textBoxObjRepo = new System.Windows.Forms.TextBox();
            this.checkBoxObjRepo = new System.Windows.Forms.CheckBox();
            this.pictureBox1 = new System.Windows.Forms.PictureBox();
            this.pictureBox2 = new System.Windows.Forms.PictureBox();
            this.textBoxRange = new System.Windows.Forms.TextBox();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).BeginInit();
            this.SuspendLayout();
            // 
            // labelRunOptions
            // 
            this.labelRunOptions.AutoSize = true;
            this.labelRunOptions.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelRunOptions.Location = new System.Drawing.Point(3, 4);
            this.labelRunOptions.Name = "labelRunOptions";
            this.labelRunOptions.Size = new System.Drawing.Size(71, 20);
            this.labelRunOptions.TabIndex = 6;
            this.labelRunOptions.Text = "Options";
            // 
            // checkBoxNoBrowser
            // 
            this.checkBoxNoBrowser.AutoSize = true;
            this.checkBoxNoBrowser.Location = new System.Drawing.Point(7, 28);
            this.checkBoxNoBrowser.Name = "checkBoxNoBrowser";
            this.checkBoxNoBrowser.Size = new System.Drawing.Size(117, 24);
            this.checkBoxNoBrowser.TabIndex = 9;
            this.checkBoxNoBrowser.Text = "No Browser";
            this.checkBoxNoBrowser.UseVisualStyleBackColor = true;
            // 
            // checkBoxReport
            // 
            this.checkBoxReport.AutoSize = true;
            this.checkBoxReport.Location = new System.Drawing.Point(7, 66);
            this.checkBoxReport.Name = "checkBoxReport";
            this.checkBoxReport.Size = new System.Drawing.Size(124, 24);
            this.checkBoxReport.TabIndex = 10;
            this.checkBoxReport.Text = "Save Report";
            this.checkBoxReport.UseVisualStyleBackColor = true;
            // 
            // checkBoxQuiet
            // 
            this.checkBoxQuiet.AutoSize = true;
            this.checkBoxQuiet.Location = new System.Drawing.Point(7, 104);
            this.checkBoxQuiet.Name = "checkBoxQuiet";
            this.checkBoxQuiet.Size = new System.Drawing.Size(117, 24);
            this.checkBoxQuiet.TabIndex = 11;
            this.checkBoxQuiet.Text = "Quiet Mode";
            this.checkBoxQuiet.UseVisualStyleBackColor = true;
            // 
            // checkBoxDatatableCSV
            // 
            this.checkBoxDatatableCSV.AutoSize = true;
            this.checkBoxDatatableCSV.Location = new System.Drawing.Point(7, 142);
            this.checkBoxDatatableCSV.Name = "checkBoxDatatableCSV";
            this.checkBoxDatatableCSV.Size = new System.Drawing.Size(138, 24);
            this.checkBoxDatatableCSV.TabIndex = 12;
            this.checkBoxDatatableCSV.Text = "Datatable File:";
            this.checkBoxDatatableCSV.UseVisualStyleBackColor = true;
            this.checkBoxDatatableCSV.CheckedChanged += new System.EventHandler(this.checkBoxDatatableCSV_CheckedChanged);
            // 
            // checkBoxInputs
            // 
            this.checkBoxInputs.AutoSize = true;
            this.checkBoxInputs.Location = new System.Drawing.Point(7, 384);
            this.checkBoxInputs.Name = "checkBoxInputs";
            this.checkBoxInputs.Size = new System.Drawing.Size(190, 24);
            this.checkBoxInputs.TabIndex = 13;
            this.checkBoxInputs.Text = "Workflow Parameters:";
            this.checkBoxInputs.UseVisualStyleBackColor = true;
            this.checkBoxInputs.CheckedChanged += new System.EventHandler(this.checkBoxInputs_CheckedChanged);
            // 
            // textBoxDatatableCSV
            // 
            this.textBoxDatatableCSV.Location = new System.Drawing.Point(35, 172);
            this.textBoxDatatableCSV.MaximumSize = new System.Drawing.Size(500, 26);
            this.textBoxDatatableCSV.MinimumSize = new System.Drawing.Size(174, 26);
            this.textBoxDatatableCSV.Name = "textBoxDatatableCSV";
            this.textBoxDatatableCSV.ReadOnly = true;
            this.textBoxDatatableCSV.Size = new System.Drawing.Size(174, 26);
            this.textBoxDatatableCSV.TabIndex = 16;
            // 
            // textBoxParam
            // 
            this.textBoxParam.Enabled = false;
            this.textBoxParam.Location = new System.Drawing.Point(35, 414);
            this.textBoxParam.Name = "textBoxParam";
            this.textBoxParam.Size = new System.Drawing.Size(174, 26);
            this.textBoxParam.TabIndex = 20;
            // 
            // labelOutput
            // 
            this.labelOutput.AutoSize = true;
            this.labelOutput.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelOutput.Location = new System.Drawing.Point(3, 459);
            this.labelOutput.Name = "labelOutput";
            this.labelOutput.Size = new System.Drawing.Size(64, 20);
            this.labelOutput.TabIndex = 25;
            this.labelOutput.Text = "Output";
            // 
            // buttonBrowse
            // 
            this.buttonBrowse.Enabled = false;
            this.buttonBrowse.Location = new System.Drawing.Point(213, 172);
            this.buttonBrowse.Name = "buttonBrowse";
            this.buttonBrowse.Size = new System.Drawing.Size(72, 26);
            this.buttonBrowse.TabIndex = 26;
            this.buttonBrowse.Text = "Browse";
            this.buttonBrowse.UseVisualStyleBackColor = true;
            this.buttonBrowse.Click += new System.EventHandler(this.buttonBrowse_Click);
            // 
            // textBoxOutput
            // 
            this.textBoxOutput.Location = new System.Drawing.Point(7, 484);
            this.textBoxOutput.Multiline = true;
            this.textBoxOutput.Name = "textBoxOutput";
            this.textBoxOutput.ReadOnly = true;
            this.textBoxOutput.ScrollBars = System.Windows.Forms.ScrollBars.Both;
            this.textBoxOutput.Size = new System.Drawing.Size(276, 189);
            this.textBoxOutput.TabIndex = 28;
            // 
            // comboBoxDatatableWs
            // 
            this.comboBoxDatatableWs.BackColor = System.Drawing.SystemColors.Window;
            this.comboBoxDatatableWs.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.comboBoxDatatableWs.Enabled = false;
            this.comboBoxDatatableWs.FormattingEnabled = true;
            this.comboBoxDatatableWs.Location = new System.Drawing.Point(35, 207);
            this.comboBoxDatatableWs.Name = "comboBoxDatatableWs";
            this.comboBoxDatatableWs.Size = new System.Drawing.Size(174, 28);
            this.comboBoxDatatableWs.TabIndex = 29;
            this.comboBoxDatatableWs.Click += new System.EventHandler(this.comboBoxDatatableWs_Click);
            // 
            // comboBoxObjRepo
            // 
            this.comboBoxObjRepo.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.comboBoxObjRepo.Enabled = false;
            this.comboBoxObjRepo.FormattingEnabled = true;
            this.comboBoxObjRepo.Location = new System.Drawing.Point(35, 345);
            this.comboBoxObjRepo.Name = "comboBoxObjRepo";
            this.comboBoxObjRepo.Size = new System.Drawing.Size(174, 28);
            this.comboBoxObjRepo.TabIndex = 35;
            this.comboBoxObjRepo.Click += new System.EventHandler(this.comboBoxObjRepo_Click);
            // 
            // buttonObjRepo
            // 
            this.buttonObjRepo.Enabled = false;
            this.buttonObjRepo.Location = new System.Drawing.Point(213, 310);
            this.buttonObjRepo.Name = "buttonObjRepo";
            this.buttonObjRepo.Size = new System.Drawing.Size(72, 26);
            this.buttonObjRepo.TabIndex = 34;
            this.buttonObjRepo.Text = "Browse";
            this.buttonObjRepo.UseVisualStyleBackColor = true;
            this.buttonObjRepo.Click += new System.EventHandler(this.buttonObjRepo_Click);
            // 
            // textBoxObjRepo
            // 
            this.textBoxObjRepo.Location = new System.Drawing.Point(35, 310);
            this.textBoxObjRepo.Name = "textBoxObjRepo";
            this.textBoxObjRepo.ReadOnly = true;
            this.textBoxObjRepo.Size = new System.Drawing.Size(174, 26);
            this.textBoxObjRepo.TabIndex = 33;
            // 
            // checkBoxObjRepo
            // 
            this.checkBoxObjRepo.AutoSize = true;
            this.checkBoxObjRepo.Location = new System.Drawing.Point(7, 280);
            this.checkBoxObjRepo.Name = "checkBoxObjRepo";
            this.checkBoxObjRepo.Size = new System.Drawing.Size(165, 24);
            this.checkBoxObjRepo.TabIndex = 32;
            this.checkBoxObjRepo.Text = "Object Repository:";
            this.checkBoxObjRepo.UseVisualStyleBackColor = true;
            this.checkBoxObjRepo.CheckedChanged += new System.EventHandler(this.checkBoxObjRepo_CheckedChanged);
            // 
            // pictureBox1
            // 
            this.pictureBox1.BackgroundImage = ((System.Drawing.Image)(resources.GetObject("pictureBox1.BackgroundImage")));
            this.pictureBox1.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Stretch;
            this.pictureBox1.Location = new System.Drawing.Point(141, 145);
            this.pictureBox1.Name = "pictureBox1";
            this.pictureBox1.Size = new System.Drawing.Size(15, 15);
            this.pictureBox1.TabIndex = 37;
            this.pictureBox1.TabStop = false;
            // 
            // pictureBox2
            // 
            this.pictureBox2.BackgroundImage = ((System.Drawing.Image)(resources.GetObject("pictureBox2.BackgroundImage")));
            this.pictureBox2.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Stretch;
            this.pictureBox2.Location = new System.Drawing.Point(168, 283);
            this.pictureBox2.Name = "pictureBox2";
            this.pictureBox2.Size = new System.Drawing.Size(15, 15);
            this.pictureBox2.TabIndex = 38;
            this.pictureBox2.TabStop = false;
            // 
            // textBoxRange
            // 
            this.textBoxRange.Enabled = false;
            this.textBoxRange.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.textBoxRange.ForeColor = System.Drawing.SystemColors.Desktop;
            this.textBoxRange.Location = new System.Drawing.Point(35, 244);
            this.textBoxRange.Name = "textBoxRange";
            this.textBoxRange.Size = new System.Drawing.Size(174, 26);
            this.textBoxRange.TabIndex = 39;
            this.textBoxRange.Text = "Optional range";
            this.textBoxRange.Enter += new System.EventHandler(this.textBoxRange_Enter);
            // 
            // TagUITaskPane
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(9F, 20F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.AutoSize = true;
            this.AutoSizeMode = System.Windows.Forms.AutoSizeMode.GrowAndShrink;
            this.Controls.Add(this.textBoxRange);
            this.Controls.Add(this.pictureBox2);
            this.Controls.Add(this.pictureBox1);
            this.Controls.Add(this.comboBoxObjRepo);
            this.Controls.Add(this.buttonObjRepo);
            this.Controls.Add(this.textBoxObjRepo);
            this.Controls.Add(this.checkBoxObjRepo);
            this.Controls.Add(this.comboBoxDatatableWs);
            this.Controls.Add(this.textBoxOutput);
            this.Controls.Add(this.buttonBrowse);
            this.Controls.Add(this.labelOutput);
            this.Controls.Add(this.textBoxParam);
            this.Controls.Add(this.textBoxDatatableCSV);
            this.Controls.Add(this.checkBoxInputs);
            this.Controls.Add(this.checkBoxDatatableCSV);
            this.Controls.Add(this.checkBoxQuiet);
            this.Controls.Add(this.checkBoxReport);
            this.Controls.Add(this.checkBoxNoBrowser);
            this.Controls.Add(this.labelRunOptions);
            this.Name = "TagUITaskPane";
            this.Size = new System.Drawing.Size(288, 676);
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).EndInit();
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
        private System.Windows.Forms.Label labelOutput;
        private System.Windows.Forms.Button buttonBrowse;
        private System.Windows.Forms.TextBox textBoxOutput;
        private System.Windows.Forms.ComboBox comboBoxDatatableWs;
        private System.Windows.Forms.ComboBox comboBoxObjRepo;
        private System.Windows.Forms.Button buttonObjRepo;
        private System.Windows.Forms.TextBox textBoxObjRepo;
        private System.Windows.Forms.CheckBox checkBoxObjRepo;
        private System.Windows.Forms.PictureBox pictureBox1;
        private System.Windows.Forms.PictureBox pictureBox2;
        private System.Windows.Forms.TextBox textBoxRange;
    }
}
