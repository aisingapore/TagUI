using Microsoft.Office.Tools.Ribbon;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Drawing;
using System.Windows.Forms;
using System.Diagnostics;
using System.IO;
using DocumentFormat.OpenXml.Packaging;
using DocumentFormat.OpenXml.Wordprocessing;
using Excel = Microsoft.Office.Interop.Excel;
using Word = Microsoft.Office.Interop.Word;
using System.Drawing.Imaging;
using Run = DocumentFormat.OpenXml.Wordprocessing.Run;
using CheckBox = System.Windows.Forms.CheckBox;
using Text = DocumentFormat.OpenXml.Wordprocessing.Text;

using CustomTaskPane = Microsoft.Office.Tools.CustomTaskPane;
using DocumentFormat.OpenXml;
using Microsoft.Office.Interop.Word;
using Microsoft.Office.Tools.Word;
using Microsoft.Office.Core;
using System.Text;
using System.Globalization;

namespace TagUIWordAddIn
{
    public partial class Ribbon1
    {
        public List<string> subFlowFilePaths = new List<string>();
        public int subFlowCount = 0;
        public int subFlowTotal = 0;
        public int imageCount = 1;
 
        private void buttonRun_Click(object sender, RibbonControlEventArgs e)
        {
            subFlowFilePaths = new List<string>();
            subFlowCount = 0;
            subFlowTotal = 0;
            imageCount = 1;
            if (buttonRun.Label == "Run")
            {
                Word.Application xlApp = Globals.ThisAddIn.Application;
                xlApp.System.Cursor = WdCursorType.wdCursorWait;

                buttonRun.Image = Properties.Resources.Stop;
                buttonRun.Label = "Stop";

                DocumentProperties prps;
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
                CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

                CheckBox checkBoxDatatableCSV = ctp.Control.Controls["checkBoxDatatableCSV"] as CheckBox;
                CheckBox checkBoxObjRepo = ctp.Control.Controls["checkBoxObjRepo"] as CheckBox;
                bool deploy = false;
                string dataTableCsv = "";
                string objRepoCsv = "";
                if (checkBoxDatatableCSV.Checked)
                {
                    if (!ValidateExcelCheckBoxFields(checkBoxDatatableCSV)) {
                        xlApp.System.Cursor = WdCursorType.wdCursorNormal;
                        buttonRun.Image = Properties.Resources.Run;
                        buttonRun.Label = "Run";
                        return;
                    };
                    dataTableCsv = ProcessDataTable(deploy);
                }
                if (checkBoxObjRepo.Checked)
                {
                    if (!ValidateExcelCheckBoxFields(checkBoxObjRepo))
                    {
                        xlApp.System.Cursor = WdCursorType.wdCursorNormal;
                        buttonRun.Image = Properties.Resources.Run;
                        buttonRun.Label = "Run";
                        return;
                    };
                    objRepoCsv = ProcessObjRepo(deploy);
                }
                try
                {
                    ProcessAllWordFiles(deploy);
                    string tagFilePath = GetTagFilePath(0, deploy);
                    string runOptions = AddRunOption(deploy, dataTableCsv);
                    RunCommand(tagFilePath, runOptions, deploy);
                }
                catch (Exception ex)
                {
                    xlApp.System.Cursor = WdCursorType.wdCursorNormal;
                    buttonRun.Image = Properties.Resources.Run;
                    buttonRun.Label = "Run";
                    ErrorDialog f1 =  new ErrorDialog();
                    f1.Show("Error", ex.Message, ex.ToString());
                }
            }
            else
            {
                EndProcessesCommand();
                Word.Application xlApp = Globals.ThisAddIn.Application;
                xlApp.System.Cursor = WdCursorType.wdCursorWait;
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
            Word.Application xlApp = Globals.ThisAddIn.Application;
            xlApp.System.Cursor = WdCursorType.wdCursorWait;

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            CheckBox checkBoxDatatableCSV = ctp.Control.Controls["checkBoxDatatableCSV"] as CheckBox;
            CheckBox checkBoxObjRepo = ctp.Control.Controls["checkBoxObjRepo"] as CheckBox;
            bool deploy = true;
            if (DocIsSaved())
            {
                string dataTableCsv = "";
                if (checkBoxDatatableCSV.Checked)
                {
                    if (!ValidateExcelCheckBoxFields(checkBoxDatatableCSV))
                    {
                        xlApp.System.Cursor = WdCursorType.wdCursorNormal;
                        return;
                    };
                    dataTableCsv = ProcessDataTable(deploy);
                }
                if (checkBoxObjRepo.Checked)
                {
                    if (!ValidateExcelCheckBoxFields(checkBoxObjRepo))
                    {
                        xlApp.System.Cursor = WdCursorType.wdCursorNormal;
                        return;
                    };
                    ProcessObjRepo(deploy);
                }
                try
                {
                    ProcessAllWordFiles(deploy);
                    string tagFilePath = GetTagFilePath(0, deploy);
                    string runOptions = AddRunOption(deploy, dataTableCsv);
                    RunCommand(tagFilePath, runOptions, deploy);
                } catch (Exception ex) 
                {
                    ErrorDialog f1 = new ErrorDialog();
                    f1.Show("Error", ex.Message, ex.ToString());
                }
            }
            else
            {
                MessageBox.Show("Document must be saved before deploying", "Oops!");
                try
                {
                    Globals.ThisAddIn.Application.ActiveDocument.Save();
                }
                catch { }
            }
        }
        private string ShiftDeployedFile(string tagFilePath)
        { 
            string originalPath = tagFilePath.Split('.')[0] + ".cmd";
            string finalPath = Globals.ThisAddIn.Application.ActiveDocument.FullName.Split('.')[0] + ".cmd";
            if (File.Exists(finalPath))
            {
                File.Delete(finalPath);
            }
            File.Move(originalPath, finalPath);
            return finalPath;
        }
        /////////////////////////////////////////////RIBBON HELPER STEPS//////////////////////////////////////////////////
        private void toggleButtonShowTaskPane_Click(object sender, RibbonControlEventArgs e)
        {
            try
            {
                DocumentProperties prps;
                prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
                Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault().Visible = ((RibbonToggleButton)sender).Checked;
            }
            catch { }
        }

        private void buttonSnapshot_Click(object sender, RibbonControlEventArgs e)
        {
            FormSnapshot snapshotBar = new FormSnapshot();
            snapshotBar.Show();
        }

        private void buttonLive_Click(object sender, RibbonControlEventArgs e)
        {
            FormLive f1 = new FormLive();
            f1.Show();
        }
        private void buttonClick_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("click ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
        }
        private void buttonRclick_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("rclick ");
            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
        }

        private void buttonDclick_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("dclick ");
            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
        }
        private void buttonType_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("type ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText(" as ");
            AddRichTextContentControl("text", "");
        }

        private void buttonSelect_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new System.Collections.Generic.List
           <Microsoft.Office.Tools.Word.RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("select ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText(" as ");
            AddRichTextContentControl("option", "Text / Value");
        }

        private void buttonHover_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("hover ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
        }
        private void buttonRead_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new System.Collections.Generic.List
            <Microsoft.Office.Tools.Word.RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("read ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("variable", "");
        }

        private void buttonShow_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("show ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
        }

        private void buttonSave_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("save ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("filename", "");
        }

        private void buttonEcho_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("echo ");
            AddRichTextContentControl("text / `variable`", "");
        }

        private void buttonAsk_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("ask ");
            AddRichTextContentControl("some question", "");

        }

        private void buttonSnap_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("snap ");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("filename", "");
        }

        private void buttonLoad_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("load ");
            AddRichTextContentControl("filename", "");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("variable", "");
        }

        private void buttonDump_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("dump ");
            AddRichTextContentControl("text / `variable`", "");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("filename", "");
        }

        private void buttonWrite_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("write ");
            AddRichTextContentControl("text / `variable`", "");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("filename", "");
        }
        private void buttonIf_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.Application.Selection.TypeText("\n");
            Range rng = Globals.ThisAddIn.Application.Selection.Range;
            rng.Delete();
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("if ");
            AddRichTextContentControl("some condition", "");
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "\t// some steps");
        }
        private void buttonLoop_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.Application.Selection.TypeText("\n");
            Range rng = Globals.ThisAddIn.Application.Selection.Range;
            rng.Delete();
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("for ");
            AddRichTextContentControl("n","");
            Globals.ThisAddIn.Application.Selection.TypeText(" from ");
            AddRichTextContentControl("1", "");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("10", "");
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "\t// some steps");
        }

        private void buttonBreak_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("break\n");
        }
        private void buttonLiveStep_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("live\n");
        }

        private void buttonExcel_Click(object sender, RibbonControlEventArgs e)
        {
            Process.Start("https://tagui.readthedocs.io/en/latest/reference.html#excel");
        }

        private void buttonTelegram_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("telegram ");
            AddRichTextContentControl("id", " Message @taguibot to get your id");
            Globals.ThisAddIn.Application.Selection.TypeText(" ");
            AddRichTextContentControl("message", "");
        }
        private void buttonKeyboard_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("keyboard ");
            AddRichTextContentControl("keys", "");
        }

        private void buttonMouse_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("mouse ");
            AddRichTextContentControl("up / down", "");
        }

        private void buttonTagui_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("tagui ");
            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            AddRichTextContentControl("flow file", " .docx");
        }

        private void buttonWait_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("wait ");
            AddRichTextContentControl("seconds", "");
        }

        private void buttonTimeout_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("timeout ");
            AddRichTextContentControl("seconds", "");
        }

        private void buttonTable_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("table ");
            AddRichTextContentControl("table number or XPath", "");
            Globals.ThisAddIn.Application.Selection.TypeText(" to ");
            AddRichTextContentControl("filename", " .csv");
        }

        private void buttonFrame_Click(object sender, RibbonControlEventArgs e)
        {
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("frame ");
            AddRichTextContentControl("id / name", "");
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "\t// some steps");
        }

        private void buttonPopup_Click(object sender, RibbonControlEventArgs e)
        {
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("popup ");
            AddRichTextContentControl("unique part of new tab's URL", "");
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "\t// some steps");
        }

        private void buttonApi_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("api ");
            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            AddRichTextContentControl("endpoint", "");
        }

        private void buttonRunStep_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("run ");
            AddRichTextContentControl("command", " OS-level command");
        }

        private void buttonVision_Click(object sender, RibbonControlEventArgs e)
        {
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("vision begin ");
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "// some steps\n" + tabSpace + "vision finish");
        }

        private void buttonDom_Click(object sender, RibbonControlEventArgs e)
        {
            Range rng = Globals.ThisAddIn.Application.Selection.Range;
            rng.Delete();
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("dom begin");
            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "// some steps\n" + tabSpace + "dom finish");
            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
        }

        private void buttonJs_Click(object sender, RibbonControlEventArgs e)
        {
            Range rng = Globals.ThisAddIn.Application.Selection.Range;
            rng.Delete();
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("js begin");

            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "// some steps\n" + tabSpace + "js finish");
            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
        }

        private void buttonR_Click(object sender, RibbonControlEventArgs e)
        {
            Range rng = Globals.ThisAddIn.Application.Selection.Range;
            rng.Delete();
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("r begin");
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "// some steps\n" + tabSpace + "r finish");
        }

        private void buttonPy_Click(object sender, RibbonControlEventArgs e)
        {
            Range rng = Globals.ThisAddIn.Application.Selection.Range;
            rng.Delete();
            string text = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Text.ToString();
            string tabSpace = text.Replace("\n", "").Replace("\r", "");
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("py begin");

            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            Globals.ThisAddIn.Application.Selection.TypeText("\n" + tabSpace + "// some steps\n" + tabSpace + "py finish");

            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
        }

        private void buttonCount_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("count('");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText("')");
        }

        private void buttonExist_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("exist('");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText("')");
        }

        private void buttonPresent_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("present('");
            AddRichTextContentControl("element", " Identifier / Snapshot / Coordinates");
            Globals.ThisAddIn.Application.Selection.TypeText("')");
        }

        private void buttonClipboard_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.Application.Selection.TypeText("clipboard()");
        }

        private void buttonCsvRow_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("write `csv_row([");

            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
            AddRichTextContentControl("variable, 'text'", "");
            Globals.ThisAddIn.Application.Selection.TypeText("])` to ");
            AddRichTextContentControl("filename", "");
        }

        private void buttonTimer_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.Application.Selection.TypeText("timer()");
        }

        private void buttonUrl_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.Application.Selection.TypeText("url()");

            foreach (Range error in Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.SpellingErrors)
            {
                error.NoProofing = 1;
            }
        }

        private void buttonTitle_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.Application.Selection.TypeText("title()");
        }

        private void buttonText_Click(object sender, RibbonControlEventArgs e)
        {
            Globals.ThisAddIn.Application.Selection.TypeText("text()");
        }

        private void buttonDelChars_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("del_chars(");
            AddRichTextContentControl("text_variable", "");
            Globals.ThisAddIn.Application.Selection.TypeText(", '");
            AddRichTextContentControl("character(s) to delete", "");
            Globals.ThisAddIn.Application.Selection.TypeText("')");
        }

        private void buttonGetText_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("get_text(");
            AddRichTextContentControl("text_variable", "");
            Globals.ThisAddIn.Application.Selection.TypeText(", '");
            AddRichTextContentControl("1st anchor", "");
            Globals.ThisAddIn.Application.Selection.TypeText("', '");
            AddRichTextContentControl("2nd anchor", "");
            Globals.ThisAddIn.Application.Selection.TypeText("')");
        }

        private void buttonGetEnv_Click(object sender, RibbonControlEventArgs e)
        {
            richTextControls = new List<RichTextContentControl>();
            Globals.ThisAddIn.Application.Selection.TypeText("get_env('");
            AddRichTextContentControl("environment variable", "");
            Globals.ThisAddIn.Application.Selection.TypeText("')");
        }
        private void buttonSettings_Click(object sender, RibbonControlEventArgs e)
        {
            FormSettings f1 = new FormSettings();
            f1.Show();
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

        private List<RichTextContentControl> richTextControls;
        private int count = 0;

        void AddRichTextContentControl(string placeholderText, string titleText)
        {
            Range rng = Globals.ThisAddIn.Application.Selection.Range;
            rng.Select();
            RichTextContentControl tempControl = Globals.Factory.GetVstoObject(Globals.ThisAddIn.Application.ActiveDocument).Controls.AddRichTextContentControl("VSTORichTextControl" + count.ToString());
            tempControl.PlaceholderText = placeholderText;
            Range tempControlRange = tempControl.Range;
            tempControlRange.Font.Size = Globals.ThisAddIn.Application.Selection.Paragraphs[1].Range.Font.Size;
            count++;
            richTextControls.Add(tempControl);
            tempControl.Title = titleText;
            rng.MoveStartUntil(Environment.NewLine, Word.WdConstants.wdForward);
            rng.Select();
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        private bool DocIsSaved()
        {
            bool isSaved;
            string fileLoc = Globals.ThisAddIn.Application.ActiveDocument.FullName;
            if (fileLoc.Contains(@"\"))
            {
                isSaved = true;
            }
            else
            {
                isSaved = false;
            }
            return isSaved;
        }

        private string GetTagFilePath(int index, bool deploy)
        {
            string tagFilePath;
            if (deploy || DocIsSaved())
            {
                if (index == 0)
                {
                    string fileLoc = Globals.ThisAddIn.Application.ActiveDocument.FullName;
                    string[] fileLocArr = fileLoc.Split('\\');
                    string[] fileLocArr2 = fileLocArr[fileLocArr.Length - 1].Split('.');
                    string fileName = fileLocArr2[0];
                    tagFilePath = GetSavedFolderPath() + fileName + ".tag";
                }
                else
                {
                    tagFilePath = GetSavedFolderPath() + "WorkFlow" + index.ToString() + ".tag";
                }
            }
            else
            {
                string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
                Directory.CreateDirectory(appDataFolder);
                tagFilePath = appDataFolder + "WorkFlow" + index.ToString() + ".tag";
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
            ((_Application)application).Quit();
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
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            CheckBox checkBoxNoBrowser = ctp.Control.Controls["checkBoxNoBrowser"] as CheckBox;
            CheckBox checkBoxReport = ctp.Control.Controls["checkBoxReport"] as CheckBox;
            CheckBox checkBoxTurbo = ctp.Control.Controls["checkBoxTurbo"] as CheckBox;
            CheckBox checkBoxQuiet = ctp.Control.Controls["checkBoxQuiet"] as CheckBox;
            CheckBox checkBoxMSEdge = ctp.Control.Controls["checkBoxMSEdge"] as CheckBox;
            CheckBox checkBoxDatatableCSV = ctp.Control.Controls["checkBoxDatatableCSV"] as CheckBox;
            CheckBox checkBoxInputs = ctp.Control.Controls["checkBoxInputs"] as CheckBox;
            TextBox textBoxParam = ctp.Control.Controls["textBoxParam"] as TextBox;
            if (checkBoxNoBrowser.Checked) runOptions += " -n";
            if (checkBoxReport.Checked) runOptions += " -r";
            if (checkBoxTurbo.Checked) runOptions += " -t";
            if (checkBoxQuiet.Checked) runOptions += " -q";
            if (checkBoxMSEdge.Checked) runOptions += " -e";
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
        Process process;
        public static StreamWriter myStreamWriter;
        public static Form FormLiveInline;
        public static TextBox textBox2;
        public static TextBox textBox1;
        public static String finalString = "";
        public static FormLiveInline fl;
        public static bool live = false;
        private void RunCommand(string tagFilePath, string runOptions, bool deploy)
        {

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            TextBox textBoxOutput = ctp.Control.Controls["textBoxOutput"] as TextBox;
            textBoxOutput.Clear();
            string workingFolder = GetWorkingFolderPath(deploy);
            string appDataFolder = Environment.GetFolderPath(Environment.SpecialFolder.ApplicationData);
            string taguiSrcPathTempString = appDataFolder + @"\TagUI\textBoxTaguiSrcPath.txt";
            string setTaguiPath = "";
            if (File.Exists(taguiSrcPathTempString))
            {
                string taguiSrcPath = File.ReadAllText(taguiSrcPathTempString);
                setTaguiPath = "set path=" + taguiSrcPath + ";%path% & ";
            }
            string cmdCommand = @"/C " + setTaguiPath + "end_processes & cd \"" + workingFolder + "\" & tagui \"" + tagFilePath + "\"" + runOptions;
            try
            {
                process = new Process
                {
                    StartInfo = new ProcessStartInfo
                    {
                        FileName = "cmd.exe",
                        Arguments = cmdCommand,
                        UseShellExecute = false,
                        RedirectStandardInput = true,
                        RedirectStandardOutput = true,
                        RedirectStandardError = true,
                        CreateNoWindow = true,
                    }
                };
                process.SynchronizingObject = textBoxOutput;
                process.EnableRaisingEvents = true;
                process.Start();
                myStreamWriter = process.StandardInput;
                process.BeginOutputReadLine();
                process.BeginErrorReadLine();
                process.OutputDataReceived += new DataReceivedEventHandler((_sender, _e) =>
                {
                    if (!String.IsNullOrEmpty(_e.Data) && live == false)
                    {
                        textBoxOutput.AppendText("» " + _e.Data.TrimStart() + Environment.NewLine);
                        if (_e.Data == "LIVE MODE - type done to quit")
                        {
                            fl = new FormLiveInline();
                            fl.Show();
                            live = true;
                            finalString = "";
                            TextBox textBox1 = fl.Controls["textBoxLiveInlineOutput"] as TextBox;
                            textBox1.Text = "";
                            TextBox textBox2 = fl.Controls["textBoxLiveInlineInput"] as TextBox;
                            textBox2.Text = "";
                        }
                    }
                    if (!String.IsNullOrEmpty(_e.Data) && live == true)
                    {
                        TextBox textBox1 = fl.Controls["textBoxLiveInlineOutput"] as TextBox;
                        textBox1.AppendText("» " + _e.Data.ToString().TrimStart() + Environment.NewLine);
                        if (_e.Data != "LIVE MODE - type done to quit")
                        {
                            textBoxOutput.AppendText("» " + _e.Data.ToString() + Environment.NewLine);
                            finalString += "// " + _e.Data.ToString().TrimStart() + "\n";
                        }
                    }
                });
                string errorMessage = "";
                process.ErrorDataReceived += new DataReceivedEventHandler((_sender, _e) =>
                {

                    if (!String.IsNullOrEmpty(_e.Data))
                    {
                        errorMessage += _e.Data + "\n";
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
                        buttonRun.Image = Properties.Resources.Run;
                        buttonRun.Label = "Run";
                    }
                    if (errorMessage != "")
                    {
                        string displayErrorMessage = "";
                        string[] errorArr = errorMessage.Split('\n');
                        string firstTwoErrorMessages = errorArr[0] + errorArr[1];
                        if (firstTwoErrorMessages == "'tagui' is not recognized as an internal or external command,operable program or batch file.") displayErrorMessage = "Incomplete TagUI installation. Go to Settings in ribbon to set your TagUI SRC Path.";
                        else if (errorArr.Length > 2) displayErrorMessage = errorMessage;
                        textBoxOutput.AppendText(displayErrorMessage + Environment.NewLine);
                    }
                };
            }
            catch (Exception e)
            {
                textBoxOutput.AppendText(e.Message);
            }
        }
        public static void OnButtonSendClick(object sender, EventArgs e)
        {
            TextBox textBox2 = fl.Controls["textboxLiveInlineInput"] as TextBox;
            string inputText = textBox2.Text;
            myStreamWriter.WriteLine(inputText);
            
            TextBox textBox1 = fl.Controls["textBoxLiveInlineOutput"] as TextBox;
            textBox1.AppendText(inputText + Environment.NewLine);
            finalString += inputText + "\n";
            if (inputText.ToLower() == "done")
            {
                live = false;
                fl.Close();
            }
            else textBox2.Text = "";
        }
        public static void OnButtonCopySelectedClick(object sender, EventArgs e)
        {
            TextBox textBox1 = fl.Controls["textBoxLiveInlineOutput"] as TextBox;
            try
            {
                Clipboard.SetText(textBox1.SelectedText);
            }
            catch { }
        }
        public static void OnButtonAllClick(object sender, EventArgs e)
        {
            Clipboard.SetText(finalString);
            fl.Close();
        }
        private void ProcessAllWordFiles(bool deploy)
        {
            int index = 0;
            string mainFlowWordCopyFilePath = GetWordCopyFilePath(index);
            CreateMainFlowWordCopy(mainFlowWordCopyFilePath);
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

        public int indentCount = 0;
        public float firstIndent = 0;
        private void ProcessSubFlows(string originalDocFilePath, string duplicateDocFilePath, bool deploy)
        {
            WordprocessingDocument duplicateDoc = WordprocessingDocument.Open(duplicateDocFilePath, true);
            var paragraphs = duplicateDoc.MainDocumentPart.RootElement.Descendants<DocumentFormat.OpenXml.Wordprocessing.Paragraph>();
            foreach (DocumentFormat.OpenXml.Wordprocessing.Paragraph paragraph in paragraphs)
            {
                string firstWord = paragraph.InnerText.ToLower().TrimStart().Split(' ')[0];
                if (firstWord == "tagui")
                {
                    subFlowCount++;
                    subFlowTotal++;
                    string subflowFilePath = "";
                    string[] arr = paragraph.InnerText.ToLower().TrimStart().Split(' ');
                    for (int i = 1; i< arr.Length; i++)
                    {
                        subflowFilePath += arr[i] + " ";
                    }
                    string subflowAbsFilePath = subflowFilePath;
                    if (!subflowFilePath.Contains(":")) { subflowAbsFilePath = GetFlowFolderPath(originalDocFilePath) + subflowFilePath; }
                    foreach (Run run in paragraph.Descendants<Run>())
                    {
                        run.RemoveAllChildren<Text>();
                    }
                    Run newRun = paragraph.AppendChild(new Run());
                    string workingFolder = GetWorkingFolderPath(deploy);
                    newRun.AppendChild(new Text("tagui " + workingFolder + "WorkFlow" + subFlowTotal + ".tag"));
                    subFlowFilePaths.Add(subflowAbsFilePath);
                }
                if (firstWord == "if" || firstWord == "for" || firstWord == "break" || firstWord == "continue" || firstWord == "break;" || firstWord == "continue;")
                {
                    string sentence = paragraph.InnerText.TrimStart();
                    
                    string finalSentence = firstWord.ToLower() + " " + string.Join(" ", sentence.Split(' ').Skip(1));
                    
                    foreach (Run run in paragraph.Descendants<Run>())
                    {
                        run.RemoveAllChildren<Text>();
                    }
                    Run newRun = paragraph.AppendChild(new Run());
                    newRun.AppendChild(new Text(finalSentence));
                }
                try
                {
                    if (paragraph.ParagraphProperties.Indentation is object)
                    {
                        indentCount++;
                        int paraIndentCount = 0;
                        float indentValue = float.Parse(paragraph.ParagraphProperties.Indentation.FirstLine);
                        if (indentCount == 1)
                        {
                            firstIndent = indentValue;
                        }
                        paraIndentCount = (int)Math.Round(indentValue / firstIndent);

                        paragraph.ParagraphProperties.Indentation.Remove();
                        string paraText = paragraph.InnerText;
                        foreach (var e in paragraph.Descendants<Text>().First().ElementsBefore())
                        {
                            if (e.LocalName == "tab") paraIndentCount++;
                        }
                        foreach (Run run in paragraph.Descendants<Run>())
                        {
                            run.RemoveAllChildren();
                        }
                        Run newRun = paragraph.AppendChild(new Run());
                        for (int i = 0; i < paraIndentCount; i++)
                        {
                            newRun.AppendChild(new TabChar());
                        }
                        newRun.AppendChild(new Text(paraText));
                    }
                }
                catch { }
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
            fs.Dispose();
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
            return Globals.ThisAddIn.Application.ActiveDocument.FullName;
        }
        private void CreateSubFlowWordCopy(string originalDocFilePath, string duplicateDocFilePath)
        {
            File.Copy(originalDocFilePath, duplicateDocFilePath, true);
        }
        private string GetFlowFolderPath(string flowFilePath)
        {
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

        private void CreateMainFlowWordCopy(string wordCopyFilePath)
        {
            Globals.ThisAddIn.Application.ActiveDocument.Range(
            Globals.ThisAddIn.Application.ActiveDocument.Content.Start,
            Globals.ThisAddIn.Application.ActiveDocument.Content.End).Select();
            Globals.ThisAddIn.Application.Selection.Copy();
            Word.Application wordApp = new Word.Application();
            wordApp.DisplayAlerts = WdAlertLevel.wdAlertsNone;
            Word.Document newDoc = null;
            newDoc = wordApp.Documents.Add();
            newDoc.Content.Paste();
            newDoc.SaveAs(wordCopyFilePath, Type.Missing, Type.Missing, Type.Missing, false,
                    Type.Missing, Type.Missing, Type.Missing,
                    Type.Missing, Type.Missing, Type.Missing,
                    Type.Missing, Type.Missing, Type.Missing,
                    Type.Missing, Type.Missing);
            newDoc.Close();
            wordApp.Quit();
        }
        private string GetImageFolderPath(bool deploy)
        {
            string imageFolderPath;
            if (deploy||DocIsSaved())
            {
                imageFolderPath = GetSavedFolderPath() + "Images" + "\\";
            }
            else
            {
                string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
                imageFolderPath = appDataFolder + "Images" + "\\";
            }
            Directory.CreateDirectory(imageFolderPath);
            return imageFolderPath;
        }

        private void GenerateCsvFile(string excelFilePath, string csvFilePath, string sheetName)
        {
            Excel.Application excelApp = new Excel.Application();
            excelApp.DisplayAlerts = false;
            Excel.Workbook workbook = excelApp.Workbooks.Open(excelFilePath);
            excelApp.Visible = false;
            workbook.Worksheets[sheetName].Select();
            workbook.SaveAs(string.Format(csvFilePath), Excel.XlFileFormat.xlCSV, Excel.XlSaveAsAccessMode.xlNoChange);
            workbook.Close(false);
            excelApp.Quit();
        }
        private bool ValidateExcelCheckBoxFields(CheckBox checkBox)
        {

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            bool isValidated = false;
            string filePath = "";
            string sheetName = "";
            string type = "";
            if (checkBox.Name == "checkBoxDatatableCSV")
            {
                type = "Datatable";

                TextBox textBoxDatatableCSV = ctp.Control.Controls["textBoxDatatableCSV"] as TextBox;
                filePath = textBoxDatatableCSV.Text;
                ComboBox comboBoxDatatableWs = ctp.Control.Controls["comboBoxDatatableWs"] as ComboBox;
                sheetName = comboBoxDatatableWs.Text;
            }
            if (checkBox.Name == "checkBoxObjRepo")
            {
                type = "Object Repository";
                TextBox textBoxObjRepo = ctp.Control.Controls["textBoxObjRepo"] as TextBox;
                filePath = textBoxObjRepo.Text;
                ComboBox comboBoxObjRepo = ctp.Control.Controls["comboBoxObjRepo"] as ComboBox;
                sheetName = comboBoxObjRepo.Text;
            }
            try
            {
                string fileType = GetExcelFileType(filePath);
                if (fileType == "xlsx")
                {
                        Excel.Application excelApp = new Excel.Application();
                        excelApp.DisplayAlerts = false;
                        excelApp.Visible = false;
                        try
                        {
                            Excel.Workbook workbook = excelApp.Workbooks.Open(filePath);
                            Excel.Worksheet worksheet = workbook.Worksheets[sheetName];
                            worksheet.Select();
                            try
                            {

                            TextBox textBoxRange = ctp.Control.Controls["textBoxRange"] as TextBox;
                                string range = textBoxRange.Text;
                                string rangeStart = "";
                                string rangeEnd = "";
                                if (range != "" && range != "Optional range")
                                {
                                    rangeStart = range.Split(':')[0];
                                    rangeEnd = range.Split(':')[1];
                                    worksheet.get_Range(rangeStart, rangeEnd).Select();
                                }
                            }
                            catch (Exception ex)
                            {
                                workbook.Close(false);
                                ErrorDialog f1 = new ErrorDialog();
                                f1.Show("Error", ex.Message, ex.ToString());
                            return false;
                            }
                            workbook.Close(false);
                    }
                        catch (Exception ex)
                        {
                            excelApp.Quit();
                            ErrorDialog f1 = new ErrorDialog();
                            f1.Show("Error", ex.Message, ex.ToString());
                            return false;
                        }
                    excelApp.Quit();
                }
                else
                {
                    try
                    {
                        using (StreamReader sr = new StreamReader(filePath))
                        {
                            //
                        }
                    }
                    catch (Exception ex)
                    {
                        ErrorDialog f1 = new ErrorDialog();
                        f1.Show("Error", ex.Message, ex.ToString());
                        return false;
                    }
                }
                isValidated = true;
            }
            catch (Exception ex)
            {
                ErrorDialog f1 = new ErrorDialog();
                f1.Show("Error", ex.Message, ex.ToString());
                return false;
            }
            return isValidated;
        }
        private string ProcessDataTable(bool deploy)
        {

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            TextBox textBoxDatatableCSV = ctp.Control.Controls["textBoxDatatableCSV"] as TextBox;
            ComboBox comboBoxDatatableWs = ctp.Control.Controls["comboBoxDatatableWs"] as ComboBox;
            TextBox textBoxRange = ctp.Control.Controls["textBoxRange"] as TextBox;
            string csvFilePath = GetWorkingFolderPath(deploy) + "DataTable.csv";
            string dataTableFilePath = textBoxDatatableCSV.Text;
            string fileType = GetExcelFileType(dataTableFilePath);
            if (fileType == "xlsx")
            {
                string dataTableSheet = comboBoxDatatableWs.Text;
                string range = textBoxRange.Text;
                string rangeStart = "";
                string rangeEnd = "";
                if (range != "" && range != "Optional range")
                {
                    rangeStart = range.Split(':')[0];
                    rangeEnd = range.Split(':')[1];
                    Excel.Application excelApp = new Excel.Application();
                    excelApp.DisplayAlerts = false;
                    excelApp.Visible = false;
                        Excel.Workbook workbook = excelApp.Workbooks.Open(dataTableFilePath);
                        Excel.Worksheet worksheet = workbook.Worksheets[dataTableSheet];
                        Excel.Workbook workbook2 = excelApp.Workbooks.Add();
                        Excel.Worksheet worksheet2 = workbook2.Worksheets.get_Item(1);
                        Excel.Range sourceRange = worksheet.get_Range(rangeStart, rangeEnd);
                        Excel.Range destinationRange = worksheet2.Range["A1"];
                        sourceRange.Copy(destinationRange);
                        workbook2.Worksheets.get_Item(1).Select();
                        workbook2.SaveAs(string.Format(csvFilePath), Excel.XlFileFormat.xlCSV, Excel.XlSaveAsAccessMode.xlNoChange);
                        workbook.Close();
                        workbook2.Close();
                    excelApp.Quit();
                }
                else
                {
                    GenerateCsvFile(dataTableFilePath, csvFilePath, dataTableSheet);
                }
                
            }
            else
            {
                if (File.Exists(csvFilePath))
                {
                    File.Delete(csvFilePath);
                }
                File.Copy(dataTableFilePath, csvFilePath);
            }
            csvFilePath = "DataTable.csv";
            return csvFilePath;
        }

        private string GetSavedFolderPath()
        {
            string fileLoc = Globals.ThisAddIn.Application.ActiveDocument.FullName;
            string folderPath = fileLoc.Substring(0, fileLoc.Length - 5) + "\\";
            Directory.CreateDirectory(folderPath);
            return folderPath;
        }
        private string GetAppDataFolderPath()
        {
            string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
            Directory.CreateDirectory(appDataFolder);
            return appDataFolder;
        }

        private string ProcessObjRepo(bool deploy)
        {

            DocumentProperties prps;
            prps = (DocumentProperties)Globals.ThisAddIn.Application.ActiveDocument.CustomDocumentProperties;
            CustomTaskPane ctp = Globals.ThisAddIn.CustomTaskPanes.Where(x => x.Control.Name == prps["customTaskPaneName"].Value.ToString()).FirstOrDefault();

            TextBox textBoxObjRepo = ctp.Control.Controls["textBoxObjRepo"] as TextBox;
            ComboBox comboBoxObjRepo = ctp.Control.Controls["comboBoxObjRepo"] as ComboBox;
            string csvFilePath = "";
            string objRepoFilePath = textBoxObjRepo.Text;
            string fileType = GetExcelFileType(objRepoFilePath);
            if (fileType == "xlsx")
            {
                string objRepoSheet = comboBoxObjRepo.Text;
                csvFilePath = GetWorkingFolderPath(deploy) + "tagui_local.csv";
                GenerateCsvFile(objRepoFilePath, csvFilePath, objRepoSheet);
            }
            else
            {
                csvFilePath = objRepoFilePath;
            }
            return csvFilePath;
        }
        private string GetWorkingFolderPath(bool deploy)
        {
            string workingFolder = "";
            if (deploy||DocIsSaved())
            {
                workingFolder = GetSavedFolderPath();
            }
            else
            {
                workingFolder = GetAppDataFolderPath();
            }
            return workingFolder;
        }

        private string GetExcelFileType(string filePath)
        {
            string fileType = "";
            if (filePath.Substring(filePath.Length - 4, 4) == "xlsx")
            {
                fileType = "xlsx";
            }
            else fileType = "csv";
            return fileType;
        }
    }
}