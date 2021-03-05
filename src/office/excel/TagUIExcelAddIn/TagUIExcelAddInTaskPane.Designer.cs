
namespace TagUIExcelAddIn
{
    partial class TagUIExcelAddInTaskPane
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
            this.textBoxFlowFile = new System.Windows.Forms.TextBox();
            this.buttonEditFlow = new System.Windows.Forms.Button();
            this.labelRunOptions = new System.Windows.Forms.Label();
            this.labelDocumentation = new System.Windows.Forms.Label();
            this.checkBoxNoBrowser = new System.Windows.Forms.CheckBox();
            this.checkBoxReport = new System.Windows.Forms.CheckBox();
            this.checkBoxQuiet = new System.Windows.Forms.CheckBox();
            this.checkBoxInputs = new System.Windows.Forms.CheckBox();
            this.textBoxParam = new System.Windows.Forms.TextBox();
            this.labelFlow = new System.Windows.Forms.Label();
            this.buttonRun = new System.Windows.Forms.Button();
            this.buttonDeploy = new System.Windows.Forms.Button();
            this.checkBoxDatatable = new System.Windows.Forms.CheckBox();
            this.comboBoxAllSheets = new System.Windows.Forms.ComboBox();
            this.labelFileTypes = new System.Windows.Forms.Label();
            this.comboBoxObjectRepository = new System.Windows.Forms.ComboBox();
            this.checkBoxObjectRepository = new System.Windows.Forms.CheckBox();
            this.openFileDialog1 = new System.Windows.Forms.OpenFileDialog();
            this.buttonSelect = new System.Windows.Forms.Button();
            this.SuspendLayout();
            // 
            // textBoxFlowFile
            // 
            this.textBoxFlowFile.Enabled = false;
            this.textBoxFlowFile.Location = new System.Drawing.Point(10, 50);
            this.textBoxFlowFile.Name = "textBoxFlowFile";
            this.textBoxFlowFile.Size = new System.Drawing.Size(202, 26);
            this.textBoxFlowFile.TabIndex = 0;
            // 
            // buttonEditFlow
            // 
            this.buttonEditFlow.BackColor = System.Drawing.SystemColors.Control;
            this.buttonEditFlow.Location = new System.Drawing.Point(10, 93);
            this.buttonEditFlow.Name = "buttonEditFlow";
            this.buttonEditFlow.Size = new System.Drawing.Size(92, 42);
            this.buttonEditFlow.TabIndex = 1;
            this.buttonEditFlow.Text = "Edit Flow";
            this.buttonEditFlow.UseVisualStyleBackColor = false;
            this.buttonEditFlow.Click += new System.EventHandler(this.buttonEditFlow_Click);
            // 
            // labelRunOptions
            // 
            this.labelRunOptions.AutoSize = true;
            this.labelRunOptions.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelRunOptions.Location = new System.Drawing.Point(6, 171);
            this.labelRunOptions.Name = "labelRunOptions";
            this.labelRunOptions.Size = new System.Drawing.Size(109, 20);
            this.labelRunOptions.TabIndex = 2;
            this.labelRunOptions.Text = "Run Options";
            // 
            // labelDocumentation
            // 
            this.labelDocumentation.AutoSize = true;
            this.labelDocumentation.ForeColor = System.Drawing.SystemColors.MenuHighlight;
            this.labelDocumentation.Location = new System.Drawing.Point(7, 590);
            this.labelDocumentation.Name = "labelDocumentation";
            this.labelDocumentation.Size = new System.Drawing.Size(79, 20);
            this.labelDocumentation.TabIndex = 3;
            this.labelDocumentation.Text = "See Docs";
            this.labelDocumentation.Click += new System.EventHandler(this.labelDocumentation_Click);
            // 
            // checkBoxNoBrowser
            // 
            this.checkBoxNoBrowser.AutoSize = true;
            this.checkBoxNoBrowser.Location = new System.Drawing.Point(11, 205);
            this.checkBoxNoBrowser.Name = "checkBoxNoBrowser";
            this.checkBoxNoBrowser.Size = new System.Drawing.Size(117, 24);
            this.checkBoxNoBrowser.TabIndex = 4;
            this.checkBoxNoBrowser.Text = "No Browser";
            this.checkBoxNoBrowser.UseVisualStyleBackColor = true;
            // 
            // checkBoxReport
            // 
            this.checkBoxReport.AutoSize = true;
            this.checkBoxReport.Location = new System.Drawing.Point(11, 246);
            this.checkBoxReport.Name = "checkBoxReport";
            this.checkBoxReport.Size = new System.Drawing.Size(124, 24);
            this.checkBoxReport.TabIndex = 5;
            this.checkBoxReport.Text = "Save Report";
            this.checkBoxReport.UseVisualStyleBackColor = true;
            // 
            // checkBoxQuiet
            // 
            this.checkBoxQuiet.AutoSize = true;
            this.checkBoxQuiet.Location = new System.Drawing.Point(11, 287);
            this.checkBoxQuiet.Name = "checkBoxQuiet";
            this.checkBoxQuiet.Size = new System.Drawing.Size(117, 24);
            this.checkBoxQuiet.TabIndex = 6;
            this.checkBoxQuiet.Text = "Quiet Mode";
            this.checkBoxQuiet.UseVisualStyleBackColor = true;
            // 
            // checkBoxInputs
            // 
            this.checkBoxInputs.AutoSize = true;
            this.checkBoxInputs.Location = new System.Drawing.Point(11, 451);
            this.checkBoxInputs.Name = "checkBoxInputs";
            this.checkBoxInputs.Size = new System.Drawing.Size(183, 24);
            this.checkBoxInputs.TabIndex = 7;
            this.checkBoxInputs.Text = "Parameters p1 to p8:";
            this.checkBoxInputs.UseVisualStyleBackColor = true;
            this.checkBoxInputs.CheckedChanged += new System.EventHandler(this.checkBoxInputs_CheckedChanged);
            // 
            // textBoxParam
            // 
            this.textBoxParam.Enabled = false;
            this.textBoxParam.Location = new System.Drawing.Point(38, 479);
            this.textBoxParam.Name = "textBoxParam";
            this.textBoxParam.Size = new System.Drawing.Size(174, 26);
            this.textBoxParam.TabIndex = 8;
            // 
            // labelFlow
            // 
            this.labelFlow.AutoSize = true;
            this.labelFlow.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelFlow.Location = new System.Drawing.Point(6, 16);
            this.labelFlow.Name = "labelFlow";
            this.labelFlow.Size = new System.Drawing.Size(80, 20);
            this.labelFlow.TabIndex = 9;
            this.labelFlow.Text = "Flow File";
            // 
            // buttonRun
            // 
            this.buttonRun.BackColor = System.Drawing.SystemColors.Control;
            this.buttonRun.Location = new System.Drawing.Point(11, 526);
            this.buttonRun.Name = "buttonRun";
            this.buttonRun.Size = new System.Drawing.Size(92, 42);
            this.buttonRun.TabIndex = 10;
            this.buttonRun.Text = "Run";
            this.buttonRun.UseVisualStyleBackColor = false;
            this.buttonRun.Click += new System.EventHandler(this.buttonRun_Click);
            // 
            // buttonDeploy
            // 
            this.buttonDeploy.BackColor = System.Drawing.SystemColors.Control;
            this.buttonDeploy.Location = new System.Drawing.Point(120, 526);
            this.buttonDeploy.Name = "buttonDeploy";
            this.buttonDeploy.Size = new System.Drawing.Size(92, 42);
            this.buttonDeploy.TabIndex = 11;
            this.buttonDeploy.Text = "Deploy";
            this.buttonDeploy.UseVisualStyleBackColor = false;
            this.buttonDeploy.Click += new System.EventHandler(this.buttonDeploy_Click);
            // 
            // checkBoxDatatable
            // 
            this.checkBoxDatatable.AutoSize = true;
            this.checkBoxDatatable.Location = new System.Drawing.Point(11, 328);
            this.checkBoxDatatable.Name = "checkBoxDatatable";
            this.checkBoxDatatable.Size = new System.Drawing.Size(156, 24);
            this.checkBoxDatatable.TabIndex = 12;
            this.checkBoxDatatable.Text = "Datatable Sheet:";
            this.checkBoxDatatable.UseVisualStyleBackColor = true;
            this.checkBoxDatatable.CheckedChanged += new System.EventHandler(this.checkBoxDatatable_CheckedChanged);
            // 
            // comboBoxAllSheets
            // 
            this.comboBoxAllSheets.Enabled = false;
            this.comboBoxAllSheets.FormattingEnabled = true;
            this.comboBoxAllSheets.Location = new System.Drawing.Point(38, 355);
            this.comboBoxAllSheets.Name = "comboBoxAllSheets";
            this.comboBoxAllSheets.Size = new System.Drawing.Size(174, 28);
            this.comboBoxAllSheets.TabIndex = 13;
            this.comboBoxAllSheets.Click += new System.EventHandler(this.comboBoxAllSheets_Click);
            // 
            // labelFileTypes
            // 
            this.labelFileTypes.AutoSize = true;
            this.labelFileTypes.Location = new System.Drawing.Point(83, 15);
            this.labelFileTypes.Name = "labelFileTypes";
            this.labelFileTypes.Size = new System.Drawing.Size(105, 20);
            this.labelFileTypes.TabIndex = 14;
            this.labelFileTypes.Text = "(.docx or .tag)";
            // 
            // comboBoxObjectRepository
            // 
            this.comboBoxObjectRepository.Enabled = false;
            this.comboBoxObjectRepository.FormattingEnabled = true;
            this.comboBoxObjectRepository.Location = new System.Drawing.Point(38, 417);
            this.comboBoxObjectRepository.Name = "comboBoxObjectRepository";
            this.comboBoxObjectRepository.Size = new System.Drawing.Size(174, 28);
            this.comboBoxObjectRepository.TabIndex = 16;
            this.comboBoxObjectRepository.Click += new System.EventHandler(this.comboBoxObjectRepository_Click);
            // 
            // checkBoxObjectRepository
            // 
            this.checkBoxObjectRepository.AutoSize = true;
            this.checkBoxObjectRepository.Location = new System.Drawing.Point(11, 389);
            this.checkBoxObjectRepository.Name = "checkBoxObjectRepository";
            this.checkBoxObjectRepository.Size = new System.Drawing.Size(212, 24);
            this.checkBoxObjectRepository.TabIndex = 17;
            this.checkBoxObjectRepository.Text = "Object Repository Sheet:";
            this.checkBoxObjectRepository.UseVisualStyleBackColor = true;
            this.checkBoxObjectRepository.CheckedChanged += new System.EventHandler(this.checkBoxObjectRepository_CheckedChanged);
            // 
            // openFileDialog1
            // 
            this.openFileDialog1.FileName = "openFileDialog1";
            // 
            // buttonSelect
            // 
            this.buttonSelect.BackColor = System.Drawing.SystemColors.Control;
            this.buttonSelect.Location = new System.Drawing.Point(216, 50);
            this.buttonSelect.Name = "buttonSelect";
            this.buttonSelect.Size = new System.Drawing.Size(75, 26);
            this.buttonSelect.TabIndex = 18;
            this.buttonSelect.Text = "Browse";
            this.buttonSelect.UseVisualStyleBackColor = false;
            this.buttonSelect.Click += new System.EventHandler(this.buttonSelect_Click);
            // 
            // TagUIExcelAddInTaskPane
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(9F, 20F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.BackColor = System.Drawing.SystemColors.Control;
            this.Controls.Add(this.labelFlow);
            this.Controls.Add(this.buttonSelect);
            this.Controls.Add(this.checkBoxObjectRepository);
            this.Controls.Add(this.comboBoxObjectRepository);
            this.Controls.Add(this.labelFileTypes);
            this.Controls.Add(this.comboBoxAllSheets);
            this.Controls.Add(this.checkBoxDatatable);
            this.Controls.Add(this.buttonDeploy);
            this.Controls.Add(this.buttonRun);
            this.Controls.Add(this.textBoxParam);
            this.Controls.Add(this.checkBoxInputs);
            this.Controls.Add(this.checkBoxQuiet);
            this.Controls.Add(this.checkBoxReport);
            this.Controls.Add(this.checkBoxNoBrowser);
            this.Controls.Add(this.labelDocumentation);
            this.Controls.Add(this.labelRunOptions);
            this.Controls.Add(this.buttonEditFlow);
            this.Controls.Add(this.textBoxFlowFile);
            this.Name = "TagUIExcelAddInTaskPane";
            this.Size = new System.Drawing.Size(332, 716);
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.TextBox textBoxFlowFile;
        private System.Windows.Forms.Button buttonEditFlow;
        private System.Windows.Forms.Label labelRunOptions;
        private System.Windows.Forms.Label labelDocumentation;
        private System.Windows.Forms.CheckBox checkBoxNoBrowser;
        private System.Windows.Forms.CheckBox checkBoxReport;
        private System.Windows.Forms.CheckBox checkBoxQuiet;
        private System.Windows.Forms.CheckBox checkBoxInputs;
        private System.Windows.Forms.TextBox textBoxParam;
        private System.Windows.Forms.Label labelFlow;
        private System.Windows.Forms.Button buttonRun;
        private System.Windows.Forms.Button buttonDeploy;
        private System.Windows.Forms.CheckBox checkBoxDatatable;
        private System.Windows.Forms.ComboBox comboBoxAllSheets;
        private System.Windows.Forms.Label labelFileTypes;
        private System.Windows.Forms.ComboBox comboBoxObjectRepository;
        private System.Windows.Forms.CheckBox checkBoxObjectRepository;
        private System.Windows.Forms.OpenFileDialog openFileDialog1;
        private System.Windows.Forms.Button buttonSelect;
    }
}
