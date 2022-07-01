
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
            this.checkBoxNoBrowser = new System.Windows.Forms.CheckBox();
            this.checkBoxReport = new System.Windows.Forms.CheckBox();
            this.checkBoxQuiet = new System.Windows.Forms.CheckBox();
            this.checkBoxInputs = new System.Windows.Forms.CheckBox();
            this.textBoxParam = new System.Windows.Forms.TextBox();
            this.labelFlow = new System.Windows.Forms.Label();
            this.checkBoxDatatable = new System.Windows.Forms.CheckBox();
            this.comboBoxAllSheets = new System.Windows.Forms.ComboBox();
            this.labelFileTypes = new System.Windows.Forms.Label();
            this.comboBoxObjectRepository = new System.Windows.Forms.ComboBox();
            this.checkBoxObjectRepository = new System.Windows.Forms.CheckBox();
            this.openFileDialog1 = new System.Windows.Forms.OpenFileDialog();
            this.buttonSelect = new System.Windows.Forms.Button();
            this.labelOutput = new System.Windows.Forms.Label();
            this.textBoxOutput = new System.Windows.Forms.TextBox();
            this.checkBoxCsvOutput = new System.Windows.Forms.CheckBox();
            this.textBoxRange = new System.Windows.Forms.TextBox();
            this.pictureBox1 = new System.Windows.Forms.PictureBox();
            this.pictureBox2 = new System.Windows.Forms.PictureBox();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).BeginInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).BeginInit();
            this.SuspendLayout();
            // 
            // textBoxFlowFile
            // 
            this.textBoxFlowFile.Location = new System.Drawing.Point(9, 26);
            this.textBoxFlowFile.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxFlowFile.Name = "textBoxFlowFile";
            this.textBoxFlowFile.ReadOnly = true;
            this.textBoxFlowFile.Size = new System.Drawing.Size(246, 22);
            this.textBoxFlowFile.TabIndex = 0;
            this.textBoxFlowFile.TextChanged += new System.EventHandler(this.textBoxFlowFile_TextChanged);
            // 
            // buttonEditFlow
            // 
            this.buttonEditFlow.BackColor = System.Drawing.SystemColors.Control;
            this.buttonEditFlow.Enabled = false;
            this.buttonEditFlow.Location = new System.Drawing.Point(97, 52);
            this.buttonEditFlow.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.buttonEditFlow.Name = "buttonEditFlow";
            this.buttonEditFlow.Size = new System.Drawing.Size(82, 26);
            this.buttonEditFlow.TabIndex = 1;
            this.buttonEditFlow.Text = "Edit Flow";
            this.buttonEditFlow.UseVisualStyleBackColor = false;
            this.buttonEditFlow.Click += new System.EventHandler(this.buttonEditFlow_Click);
            // 
            // labelRunOptions
            // 
            this.labelRunOptions.AutoSize = true;
            this.labelRunOptions.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelRunOptions.Location = new System.Drawing.Point(5, 90);
            this.labelRunOptions.Name = "labelRunOptions";
            this.labelRunOptions.Size = new System.Drawing.Size(98, 17);
            this.labelRunOptions.TabIndex = 2;
            this.labelRunOptions.Text = "Run Options";
            // 
            // checkBoxNoBrowser
            // 
            this.checkBoxNoBrowser.AutoSize = true;
            this.checkBoxNoBrowser.Location = new System.Drawing.Point(10, 111);
            this.checkBoxNoBrowser.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxNoBrowser.Name = "checkBoxNoBrowser";
            this.checkBoxNoBrowser.Size = new System.Drawing.Size(103, 21);
            this.checkBoxNoBrowser.TabIndex = 4;
            this.checkBoxNoBrowser.Text = "No Browser";
            this.checkBoxNoBrowser.UseVisualStyleBackColor = true;
            // 
            // checkBoxReport
            // 
            this.checkBoxReport.AutoSize = true;
            this.checkBoxReport.Location = new System.Drawing.Point(10, 144);
            this.checkBoxReport.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxReport.Name = "checkBoxReport";
            this.checkBoxReport.Size = new System.Drawing.Size(109, 21);
            this.checkBoxReport.TabIndex = 5;
            this.checkBoxReport.Text = "Save Report";
            this.checkBoxReport.UseVisualStyleBackColor = true;
            // 
            // checkBoxQuiet
            // 
            this.checkBoxQuiet.AutoSize = true;
            this.checkBoxQuiet.Location = new System.Drawing.Point(10, 178);
            this.checkBoxQuiet.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxQuiet.Name = "checkBoxQuiet";
            this.checkBoxQuiet.Size = new System.Drawing.Size(103, 21);
            this.checkBoxQuiet.TabIndex = 6;
            this.checkBoxQuiet.Text = "Quiet Mode";
            this.checkBoxQuiet.UseVisualStyleBackColor = true;
            // 
            // checkBoxInputs
            // 
            this.checkBoxInputs.AutoSize = true;
            this.checkBoxInputs.Location = new System.Drawing.Point(10, 356);
            this.checkBoxInputs.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxInputs.Name = "checkBoxInputs";
            this.checkBoxInputs.Size = new System.Drawing.Size(163, 21);
            this.checkBoxInputs.TabIndex = 7;
            this.checkBoxInputs.Text = "Parameters p1 to p8:";
            this.checkBoxInputs.UseVisualStyleBackColor = true;
            this.checkBoxInputs.CheckedChanged += new System.EventHandler(this.checkBoxInputs_CheckedChanged);
            // 
            // textBoxParam
            // 
            this.textBoxParam.Enabled = false;
            this.textBoxParam.Location = new System.Drawing.Point(34, 379);
            this.textBoxParam.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxParam.Name = "textBoxParam";
            this.textBoxParam.Size = new System.Drawing.Size(221, 22);
            this.textBoxParam.TabIndex = 8;
            // 
            // labelFlow
            // 
            this.labelFlow.AutoSize = true;
            this.labelFlow.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelFlow.Location = new System.Drawing.Point(5, 6);
            this.labelFlow.Name = "labelFlow";
            this.labelFlow.Size = new System.Drawing.Size(71, 17);
            this.labelFlow.TabIndex = 9;
            this.labelFlow.Text = "Flow File";
            // 
            // checkBoxDatatable
            // 
            this.checkBoxDatatable.AutoSize = true;
            this.checkBoxDatatable.Location = new System.Drawing.Point(10, 211);
            this.checkBoxDatatable.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxDatatable.Name = "checkBoxDatatable";
            this.checkBoxDatatable.Size = new System.Drawing.Size(136, 21);
            this.checkBoxDatatable.TabIndex = 12;
            this.checkBoxDatatable.Text = "Datatable Sheet:";
            this.checkBoxDatatable.UseVisualStyleBackColor = true;
            this.checkBoxDatatable.CheckedChanged += new System.EventHandler(this.checkBoxDatatable_CheckedChanged);
            // 
            // comboBoxAllSheets
            // 
            this.comboBoxAllSheets.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.comboBoxAllSheets.Enabled = false;
            this.comboBoxAllSheets.FormattingEnabled = true;
            this.comboBoxAllSheets.Location = new System.Drawing.Point(34, 232);
            this.comboBoxAllSheets.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.comboBoxAllSheets.Name = "comboBoxAllSheets";
            this.comboBoxAllSheets.Size = new System.Drawing.Size(221, 24);
            this.comboBoxAllSheets.TabIndex = 13;
            this.comboBoxAllSheets.SelectedIndexChanged += new System.EventHandler(this.comboBoxAllSheets_SelectedIndexChanged);
            this.comboBoxAllSheets.Click += new System.EventHandler(this.comboBoxAllSheets_Click);
            // 
            // labelFileTypes
            // 
            this.labelFileTypes.AutoSize = true;
            this.labelFileTypes.Location = new System.Drawing.Point(74, 5);
            this.labelFileTypes.Name = "labelFileTypes";
            this.labelFileTypes.Size = new System.Drawing.Size(96, 17);
            this.labelFileTypes.TabIndex = 14;
            this.labelFileTypes.Text = "(.docx or .tag)";
            // 
            // comboBoxObjectRepository
            // 
            this.comboBoxObjectRepository.DropDownStyle = System.Windows.Forms.ComboBoxStyle.DropDownList;
            this.comboBoxObjectRepository.Enabled = false;
            this.comboBoxObjectRepository.FormattingEnabled = true;
            this.comboBoxObjectRepository.Location = new System.Drawing.Point(34, 322);
            this.comboBoxObjectRepository.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.comboBoxObjectRepository.Name = "comboBoxObjectRepository";
            this.comboBoxObjectRepository.Size = new System.Drawing.Size(221, 24);
            this.comboBoxObjectRepository.TabIndex = 16;
            this.comboBoxObjectRepository.Click += new System.EventHandler(this.comboBoxObjectRepository_Click);
            // 
            // checkBoxObjectRepository
            // 
            this.checkBoxObjectRepository.AutoSize = true;
            this.checkBoxObjectRepository.Location = new System.Drawing.Point(10, 299);
            this.checkBoxObjectRepository.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxObjectRepository.Name = "checkBoxObjectRepository";
            this.checkBoxObjectRepository.Size = new System.Drawing.Size(188, 21);
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
            this.buttonSelect.Location = new System.Drawing.Point(9, 52);
            this.buttonSelect.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.buttonSelect.Name = "buttonSelect";
            this.buttonSelect.Size = new System.Drawing.Size(82, 26);
            this.buttonSelect.TabIndex = 18;
            this.buttonSelect.Text = "Browse";
            this.buttonSelect.UseVisualStyleBackColor = false;
            this.buttonSelect.Click += new System.EventHandler(this.buttonSelect_Click);
            // 
            // labelOutput
            // 
            this.labelOutput.AutoSize = true;
            this.labelOutput.Font = new System.Drawing.Font("Microsoft Sans Serif", 8F, System.Drawing.FontStyle.Bold, System.Drawing.GraphicsUnit.Point, ((byte)(0)));
            this.labelOutput.Location = new System.Drawing.Point(5, 446);
            this.labelOutput.Name = "labelOutput";
            this.labelOutput.Size = new System.Drawing.Size(57, 17);
            this.labelOutput.TabIndex = 20;
            this.labelOutput.Text = "Output";
            // 
            // textBoxOutput
            // 
            this.textBoxOutput.Location = new System.Drawing.Point(9, 465);
            this.textBoxOutput.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxOutput.Multiline = true;
            this.textBoxOutput.Name = "textBoxOutput";
            this.textBoxOutput.ReadOnly = true;
            this.textBoxOutput.Size = new System.Drawing.Size(246, 72);
            this.textBoxOutput.TabIndex = 23;
            // 
            // checkBoxCsvOutput
            // 
            this.checkBoxCsvOutput.AutoSize = true;
            this.checkBoxCsvOutput.Location = new System.Drawing.Point(9, 410);
            this.checkBoxCsvOutput.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.checkBoxCsvOutput.Name = "checkBoxCsvOutput";
            this.checkBoxCsvOutput.Size = new System.Drawing.Size(222, 21);
            this.checkBoxCsvOutput.TabIndex = 24;
            this.checkBoxCsvOutput.Text = "Write CSV output to new sheet";
            this.checkBoxCsvOutput.UseVisualStyleBackColor = true;
            // 
            // textBoxRange
            // 
            this.textBoxRange.Enabled = false;
            this.textBoxRange.Location = new System.Drawing.Point(34, 264);
            this.textBoxRange.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.textBoxRange.Name = "textBoxRange";
            this.textBoxRange.Size = new System.Drawing.Size(221, 22);
            this.textBoxRange.TabIndex = 25;
            this.textBoxRange.Text = "Optional range";
            this.textBoxRange.Click += new System.EventHandler(this.textBoxRange_Click);
            // 
            // pictureBox1
            // 
            this.pictureBox1.BackgroundImage = global::TagUIExcelAddIn.Properties.Resources.Question;
            this.pictureBox1.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Stretch;
            this.pictureBox1.Location = new System.Drawing.Point(139, 215);
            this.pictureBox1.Name = "pictureBox1";
            this.pictureBox1.Size = new System.Drawing.Size(13, 12);
            this.pictureBox1.TabIndex = 26;
            this.pictureBox1.TabStop = false;
            // 
            // pictureBox2
            // 
            this.pictureBox2.BackgroundImage = global::TagUIExcelAddIn.Properties.Resources.Question;
            this.pictureBox2.BackgroundImageLayout = System.Windows.Forms.ImageLayout.Stretch;
            this.pictureBox2.Location = new System.Drawing.Point(191, 303);
            this.pictureBox2.Name = "pictureBox2";
            this.pictureBox2.Size = new System.Drawing.Size(13, 12);
            this.pictureBox2.TabIndex = 27;
            this.pictureBox2.TabStop = false;
            // 
            // TagUIExcelAddInTaskPane
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(8F, 16F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.AutoSize = true;
            this.AutoSizeMode = System.Windows.Forms.AutoSizeMode.GrowAndShrink;
            this.BackColor = System.Drawing.SystemColors.Control;
            this.Controls.Add(this.pictureBox2);
            this.Controls.Add(this.pictureBox1);
            this.Controls.Add(this.textBoxRange);
            this.Controls.Add(this.checkBoxCsvOutput);
            this.Controls.Add(this.textBoxOutput);
            this.Controls.Add(this.labelOutput);
            this.Controls.Add(this.labelFlow);
            this.Controls.Add(this.buttonSelect);
            this.Controls.Add(this.checkBoxObjectRepository);
            this.Controls.Add(this.comboBoxObjectRepository);
            this.Controls.Add(this.labelFileTypes);
            this.Controls.Add(this.comboBoxAllSheets);
            this.Controls.Add(this.checkBoxDatatable);
            this.Controls.Add(this.textBoxParam);
            this.Controls.Add(this.checkBoxInputs);
            this.Controls.Add(this.checkBoxQuiet);
            this.Controls.Add(this.checkBoxReport);
            this.Controls.Add(this.checkBoxNoBrowser);
            this.Controls.Add(this.labelRunOptions);
            this.Controls.Add(this.buttonEditFlow);
            this.Controls.Add(this.textBoxFlowFile);
            this.Margin = new System.Windows.Forms.Padding(3, 2, 3, 2);
            this.Name = "TagUIExcelAddInTaskPane";
            this.Size = new System.Drawing.Size(258, 539);
            this.SizeChanged += new System.EventHandler(this.TagUIExcelAddInTaskPane_SizeChanged);
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox1)).EndInit();
            ((System.ComponentModel.ISupportInitialize)(this.pictureBox2)).EndInit();
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.TextBox textBoxFlowFile;
        private System.Windows.Forms.Button buttonEditFlow;
        private System.Windows.Forms.Label labelRunOptions;
        private System.Windows.Forms.CheckBox checkBoxNoBrowser;
        private System.Windows.Forms.CheckBox checkBoxReport;
        private System.Windows.Forms.CheckBox checkBoxQuiet;
        private System.Windows.Forms.CheckBox checkBoxInputs;
        private System.Windows.Forms.TextBox textBoxParam;
        private System.Windows.Forms.Label labelFlow;
        private System.Windows.Forms.CheckBox checkBoxDatatable;
        private System.Windows.Forms.ComboBox comboBoxAllSheets;
        private System.Windows.Forms.Label labelFileTypes;
        private System.Windows.Forms.ComboBox comboBoxObjectRepository;
        private System.Windows.Forms.CheckBox checkBoxObjectRepository;
        private System.Windows.Forms.OpenFileDialog openFileDialog1;
        private System.Windows.Forms.Button buttonSelect;
        private System.Windows.Forms.Label labelOutput;
        private System.Windows.Forms.TextBox textBoxOutput;
        private System.Windows.Forms.CheckBox checkBoxCsvOutput;
        private System.Windows.Forms.TextBox textBoxRange;
        private System.Windows.Forms.PictureBox pictureBox1;
        private System.Windows.Forms.PictureBox pictureBox2;
    }
}
