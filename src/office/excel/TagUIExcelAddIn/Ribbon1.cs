using Microsoft.Office.Tools.Ribbon;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using Excel = Microsoft.Office.Interop.Excel;
using Microsoft.Office.Tools.Excel;
using Microsoft.Office.Interop.Excel;
using System.Diagnostics;
using System.Windows.Forms;
using System.IO;
using TextBox = System.Windows.Forms.TextBox;
using DocumentFormat.OpenXml.Packaging;
using DocumentFormat.OpenXml.Wordprocessing;
using CheckBox = System.Windows.Forms.CheckBox;

namespace TagUIExcelAddIn
{
    public partial class Ribbon1
    {
        private void Ribbon1_Load(object sender, RibbonUIEventArgs e)
        {

        }

        private void toggleButton1_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.TaskPane.Visible = ((RibbonToggleButton)sender).Checked;
        }

        private void buttonRun_Click(object sender, RibbonControlEventArgs e)
        {
            RunFlow(false);
        }
        private void ImportCSV(string csvFilePath)
        {

            Excel.Application xlApp = Globals.ThisAddIn.Application;
            var activeWkbName = xlApp.ActiveWorkbook.Name;
            var templateWorkbook = xlApp.Workbooks.Open(csvFilePath);

            var from = (templateWorkbook.Sheets[1] as Excel.Worksheet);

            xlApp.Workbooks[activeWkbName].Activate();
            from.Copy(Type.Missing, xlApp.Worksheets[xlApp.ActiveWorkbook.Sheets.Count]);
            templateWorkbook.Close();
        }
        private void buttonDeploy_Click(object sender, RibbonControlEventArgs e)
        {
            string fileLoc = Globals.ThisAddIn.Application.ActiveWorkbook.FullName;
            if (fileLoc.Contains(@"\"))
            {
                TextBox textBoxFlowFile = Globals.ThisAddIn.TaskPane.Control.Controls["textBoxFlowFile"] as TextBox;
                string tagFilePath = textBoxFlowFile.Text;
                RunFlow(true);
            }
            else
            {
                MessageBox.Show("Workbook must be saved before deploying", "Oops!");
                try
                {
                    Globals.ThisAddIn.Application.ActiveWorkbook.Save();
                }
                catch { }
            }
        }
        private void PivotData()
        {
            Excel.Worksheet osheet = Globals.ThisAddIn.Application.ActiveWorkbook.ActiveSheet;
            Excel.Range oRange = (Excel.Range)osheet.UsedRange;
            var pch = Globals.ThisAddIn.Application.ActiveWorkbook.PivotCaches();
            Excel.Range pivotData = (Excel.Range)osheet.UsedRange;
            Excel.PivotCache pc = pch.Create(Excel.XlPivotTableSourceType.xlDatabase, pivotData);
            Excel.PivotTable pvt = pc.CreatePivotTable(osheet.Range["I1"], "MyPivotTable");
            Excel.PivotField pageField = (Excel.PivotField)pvt.PivotFields("WORKFLOW");
            pageField.Orientation = Excel.XlPivotFieldOrientation.xlPageField;
            Excel.PivotField rowField = (Excel.PivotField)pvt.PivotFields("Status");
            rowField.Orientation = Excel.XlPivotFieldOrientation.xlRowField;
            Excel.PivotField valueField = (Excel.PivotField)pvt.PivotFields("Status");
            valueField.Orientation = Excel.XlPivotFieldOrientation.xlDataField;


            Excel.SlicerCaches slicerCaches = null;
            Excel.SlicerCache monthSlicerCache = null;
            Excel.Slicers monthSlicers = null;
            Excel.Slicer monthSlicer = null;
            slicerCaches = Globals.ThisAddIn.Application.ActiveWorkbook.SlicerCaches;
            // Month Slicer
            monthSlicerCache = slicerCaches.Add2(pvt, "DATETIME", "DATETIME", Excel.XlSlicerCacheType.xlTimeline);
            monthSlicers = monthSlicerCache.Slicers;
            monthSlicer = monthSlicers.Add(osheet, Type.Missing,
                "Month", "Month", 160, 10, 250, 150);
        }
        private void buttonUserGuide_Click(object sender, RibbonControlEventArgs e)
        {
            Process.Start("https://tagui.readthedocs.io/en/latest/index.html");
        }

        private void buttonViewReport_Click(object sender, RibbonControlEventArgs e)
        {
            string allPaths = Environment.GetEnvironmentVariable("Path").ToLower() + ";" + Environment.GetEnvironmentVariable("Path", EnvironmentVariableTarget.User).ToLower();
            string[] arr = allPaths.Split(';');
            string taguiFolderPath = arr.Where(s => s.Contains("tagui")).FirstOrDefault();
            string taguiReportCsvPath = taguiFolderPath + @"\tagui_report.csv";
            StreamReader reader;
            try
            {
                reader = new StreamReader(File.OpenRead(taguiReportCsvPath));
            } catch
            {
                MessageBox.Show("TagUI report file in use, unable to generate report.");
                return;
            }
            string data = "";
            while (!reader.EndOfStream)
            {
                var line = reader.ReadLine();
                string[] lineArr = line.Split(',');

                if (!lineArr[0].Contains("#"))
                {

                    data = data + lineArr[0] + "," + lineArr[1] + ",";
                    string[] dateArr = lineArr[2].Split(' ');
                    if (!dateArr[0].Contains("NOT"))
                    {
                        data = data + dateArr[2] + " " + dateArr[1] + " " + dateArr[3] + " " + dateArr[4] + "," + dateArr[5] + " " + dateArr[6].TrimEnd('\"') + ",";
                    }
                    else
                    {
                        data = data + ",,";
                    }
                    if (!lineArr[3].Contains("NOT FINISH"))
                    {
                        data = data + lineArr[3] + ",";
                    }
                    else
                    {
                        data = data + ",";
                    }
                    if (lineArr[4].Contains("SUCCESS"))
                    {
                        data = data + lineArr[4] + ",,";
                    } else
                    {
                        data = data + "FAIL" + "," + lineArr[4] + ",";
                    }
                    data = data + lineArr[5] + "\n";
                } else
                {
                    data = "#,WORKFLOW,DATETIME,TIMEZONE,TIME TAKEN,STATUS,ERROR,LOG FILE\n";
                }
                using (TextWriter sw = new StreamWriter(Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\" + "tagui_report.csv"))
                {
                    sw.WriteLine(data);
                }
            }
            ImportCSV(Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\" + "tagui_report.csv");
            PivotData();
        }


        private void RunFlow(bool deploy)
        {

            TextBox textBoxFlowFile = Globals.ThisAddIn.TaskPane.Control.Controls["textBoxFlowFile"] as TextBox;
            string flowFilePath = textBoxFlowFile.Text;
            string fileType = FlowFileType(flowFilePath);
            if (fileType != "invalid")
            {
                string tagFilePath = flowFilePath;
                if (fileType == ".docx")
                {
                    if (!ValidateFlowFileIsClosed(flowFilePath))
                    {
                        MessageBox.Show("Flow file must be saved and closed before running/deploying", "Oops!");
                        return;
                    }
                    tagFilePath = CreateTagFile(flowFilePath);
                }
                CheckBox checkBoxObjectRepository = Globals.ThisAddIn.TaskPane.Control.Controls["checkBoxObjectRepository"] as CheckBox;
                ComboBox comboBoxObjectRepository = Globals.ThisAddIn.TaskPane.Control.Controls["comboBoxObjectRepository"] as ComboBox;
                if (checkBoxObjectRepository.Checked)
                {
                    
                    string sheetName = comboBoxObjectRepository.Text;
                    string objRepoCsvFilePath = GetObjRepoCsvFilePath(flowFilePath);
                    if (ValidateSheetStillExists(sheetName))
                    {
                        CreateCsvFile(objRepoCsvFilePath, sheetName);
                    }
                    else
                    {
                        MessageBox.Show("Worksheet " + sheetName + " does not exist", "Oops!");
                        return;
                    }
                }
                string datatableCsvFilePath = "";
                CheckBox checkBoxDatatable = Globals.ThisAddIn.TaskPane.Control.Controls["checkBoxDatatable"] as CheckBox;

                ComboBox comboBoxAllSheets = Globals.ThisAddIn.TaskPane.Control.Controls["comboBoxAllSheets"] as ComboBox;
                if (checkBoxDatatable.Checked)
                {

                    string sheetName = comboBoxAllSheets.Text;
                    if (ValidateSheetStillExists(sheetName))
                    {
                        datatableCsvFilePath = GetCsvFilePath();
                        CreateCsvFile(datatableCsvFilePath, sheetName);
                    }
                    else
                    {
                        MessageBox.Show("Worksheet " + sheetName + " does not exist", "Oops!");
                        return;
                    }
                }
                string runOptions = AddRunOption(deploy, datatableCsvFilePath);
                RunCommand(tagFilePath, runOptions, deploy);
            }
            else
            {
                MessageBox.Show("Please select a valid flow file to run/deploy", "Oops!");
            }
        }
        private string GetObjRepoCsvFilePath(string flowFilePath)
        {
            string[] fileLocArr = flowFilePath.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = flowFilePath.Substring(0, flowFilePath.Length - lengthToCut);
            string objRepoCsvFilePath = folderPath + "tagui_local.csv";
            return objRepoCsvFilePath;
        }
        private string FlowFileType(string flowFilePath)
        {
            string flowFileType = "";
            if (!File.Exists(flowFilePath))
            {
                flowFileType = "invalid";
            }
            else
            {
                try
                {
                    if (flowFilePath.Substring(flowFilePath.Length - 4, 4) == ".tag")
                    {
                        flowFileType = ".tag";
                    }
                    else if (flowFilePath.Substring(flowFilePath.Length - 5, 5) == ".docx")
                    {
                        flowFileType = ".docx";
                    }
                    else
                    {
                        flowFileType = "invalid";
                    }
                }
                catch
                {
                    flowFileType = "invalid";
                }
            }
            return flowFileType;
        }
        private string CreateTagFile(string flowFilePath)
        {
            string[] fileLocArr = flowFilePath.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = flowFilePath.Substring(0, flowFilePath.Length - lengthToCut);
            string[] fileLocArr2 = fileLocArr[fileLocArr.Length - 1].Split('.');
            string fileName = fileLocArr2[0];
            string tagFilePath = folderPath + fileName.Replace(" ", "_") + ".tag";
            WordprocessingDocument wordprocessingDocument =
                    WordprocessingDocument.Open(flowFilePath, true);
            string textFromDoc = "";
            var paragraphs = wordprocessingDocument.MainDocumentPart.RootElement.Descendants<Paragraph>();
            foreach (var paragraph in paragraphs)
            {
                textFromDoc += paragraph.InnerText + "\r\n";
            }
            wordprocessingDocument.Close();
            File.WriteAllText(tagFilePath, textFromDoc);
            return tagFilePath;
        }
        private string AddRunOption(bool deploy, string datatableCsvFilePath)
        {
            CheckBox checkBoxNoBrowser = Globals.ThisAddIn.TaskPane.Control.Controls["checkBoxNoBrowser"] as CheckBox;
            CheckBox checkBoxReport = Globals.ThisAddIn.TaskPane.Control.Controls["checkBoxReport"] as CheckBox;
            CheckBox checkBoxQuiet = Globals.ThisAddIn.TaskPane.Control.Controls["checkBoxQuiet"] as CheckBox;
            CheckBox checkBoxDatatable = Globals.ThisAddIn.TaskPane.Control.Controls["checkBoxDatatable"] as CheckBox;
            CheckBox checkBoxInputs = Globals.ThisAddIn.TaskPane.Control.Controls["checkBoxInputs"] as CheckBox;
            TextBox textBoxParam = Globals.ThisAddIn.TaskPane.Control.Controls["textBoxParam"] as TextBox;
            string runOptions = "";
            if (checkBoxNoBrowser.Checked) runOptions += " -n";
            if (checkBoxReport.Checked) runOptions += " -r";
            if (checkBoxQuiet.Checked) runOptions += " -q";
            if (checkBoxDatatable.Checked) runOptions += " " + datatableCsvFilePath;
            if (checkBoxInputs.Checked) runOptions += " " + textBoxParam.Text;
            if (deploy) runOptions += " -d";
            return runOptions;
        }
        private string GetCsvFilePath()
        {
            string fileLoc = Globals.ThisAddIn.Application.ActiveWorkbook.FullName;
            string csvFilePath;
            if (fileLoc.Contains(@"\"))
            {
                csvFilePath = GetCsvFilePathSavedDoc(fileLoc);
            }
            else
            {
                string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
                Directory.CreateDirectory(appDataFolder); //create new TagUI folder in appData folder it it does not exist
                csvFilePath = appDataFolder + "DataTable.csv";
            }
            return csvFilePath;
        }

        private void CreateCsvFile(string csvFilePath, string sheetName)
        {
            Excel.Worksheet EW = Globals.ThisAddIn.Application.ActiveWorkbook.Worksheets[sheetName] as Excel.Worksheet;
            Excel.Workbook newWorkbook = Globals.ThisAddIn.Application.Workbooks.Add();
            EW.Copy(newWorkbook.Sheets[1]);
            string newWbSheetName = sheetName;
            if (sheetName == "Sheet1") newWbSheetName = sheetName + " (2)";
            Globals.ThisAddIn.Application.DisplayAlerts = false; //to overwrite existing file without promopting user
            newWorkbook.Worksheets[newWbSheetName].SaveAs(csvFilePath, Excel.XlFileFormat.xlCSV, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing);
            newWorkbook.Close(true);
        }
        private string GetCsvFilePathSavedDoc(string fileLoc)
        {
            string[] fileLocArr = fileLoc.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = fileLoc.Substring(0, fileLoc.Length - lengthToCut);
            string[] fileLocArr2 = fileLocArr[fileLocArr.Length - 1].Split('.');
            string fileName = fileLocArr2[0];
            ComboBox comboBoxAllSheets = Globals.ThisAddIn.TaskPane.Control.Controls["comboBoxAllSheets"] as ComboBox;
            string csvFilePath = folderPath + fileName.Replace(" ", "_") + "_" + comboBoxAllSheets.Text.Replace(" ", "_") + ".csv";
            return csvFilePath;
        }

        private void ShiftDeployedFile(string tagFilePath)
        {
            string originalPath = tagFilePath.Split('.')[0] + ".cmd";
            string finalPath = Globals.ThisAddIn.Application.ActiveWorkbook.FullName.Split('.')[0] + ".cmd";
            File.Move(originalPath, finalPath);
        }
        private void RunCommand(string tagFilePath, string runOptions, bool deploy)
        {
            TextBox textBoxOutput = Globals.ThisAddIn.TaskPane.Control.Controls["textBoxOutput"] as TextBox;
            textBoxOutput.Clear();
            string cmdCommand = "/C end_processes & tagui " + tagFilePath + runOptions;
            try
            {
                var process = new Process
                {
                    StartInfo = new ProcessStartInfo
                    {
                        FileName = "cmd.exe",
                        Arguments = cmdCommand,
                        UseShellExecute = false,
                        RedirectStandardOutput = true,
                        CreateNoWindow = true
                    }
                };
                process.SynchronizingObject = textBoxOutput;
                process.EnableRaisingEvents = true;
                process.Start();
                process.BeginOutputReadLine();
                process.OutputDataReceived += new DataReceivedEventHandler((_sender, _e) =>
                {
                    if (!String.IsNullOrEmpty(_e.Data))
                    {
                        textBoxOutput.AppendText("» " + _e.Data);
                        textBoxOutput.AppendText(Environment.NewLine);
                    }
                });
                process.Exited += (s, evt) =>
                {
                    process?.Dispose();
                    if (deploy)
                    {
                        ShiftDeployedFile(tagFilePath);
                    }
                    else
                    {
                        buttonRun.Image = Properties.Resources.Run;
                        buttonRun.Label = "Run";
                    }
                };
            }
            catch (Exception e)
            {
                textBoxOutput.AppendText(e.Message);
            }
        }
        ///// validations /////
        private bool ValidateFlowFileIsClosed(string flowFilePath)
        {
            bool isClosed;
            try
            {
                WordprocessingDocument wordprocessingDocument =
                    WordprocessingDocument.Open(flowFilePath, false);
                wordprocessingDocument.Close();
                isClosed = true;
            }
            catch { isClosed = false; }
            return isClosed;
        }
        private bool ValidateSheetStillExists(string sheetName)
        {
            bool sheetExists;
            try
            {
                Excel.Worksheet EW = Globals.ThisAddIn.Application.ActiveWorkbook.Worksheets[sheetName] as Excel.Worksheet;
                sheetExists = true;
            }
            catch
            {
                sheetExists = false;
            }
            return sheetExists;
        }
    }
}
