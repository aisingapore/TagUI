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
using Microsoft.Office.Core;
using Word = Microsoft.Office.Interop.Word;
using System.Drawing.Imaging;
using Run = DocumentFormat.OpenXml.Wordprocessing.Run;
using Text = DocumentFormat.OpenXml.Wordprocessing.Text;
using CustomTaskPane = Microsoft.Office.Tools.CustomTaskPane;
using DocumentFormat.OpenXml;
using Microsoft.Office.Interop.Word;
using Drawing = DocumentFormat.OpenXml.Wordprocessing.Drawing;
using System.Drawing;

namespace TagUIExcelAddIn
{
    public partial class Ribbon1
    {
        public List<string> subFlowFilePaths = new List<string>();
        public List<string> csvList = new List<string>();
        public int subFlowCount = 0;
        public int subFlowTotal = 0;
        public int imageCount = 1;
        private void Ribbon1_Load(object sender, RibbonUIEventArgs e)
        {

        }

        private void toggleButton1_Click(object sender, RibbonControlEventArgs e)
        {
            //Globals.ThisAddIn.TaskPane.Visible = ((RibbonToggleButton)sender).Checked;
            try
            {
                DocumentProperties prps;
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
                Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault().Visible = ((RibbonToggleButton)sender).Checked;
            }
            catch { }
        }

        private void buttonRun_Click(object sender, RibbonControlEventArgs e)
        {
            subFlowFilePaths = new List<string>();
            subFlowCount = 0;
            subFlowTotal = 0;
            imageCount = 1;
            csvList = new List<string>();

            if (buttonRun.Label == "Run")
            {
                Excel.Application xlApp = Globals.ThisAddIn.Application;
                xlApp.Cursor = XlMousePointer.xlWait;

                buttonRun.Image = Properties.Resources.Stop;
                buttonRun.Label = "Stop";

                DocumentProperties prps;
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
                CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
                //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
                CheckBox checkBoxDatatableCSV = ctp.Control.Controls["checkBoxDatatable"] as CheckBox;
                CheckBox checkBoxObjRepo = ctp.Control.Controls["checkBoxObjectRepository"] as CheckBox;
                bool deploy = false;
                string dataTableCsv = "";
                string objRepoCsv = "";
                if (checkBoxDatatableCSV.Checked)
                {
                    dataTableCsv = ProcessDataTable(deploy);
                }
                if (checkBoxObjRepo.Checked)
                {
                    objRepoCsv = ProcessObjRepo(deploy);
                }
                try
                {
                    TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;
                    string tagFilePath = "";
                    if (FlowFileType(textBoxFlowFile.Text) == ".docx")
                    {
                        ProcessAllWordFiles(deploy);
                        tagFilePath = GetTagFilePath(0, deploy);
                    }
                    else
                    {
                        tagFilePath = textBoxFlowFile.Text;
                        ProcessTagFile(tagFilePath);
                    }
                    string runOptions = AddRunOption(deploy, dataTableCsv);
                 
                    RunCommand(tagFilePath, runOptions, deploy);
                }
                catch
                {
                    xlApp.Cursor = XlMousePointer.xlDefault;
                    buttonRun.Image = Properties.Resources.Run;
                    buttonRun.Label = "Run";
                    MessageBox.Show("Related workflow files must be closed before running/deploying");
                }
            }
            else
            {
                EndProcessesCommand();
                Excel.Application xlApp = Globals.ThisAddIn.Application;
                xlApp.Cursor = XlMousePointer.xlDefault;
                buttonRun.Image = Properties.Resources.Run;
                buttonRun.Label = "Run";
            }
        }

        private void buttonDeploy_Click(object sender, RibbonControlEventArgs e)
        {
            subFlowFilePaths = new List<string>();
            subFlowCount = 0;
            subFlowTotal = 0;
            imageCount = 1;
            csvList = new List<string>();
            Excel.Application xlApp = Globals.ThisAddIn.Application;
            xlApp.Cursor = XlMousePointer.xlWait;

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            CheckBox checkBoxDatatableCSV = ctp.Control.Controls["checkBoxDatatable"] as CheckBox;
            CheckBox checkBoxObjRepo = ctp.Control.Controls["checkBoxObjectRepository"] as CheckBox;
            bool deploy = true;
            string dataTableCsv = "";
            if (checkBoxDatatableCSV.Checked)
            {
                dataTableCsv = ProcessDataTable(deploy);
            }
            if (checkBoxObjRepo.Checked)
            {
                ProcessObjRepo(deploy);
            }
            try
            {
                TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;
                string tagFilePath = "";
                if (FlowFileType(textBoxFlowFile.Text) == ".docx")
                {
                    ProcessAllWordFiles(deploy);
                    tagFilePath = GetTagFilePath(0, deploy);
                }
                else
                {
                    tagFilePath = textBoxFlowFile.Text;
                    ProcessTagFile(tagFilePath);
                }
                string runOptions = AddRunOption(deploy, dataTableCsv);
                RunCommand(tagFilePath, runOptions, deploy);
            }
            catch { MessageBox.Show("Related workflow files must be closed before running/deploying"); }
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
        private string ShiftDeployedFile(string tagFilePath)
        {
            string originalPath = tagFilePath.Split('.')[0] + ".cmd";
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;
            string finalPath = textBoxFlowFile.Text.Split('.')[0] + ".cmd";
            if (File.Exists(finalPath))
            {
                File.Delete(finalPath);
            }
            File.Move(originalPath, finalPath);
            return finalPath;
        }

        private string GetTagFilePath(int index, bool deploy)
        {
            string tagFilePath;
            if (index == 0)
            {
                DocumentProperties prps;
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
                CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

                //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
                TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;
                string fileLoc = textBoxFlowFile.Text;
                string[] fileLocArr = fileLoc.Split('\\');
                string[] fileLocArr2 = fileLocArr[fileLocArr.Length - 1].Split('.');
                string fileName = fileLocArr2[0];
                tagFilePath = GetSavedFolderPath() + fileName + ".tag";
            }
            else
            {
                tagFilePath = GetSavedFolderPath() + "WorkFlow" + index.ToString() + ".tag";
            }
            return tagFilePath;
        }
        private void GenerateTagFile(string tagFilePath, string wordFilePath)
        {
            Word.Application application = new Word.Application();
            application.Visible = false;
            application.DisplayAlerts = WdAlertLevel.wdAlertsNone;
            Word.Document document = application.Documents.Open(wordFilePath);
            application.ActiveDocument.SaveAs(tagFilePath, WdSaveFormat.wdFormatText, Type.Missing, Type.Missing, false,
                    Type.Missing, Type.Missing, Type.Missing,
                    Type.Missing, Type.Missing, Type.Missing,
                    Type.Missing, Type.Missing, Type.Missing,
                    Type.Missing, Type.Missing);
            ((Word._Application)application).Quit();
        }

        private string GetSubFlowFileType(string subflowFilePath)
        {
            string fileType = "";
            if (subflowFilePath.Substring(subflowFilePath.Length - 4, 4) == "docx")
            {
                fileType = "docx";
            }
            else fileType = "tag";
            return fileType;
        }
        private string AddRunOption(bool deploy, string dataTableCsv)
        {
            string runOptions = "";
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            CheckBox checkBoxNoBrowser = ctp.Control.Controls["checkBoxNoBrowser"] as CheckBox;
            CheckBox checkBoxReport = ctp.Control.Controls["checkBoxReport"] as CheckBox;
            CheckBox checkBoxQuiet = ctp.Control.Controls["checkBoxQuiet"] as CheckBox;
            CheckBox checkBoxDatatableCSV = ctp.Control.Controls["checkBoxDatatable"] as CheckBox;
            CheckBox checkBoxInputs = ctp.Control.Controls["checkBoxInputs"] as CheckBox;
            TextBox textBoxParam = ctp.Control.Controls["textBoxParam"] as TextBox;
            if (checkBoxNoBrowser.Checked) runOptions += " -n";
            if (checkBoxReport.Checked) runOptions += " -r";
            if (checkBoxQuiet.Checked) runOptions += " -q";
            if (checkBoxDatatableCSV.Checked) runOptions += " " + dataTableCsv;
            if (checkBoxInputs.Checked) runOptions += " " + textBoxParam.Text;
            if (deploy) runOptions += " -d";
            return runOptions;
        }
        private void EndProcessesCommand()
        {
            string cmdCommand = "/C end_processes";
            var p = new Process
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
            p.Start();
            p.StartInfo.WindowStyle = ProcessWindowStyle.Hidden;
            p.Exited += (s, evt) =>
            {
                p?.Dispose();
            };
        }

        private void RunCommand(string tagFilePath, string runOptions, bool deploy)
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            TextBox textBoxOutput = ctp.Control.Controls["textBoxOutput"] as TextBox;
            textBoxOutput.Clear();
            string workingFolder = GetWorkingFolderPath(deploy);
            string cmdCommand = "/C end_processes & cd \"" + workingFolder + "\" & tagui \"" + tagFilePath + "\"" + runOptions;
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
                        string deployedPath = ShiftDeployedFile(tagFilePath);
                        MessageBox.Show("Workflow deployed at " + deployedPath);
                      
                    }
                    else
                    {
                        ProcessCsvOutput();
                        buttonRun.Image = Properties.Resources.Run;
                        buttonRun.Label = "Run";
                        Excel.Application xlApp = Globals.ThisAddIn.Application;
                        xlApp.Cursor = XlMousePointer.xlDefault;
                    }
                };
            }
            catch (Exception e)
            {
                textBoxOutput.AppendText(e.Message);
            }
        }

        private void ProcessCsvOutput()
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            CheckBox checkBoxCsvOutput = ctp.Control.Controls["checkBoxCsvOutput"] as CheckBox;
            if (csvList.Count > 0 && checkBoxCsvOutput.Checked)
            { 
                foreach (string csvFile in csvList)
                {
                    if (File.Exists(csvFile)) ImportCSV(csvFile);
                }
            }
            else return;
        }
        private void ProcessTagFile(string tagFilePath)
        {
            using (StreamReader sr = new StreamReader(tagFilePath))
            {
                string line = String.Empty;
                while ((line = sr.ReadLine()) != null)
                {
                    string firstWord = line.Split(' ')[0];
                    if (firstWord == "write" || firstWord == "dump" || firstWord == "save" || firstWord == "table")
                    {
                        string[] arr = line.Split(' ');
                        string lastWord = arr[arr.Length - 1];
                        if (lastWord.Substring(lastWord.Length - 3).ToLower() == "csv")
                        {
                            for (int i = arr.Length-1; i >0; i--)
                            {
                                if (arr[i] == "to")
                                {
                                    string csvFile = GetSavedFolderPath();
                                    for (int j = i + 1; j < arr.Length; j++)
                                    {
                                        csvFile += arr[j];
                                        if (j != arr.Length - 1)
                                        {
                                            csvFile += " ";
                                        }
                                    }
                                    if (!csvList.Any(x => csvList.Contains(csvFile))) csvList.Add(csvFile);
                                    break;
                                }
                            }
                        }
                    }

                    Console.WriteLine(line);
                }
            }
        }
        private void ProcessAllWordFiles(bool deploy)
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;

            int index = 0;
            string mainFlowWordCopyFilePath = GetWordCopyFilePath(index);
            CreateSubFlowWordCopy(textBoxFlowFile.Text, mainFlowWordCopyFilePath);
            string mainFlowFilePath = GetMainFlowFilePath();
            ProcessSubFlows(mainFlowFilePath, mainFlowWordCopyFilePath, deploy);
            string imageFolderPath = GetImageFolderPath(deploy);
            ProcessImages(mainFlowWordCopyFilePath, imageFolderPath);
            string tagFilePath = GetTagFilePath(index, deploy);
            GenerateTagFile(tagFilePath, mainFlowWordCopyFilePath);
            while (subFlowCount > 0)
            {
                index++;
                string subFlowWordCopyFilePath = GetWordCopyFilePath(index);
                CreateSubFlowWordCopy(subFlowFilePaths[index - 1], subFlowWordCopyFilePath);
                ProcessSubFlows(subFlowFilePaths[index - 1], subFlowWordCopyFilePath, deploy);
                ProcessImages(subFlowWordCopyFilePath, imageFolderPath);
                string subFlowTagFilePath = GetTagFilePath(index, deploy);
                GenerateTagFile(subFlowTagFilePath, subFlowWordCopyFilePath);
                subFlowCount--;
            }
        }
        //replaces all relative subflow file paths with absolute subflow file path
        private void ProcessSubFlows(string originalDocFilePath, string duplicateDocFilePath, bool deploy)
        {
            WordprocessingDocument duplicateDoc = WordprocessingDocument.Open(duplicateDocFilePath, true);
            var paragraphs = duplicateDoc.MainDocumentPart.RootElement.Descendants<DocumentFormat.OpenXml.Wordprocessing.Paragraph>();
            foreach (DocumentFormat.OpenXml.Wordprocessing.Paragraph paragraph in paragraphs)
            {
                string firstWord = paragraph.InnerText.ToLower().TrimStart().Split(' ')[0];
                if ( firstWord == "tagui")
                {
                    subFlowCount++;
                    subFlowTotal++;
                    string subflowFilePath = "";
                    string[] arr = paragraph.InnerText.ToLower().TrimStart().Split(' ');
                    for (int i = 1; i < arr.Length; i++)
                    {
                        subflowFilePath += arr[i] + " ";
                    }

                    string subflowAbsFilePath = subflowFilePath;
                    if (!subflowFilePath.Contains(":")) { subflowAbsFilePath = GetFlowFolderPath() + subflowFilePath; }
                    foreach (Run run in paragraph.Descendants<Run>())
                    {
                        run.RemoveAllChildren<Text>();
                    }
                    Run newRun = paragraph.AppendChild(new Run());
                    string workingFolder = GetWorkingFolderPath(deploy);
                    newRun.AppendChild(new Text("tagui " + workingFolder + "WorkFlow" + subFlowCount + ".tag"));
                    subFlowFilePaths.Add(subflowAbsFilePath);

                }
                if (firstWord == "write" || firstWord == "dump" || firstWord == "save" || firstWord == "table")
                {
                    string[] arr = paragraph.InnerText.ToLower().TrimStart().Split(' ');
                    string lastWord = arr[arr.Length - 1];
                    if (lastWord.Substring(lastWord.Length-3).ToLower() == "csv")
                    {
                        for (int i = arr.Length -1 ; i > 0; i--)
                        {
                            if (arr[i] == "to")
                            {
                                string csvFile = GetSavedFolderPath();
                                for (int j = i+1; j < arr.Length; j++)
                                {
                                    csvFile += arr[j];
                                    if (j!= arr.Length - 1)
                                    {
                                        csvFile += " ";
                                    }
                                }
                                if (!csvList.Any(x => csvList.Contains(csvFile))) csvList.Add(csvFile);
                                break;
                            }
                        }
                    }
                    
                }
            }
            duplicateDoc.Close();
        }
        private void ProcessImages(string wordCopyFilePath, string imageFolderPath)
        {
            FileStream fs = new FileStream(wordCopyFilePath, FileMode.Open);
            Body body = null;
            MainDocumentPart mainPart = null;
            using (WordprocessingDocument wdDoc = WordprocessingDocument.Open(fs, true))
            {
                mainPart = wdDoc.MainDocumentPart;
                body = wdDoc.MainDocumentPart.Document.Body;
                if (body != null)
                {
                    ExtractImages(body, mainPart, imageFolderPath);
                }
            }
            fs.Flush();
            fs.Close();
        }

        private List<string> ExtractImages(Body content, MainDocumentPart wDoc, string imageFolderPath)
        {
            List<string> imageList = new List<string>();
            foreach (DocumentFormat.OpenXml.Wordprocessing.Paragraph par in content.Descendants<DocumentFormat.OpenXml.Wordprocessing.Paragraph>())
            {
                ParagraphProperties paragraphProperties = par.ParagraphProperties;
                for (int i = 0; i < par.Descendants<Run>().Count(); i++)
                {
                    Run run = par.Descendants<Run>().ElementAt(i);
                    Drawing image =
                    run.Descendants<Drawing>().FirstOrDefault();
                    if (image != null)
                    {
                        var imageFirst = image.Inline.Graphic.GraphicData.Descendants<DocumentFormat.OpenXml.Drawing.Pictures.Picture>().FirstOrDefault();
                        var blip = imageFirst.BlipFill.Blip.Embed.Value;
                        ImagePart img = (ImagePart)wDoc.Document.MainDocumentPart.GetPartById(blip);
                        string imageFileName = string.Empty;
                        string imageFilePath = string.Empty;
                        using (Image toSaveImage = Bitmap.FromStream(img.GetStream()))
                        {
                            imageFileName = "Img_" + imageCount;
                            imageFilePath = imageFolderPath + "\\" + imageFileName + ".png";
                            try
                            {
                                toSaveImage.Save(imageFilePath, ImageFormat.Png);
                            }
                            catch (Exception ex)
                            {
                            }
                        }
                        imageList.Add(imageFilePath);
                        Run run2 = par.Descendants<Run>().ElementAt(i - 1);
                        run2.AppendChild(new Text(" Images/" + imageFileName + ".png"));
                        image.Remove();
                        imageCount++;
                    }
                }
            }
            return imageList;
        }
        private string GetMainFlowFilePath()
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

           // CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;
            string flowFilePath = textBoxFlowFile.Text;
            return flowFilePath;
        }
        private void CreateSubFlowWordCopy(string originalDocFilePath, string duplicateDocFilePath)
        {
            using (WordprocessingDocument originalDoc = WordprocessingDocument.Open(originalDocFilePath, false))
            using (WordprocessingDocument duplicateDoc = WordprocessingDocument.Create(duplicateDocFilePath, WordprocessingDocumentType.Document))
            {
                foreach (var part in originalDoc.Parts)
                    duplicateDoc.AddPart(part.OpenXmlPart, part.RelationshipId);
            }
        }
        private string GetFlowFolderPath()
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;
            string flowFilePath = textBoxFlowFile.Text;
            string[] fileLocArr = flowFilePath.Split('\\');
            Int32 lengthToCut = fileLocArr[fileLocArr.Length - 1].Length;
            string folderPath = flowFilePath.Substring(0, flowFilePath.Length - lengthToCut);
            return folderPath;
        }

        private string GetWordCopyFilePath(int index)
        {
            string wordCopyFilePath;
            string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
            Directory.CreateDirectory(appDataFolder);
            wordCopyFilePath = appDataFolder + "WorkFlow" + index.ToString() + ".docx";
            return wordCopyFilePath;
        }

        
        private string GetImageFolderPath(bool deploy)
        {
            string imageFolderPath;
            imageFolderPath = GetSavedFolderPath() + "Images" + "";
            Directory.CreateDirectory(imageFolderPath);
            return imageFolderPath;
        }

        private void GenerateObjRepoCsvFile(string csvFilePath, string sheetName)
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
        private string ProcessDataTable(bool deploy)
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            ComboBox comboBoxDatatableWs = ctp.Control.Controls["comboBoxAllSheets"] as ComboBox;
            TextBox textBoxRange = ctp.Control.Controls["textBoxRange"] as TextBox;
            string csvFilePath = "";
            string dataTableSheet = comboBoxDatatableWs.Text;
            csvFilePath = GetWorkingFolderPath(deploy) + "DataTable.csv";
            Excel.Worksheet EW = Globals.ThisAddIn.Application.ActiveWorkbook.Worksheets[dataTableSheet] as Excel.Worksheet;
            Excel.Workbook newWorkbook = Globals.ThisAddIn.Application.Workbooks.Add();
            string range = textBoxRange.Text;
            if (range != "" && range != "Optional range")
            {
                string rangeStart = "";
                string rangeEnd = "";
                rangeStart = range.Split(':')[0];
                rangeEnd = range.Split(':')[1];
                Excel.Range sourceRange = EW.get_Range(rangeStart, rangeEnd);
                Excel.Range destinationRange = newWorkbook.Worksheets.get_Item(1).Range["A1"];
                sourceRange.Copy(destinationRange);
            }
            else
            {
                EW.Copy(newWorkbook.Sheets[1]);
            }
            Globals.ThisAddIn.Application.DisplayAlerts = false; //to overwrite existing file without promopting user
            newWorkbook.ActiveSheet.SaveAs(csvFilePath, Excel.XlFileFormat.xlCSV, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing, Type.Missing);
            newWorkbook.Close(false);

            csvFilePath = "DataTable.csv";
            return csvFilePath;
        }

        private string GetSavedFolderPath()
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            TextBox textBoxFlowFile = ctp.Control.Controls["textBoxFlowFile"] as TextBox;
            string fileLoc = textBoxFlowFile.Text;
            string folderPath = "";
            if (FlowFileType(fileLoc) == ".docx")
            {
                folderPath = fileLoc.Substring(0, fileLoc.Length - 5) + "\\";
                Directory.CreateDirectory(folderPath);
            }
            else
            {
                string[] arr = fileLoc.Split('\\');
                int lengthToCut = arr[arr.Length - 1].Length;
                folderPath = fileLoc.Substring(0, fileLoc.Length - lengthToCut);
            }
            
            return folderPath;
        }

        private string ProcessObjRepo(bool deploy)
        {
            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveWorkbook.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();
            //CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.FirstOrDefault();
            ComboBox comboBoxObjRepo = ctp.Control.Controls["comboBoxObjectRepository"] as ComboBox;
            string csvFilePath = "";
            string objRepoSheet = comboBoxObjRepo.Text;
            csvFilePath = GetWorkingFolderPath(deploy) + "tagui_local.csv";
            GenerateObjRepoCsvFile(csvFilePath, objRepoSheet);
            return csvFilePath;
        }
        private string GetWorkingFolderPath(bool deploy)
        {
            string workingFolder = "";
            workingFolder = GetSavedFolderPath();
            return workingFolder;
        }


        private void buttonUpdate_Click(object sender, RibbonControlEventArgs e)
        {
            FormUpdate f1 = new FormUpdate();
            f1.Show();
        }
        private void buttonUsageGuide_Click(object sender, RibbonControlEventArgs e)
        {
            Process.Start("https://tagui.readthedocs.io/en/latest/index.html");
        }

        private void buttonViewReport_Click(object sender, RibbonControlEventArgs e)
        {
            string allPaths = Environment.GetEnvironmentVariable("Path").ToLower() + ";" + Environment.GetEnvironmentVariable("Path", EnvironmentVariableTarget.User).ToLower();
            string[] arr = allPaths.Split(';');
            string taguiFolderPath = arr.Where(s => s.Contains("tagui")).FirstOrDefault();
            string taguiReportCsvPath = taguiFolderPath + @"\tagui_report.csv";
            string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
            Directory.CreateDirectory(appDataFolder);
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

                    string userId = "";
                    if (lineArr.Length == 7) userId = lineArr[6];
                    data = data + lineArr[5] + "," + userId + "\n";
                } else
                {
                    data = "#,WORKFLOW,DATETIME,TIMEZONE,TIME TAKEN,STATUS,ERROR,LOG FILE,USER ID\n";
                }
                using (TextWriter sw = new StreamWriter(appDataFolder + "tagui_report.csv"))
                {
                    sw.WriteLine(data);
                }
            }
            ImportCSV(Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\" + "tagui_report.csv");
            PivotData();
        }
        private void ImportCSV(string csvFilePath)
        {
            Excel.Application xlApp = Globals.ThisAddIn.Application;
            xlApp.DisplayAlerts = false;
            var activeWkbName = xlApp.ActiveWorkbook.Name;
            var templateWorkbook = xlApp.Workbooks.Open(csvFilePath);
            var from = (templateWorkbook.Sheets[1] as Excel.Worksheet);
            xlApp.Workbooks[activeWkbName].Activate();
            from.Copy(Type.Missing, xlApp.Worksheets[xlApp.ActiveWorkbook.Sheets.Count]);
            templateWorkbook.Close(false);
            xlApp.DisplayAlerts = true;
        }
        int index = 0;
        private void PivotData()
        {
            index++;
            Excel.Worksheet osheet = Globals.ThisAddIn.Application.ActiveWorkbook.ActiveSheet;
            var pch = Globals.ThisAddIn.Application.ActiveWorkbook.PivotCaches();
            Excel.Range pivotData = (Excel.Range)osheet.UsedRange;
            Excel.PivotCache pc = pch.Create(Excel.XlPivotTableSourceType.xlDatabase, pivotData);
            Excel.PivotTable pvt = pc.CreatePivotTable(osheet.Range["J1"], "MyPivotTable"+index);
            Excel.PivotField pageField = (Excel.PivotField)pvt.PivotFields("WORKFLOW");
            pageField.Orientation = Excel.XlPivotFieldOrientation.xlPageField;
            Excel.PivotField rowField = (Excel.PivotField)pvt.PivotFields("Status");
            rowField.Orientation = Excel.XlPivotFieldOrientation.xlRowField;
            Excel.PivotField valueField = (Excel.PivotField)pvt.PivotFields("Status");
            valueField.Orientation = Excel.XlPivotFieldOrientation.xlDataField;

            Excel.SlicerCaches slicerCaches = Globals.ThisAddIn.Application.ActiveWorkbook.SlicerCaches;
            //Month Slicer
            string nameDateTime = "Slicer_DATETIME" + index.ToString();
            string nameMonth = "Slicer_Month" + index.ToString();
           
            Excel.SlicerCache monthSlicerCache = slicerCaches.Add2(pvt, "DATETIME", nameDateTime, XlSlicerCacheType.xlTimeline);
            Excel.Slicers monthSlicers = monthSlicerCache.Slicers;
            Excel.Slicer monthSlicer = monthSlicers.Add(osheet, Type.Missing,
                nameMonth, "Date Range", 160, 10, 250, 150);
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
