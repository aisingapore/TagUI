
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
            this.checkBoxTurbo = new System.Windows.Forms.CheckBox();
            this.checkBoxMSEdge = new System.Windows.Forms.CheckBox();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).BeginInit();
            this.SuspendLayout();
            // 
            // labelRunOptions
            // 
            this.labelRunOptions.AutoSize = true;
            this.labelRunOptions.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelRunOptions.Location = new System.Drawing.Point(3, 3);
            this.labelRunOptions.Name = "labelRunOptions";
            this.labelRunOptions.Size = new System.Drawing.Size(64, 17);
            this.labelRunOptions.TabIndex = 6;
            this.labelRunOptions.Text = "Options";
            // 
            // checkBoxNoBrowser
            // 
            this.checkBoxNoBrowser.AutoSize = true;
            this.checkBoxNoBrowser.Location = new System.Drawing.Point(6, 24);
            this.checkBoxNoBrowser.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxNoBrowser.Name = "checkBoxNoBrowser";
            this.checkBoxNoBrowser.Size = new System.Drawing.Size(103, 21);
            this.checkBoxNoBrowser.TabIndex = 9;
            this.checkBoxNoBrowser.Text = "No Browser";
            this.checkBoxNoBrowser.UseVisualStyleBackColor = true;
            // 
            // checkBoxReport
            // 
            this.checkBoxReport.AutoSize = true;
            this.checkBoxReport.Location = new System.Drawing.Point(6, 54);
            this.checkBoxReport.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxReport.Name = "checkBoxReport";
            this.checkBoxReport.Size = new System.Drawing.Size(109, 21);
            this.checkBoxReport.TabIndex = 10;
            this.checkBoxReport.Text = "Save Report";
            this.checkBoxReport.UseVisualStyleBackColor = true;
            // 
            // checkBoxQuiet
            // 
            this.checkBoxQuiet.AutoSize = true;
            this.checkBoxQuiet.Location = new System.Drawing.Point(6, 114);
            this.checkBoxQuiet.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxQuiet.Name = "checkBoxQuiet";
            this.checkBoxQuiet.Size = new System.Drawing.Size(103, 21);
            this.checkBoxQuiet.TabIndex = 11;
            this.checkBoxQuiet.Text = "Quiet Mode";
            this.checkBoxQuiet.UseVisualStyleBackColor = true;
            // 
            // checkBoxDatatableCSV
            // 
            this.checkBoxDatatableCSV.AutoSize = true;
            this.checkBoxDatatableCSV.Location = new System.Drawing.Point(6, 174);
            this.checkBoxDatatableCSV.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxDatatableCSV.Name = "checkBoxDatatableCSV";
            this.checkBoxDatatableCSV.Size = new System.Drawing.Size(121, 21);
            this.checkBoxDatatableCSV.TabIndex = 12;
            this.checkBoxDatatableCSV.Text = "Datatable File:";
            this.checkBoxDatatableCSV.UseVisualStyleBackColor = true;
            this.checkBoxDatatableCSV.CheckedChanged += new System.EventHandler(this.checkBoxDatatableCSV_CheckedChanged);
            // 
            // checkBoxInputs
            // 
            this.checkBoxInputs.AutoSize = true;
            this.checkBoxInputs.Location = new System.Drawing.Point(6, 368);
            this.checkBoxInputs.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxInputs.Name = "checkBoxInputs";
            this.checkBoxInputs.Size = new System.Drawing.Size(168, 21);
            this.checkBoxInputs.TabIndex = 13;
            this.checkBoxInputs.Text = "Workflow Parameters:";
            this.checkBoxInputs.UseVisualStyleBackColor = true;
            this.checkBoxInputs.CheckedChanged += new System.EventHandler(this.checkBoxInputs_CheckedChanged);
            // 
            // textBoxDatatableCSV
            // 
            this.textBoxDatatableCSV.Location = new System.Drawing.Point(31, 200);
            this.textBoxDatatableCSV.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxDatatableCSV.Name = "textBoxDatatableCSV";
            this.textBoxDatatableCSV.ReadOnly = true;
            this.textBoxDatatableCSV.Size = new System.Drawing.Size(155, 22);
            this.textBoxDatatableCSV.TabIndex = 16;
            // 
            // textBoxParam
            // 
            this.textBoxParam.Enabled = false;
            this.textBoxParam.Location = new System.Drawing.Point(31, 394);
            this.textBoxParam.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxParam.Name = "textBoxParam";
            this.textBoxParam.Size = new System.Drawing.Size(155, 22);
            this.textBoxParam.TabIndex = 20;
            // 
            // labelOutput
            // 
            this.labelOutput.AutoSize = true;
            this.labelOutput.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelOutput.Location = new System.Drawing.Point(3, 427);
            this.labelOutput.Name = "labelOutput";
            this.labelOutput.Size = new System.Drawing.Size(57, 17);
            this.labelOutput.TabIndex = 25;
            this.labelOutput.Text = "Output";
            // 
            // buttonBrowse
            // 
            this.buttonBrowse.Enabled = false;
            this.buttonBrowse.Location = new System.Drawing.Point(189, 200);
            this.buttonBrowse.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.buttonBrowse.Name = "buttonBrowse";
            this.buttonBrowse.Size = new System.Drawing.Size(64, 21);
            this.buttonBrowse.TabIndex = 26;
            this.buttonBrowse.Text = "Browse";
            this.buttonBrowse.UseVisualStyleBackColor = true;
            this.buttonBrowse.Click += new System.EventHandler(this.buttonBrowse_Click);
            // 
            // textBoxOutput
            // 
            this.textBoxOutput.Location = new System.Drawing.Point(6, 447);
            this.textBoxOutput.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxOutput.Multiline = true;
            this.textBoxOutput.Name = "textBoxOutput";
            this.textBoxOutput.ReadOnly = true;
            this.textBoxOutput.ScrollBars = System.Windows.Forms.ScrollBars.Both;
            this.textBoxOutput.Size = new System.Drawing.Size(246, 152);
            this.textBoxOutput.TabIndex = 28;
            // 
            // comboBoxDatatableWs
            // 
            this.comboBoxDatatableWs.BackColor = System.Drawing.SystemColors.Window;
            this.comboBoxDatatableWs.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.comboBoxDatatableWs.Enabled = false;
            this.comboBoxDatatableWs.FormattingEnabled = true;
            this.comboBoxDatatableWs.Location = new System.Drawing.Point(31, 226);
            this.comboBoxDatatableWs.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.comboBoxDatatableWs.Name = "comboBoxDatatableWs";
            this.comboBoxDatatableWs.Size = new System.Drawing.Size(155, 24);
            this.comboBoxDatatableWs.TabIndex = 29;
            this.comboBoxDatatableWs.Click += new System.EventHandler(this.comboBoxDatatableWs_Click);
            // 
            // comboBoxObjRepo
            // 
            this.comboBoxObjRepo.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.comboBoxObjRepo.Enabled = false;
            this.comboBoxObjRepo.FormattingEnabled = true;
            this.comboBoxObjRepo.Location = new System.Drawing.Point(31, 336);
            this.comboBoxObjRepo.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.comboBoxObjRepo.Name = "comboBoxObjRepo";
            this.comboBoxObjRepo.Size = new System.Drawing.Size(155, 24);
            this.comboBoxObjRepo.TabIndex = 35;
            this.comboBoxObjRepo.Click += new System.EventHandler(this.comboBoxObjRepo_Click);
            // 
            // buttonObjRepo
            // 
            this.buttonObjRepo.Enabled = false;
            this.buttonObjRepo.Location = new System.Drawing.Point(189, 310);
            this.buttonObjRepo.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.buttonObjRepo.Name = "buttonObjRepo";
            this.buttonObjRepo.Size = new System.Drawing.Size(64, 21);
            this.buttonObjRepo.TabIndex = 34;
            this.buttonObjRepo.Text = "Browse";
            this.buttonObjRepo.UseVisualStyleBackColor = true;
            this.buttonObjRepo.Click += new System.EventHandler(this.buttonObjRepo_Click);
            // 
            // textBoxObjRepo
            // 
            this.textBoxObjRepo.Location = new System.Drawing.Point(31, 310);
            this.textBoxObjRepo.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxObjRepo.Name = "textBoxObjRepo";
            this.textBoxObjRepo.ReadOnly = true;
            this.textBoxObjRepo.Size = new System.Drawing.Size(155, 22);
            this.textBoxObjRepo.TabIndex = 33;
            // 
            // checkBoxObjRepo
            // 
            this.checkBoxObjRepo.AutoSize = true;
            this.checkBoxObjRepo.Location = new System.Drawing.Point(6, 284);
            this.checkBoxObjRepo.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxObjRepo.Name = "checkBoxObjRepo";
            this.checkBoxObjRepo.Size = new System.Drawing.Size(147, 21);
            this.checkBoxObjRepo.TabIndex = 32;
            this.checkBoxObjRepo.Text = "Object Repository:";
            this.checkBoxObjRepo.UseVisualStyleBackColor = true;
            this.checkBoxObjRepo.CheckedChanged += new System.EventHandler(this.checkBoxObjRepo_CheckedChanged);
            // 
            // pictureBox1
            // 
            this.pictureBox1.BackgroundImage = ((System.Drawing.Image)(resources.GetObject("pictureBox1.BackgroundImage")));
            this.pictureBox1.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Stretch;
            this.pictureBox1.Location = new System.Drawing.Point(125, 178);
            this.pictureBox1.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.pictureBox1.Name = "pictureBox1";
            this.pictureBox1.Size = new System.Drawing.Size(13, 12);
            this.pictureBox1.TabIndex = 37;
            this.pictureBox1.TabStop = false;
            // 
            // pictureBox2
            // 
            this.pictureBox2.BackgroundImage = ((System.Drawing.Image)(resources.GetObject("pictureBox2.BackgroundImage")));
            this.pictureBox2.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Stretch;
            this.pictureBox2.Location = new System.Drawing.Point(149, 288);
            this.pictureBox2.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.pictureBox2.Name = "pictureBox2";
            this.pictureBox2.Size = new System.Drawing.Size(13, 12);
            this.pictureBox2.TabIndex = 38;
            this.pictureBox2.TabStop = false;
            // 
            // textBoxRange
            // 
            this.textBoxRange.Enabled = false;
            this.textBoxRange.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Regular, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.textBoxRange.ForeColor = System.Drawing.SystemColors.Desktop;
            this.textBoxRange.Location = new System.Drawing.Point(31, 254);
            this.textBoxRange.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxRange.Name = "textBoxRange";
            this.textBoxRange.Size = new System.Drawing.Size(155, 23);
            this.textBoxRange.TabIndex = 39;
            this.textBoxRange.Text = "Optional range";
            this.textBoxRange.Enter += new System.EventHandler(this.textBoxRange_Enter);
            // 
            // checkBoxTurbo
            // 
            this.checkBoxTurbo.AutoSize = true;
            this.checkBoxTurbo.Location = new System.Drawing.Point(6, 84);
            this.checkBoxTurbo.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxTurbo.Name = "checkBoxTurbo";
            this.checkBoxTurbo.Size = new System.Drawing.Size(107, 21);
            this.checkBoxTurbo.TabIndex = 40;
            this.checkBoxTurbo.Text = "Turbo Mode";
            this.checkBoxTurbo.UseVisualStyleBackColor = true;
            // 
            // checkBoxMSEdge
            // 
            this.checkBoxMSEdge.AutoSize = true;
            this.checkBoxMSEdge.Location = new System.Drawing.Point(6, 144);
            this.checkBoxMSEdge.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxMSEdge.Name = "checkBoxMSEdge";
            this.checkBoxMSEdge.Size = new System.Drawing.Size(142, 21);
            this.checkBoxMSEdge.TabIndex = 41;
            this.checkBoxMSEdge.Text = "MS Edge Browser";
            this.checkBoxMSEdge.UseVisualStyleBackColor = true;
            // 
            // TagUITaskPane
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.AutoSize = true;
            this.AutoSizeMode = System.Windows.Forms.AutoSizeMode.GrowAndShrink;
            this.BackColor = System.Drawing.SystemColors.ControlLightLight;
            this.Controls.Add(this.checkBoxMSEdge);
            this.Controls.Add(this.checkBoxTurbo);
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
            this.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.Name = "TagUITaskPane";
            this.Size = new System.Drawing.Size(256, 601);
            this.SizeChanged += new System.EventHandler(this.TagUITaskPane_SizeChanged);
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
        private System.Windows.Forms.CheckBox checkBoxTurbo;
        private System.Windows.Forms.CheckBox checkBoxMSEdge;
    }
}
