using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Drawing.Imaging;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using Word = Microsoft.Office.Interop.Word;

namespace TagUIWordAddIn
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }
        private void Form1_Load(object sender, EventArgs e)
        {
            this.FormBorderStyle = FormBorderStyle.None;
            this.TopMost = true;
            this.Cursor = Cursors.Cross;
        }
        
        //public int startX, startY, endX, endY;
        Point selPoint, p;
        Rectangle mRect;

        private void Form1_MouseMove(object sender, MouseEventArgs e)
        {
            if (e.Button == MouseButtons.Left)
            {
                p = e.Location;
                int x = Math.Min(selPoint.X, p.X);
                int y = Math.Min(selPoint.Y, p.Y);
                int w = Math.Abs(p.X - selPoint.X);
                int h = Math.Abs(p.Y - selPoint.Y);
                mRect = new Rectangle(x, y, w, h);
                this.Invalidate();
            }
        }

        private void Form1_Paint(object sender, PaintEventArgs e)
        {
            e.Graphics.DrawLine(Pens.Gray, p.X, selPoint.Y, selPoint.X, p.Y);
            e.Graphics.DrawLine(Pens.Gray, selPoint.X, selPoint.Y, p.X, p.Y);
            e.Graphics.DrawRectangle(Pens.Gray, mRect);
        }
        private void Form1_MouseDown(object sender, MouseEventArgs e)
        {
            selPoint = e.Location;
        }
        private void Form1_MouseUp(object sender, MouseEventArgs e)
        {
            this.Opacity = 0.0;
            CaptureMyScreen(mRect.X, mRect.Y, mRect.Width, mRect.Height);
            this.Close();
        }

        private void Form1_FormClosed(object sender, FormClosedEventArgs e)
        {

        }

        private void CaptureMyScreen(int startX, int startY, int width, int height)
        {
            try
            {
                string appDataFolder = Environment.GetEnvironmentVariable("USERPROFILE") + @"\AppData\Roaming\TagUI\";
                string tmpFile = appDataFolder + "Capture.png";
                Directory.CreateDirectory(appDataFolder);
                Bitmap captureBitmap = new Bitmap(width, height);
                Rectangle captureRectangle = new Rectangle(startX, startY, width, height);
                Graphics captureGraphics = Graphics.FromImage(captureBitmap);
                captureGraphics.CopyFromScreen(captureRectangle.Left, captureRectangle.Top, 0, 0, captureRectangle.Size);
                captureBitmap.Save(tmpFile, ImageFormat.Png);
                Word.Range rng = Globals.ThisAddIn.Application.Selection.Range;
                rng.Delete();
                var pic = rng.InlineShapes.AddPicture(tmpFile, false, true).ConvertToShape();
                pic.WrapFormat.Type = Word.WdWrapType.wdWrapInline;
                captureBitmap.Dispose();
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }
    }
}
