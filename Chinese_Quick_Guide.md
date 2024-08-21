# 实战教程

https://www.rpa-sg.org/TagUI-Tutorial-1/tagui-tutorial-1-goodreads-com-part-1.php

# 如何编程

- **编程**的时候用VsCode

  因为有很多插件可用,编程环境更好,界面也更舒服,然后我安装了支持TagUI的插件,然后还有Copilot版面

  而且VsCode里面,可以直接使用tagui live

  而且界面切换也更方便

  建议一直用这个也行

- 简单的**查看**程序可以**用NotePad++**

  但是不推荐,我想的是如果只是看看,可以用notepad++查看.  但是编程还是VsCode

- **使用用**-d生成的**cmd文件**

  最后-deploy,用cmd运行
  方便日常使用

# TagUI功能

1. tagui rpa.tag

   运行tag文件

2. tagui rpa.tag -deploy(-d)

   生成cmd文件

3. tagui rpa.tag -headless(-h)

   隐藏浏览器运行
   如果流程中使用可视自动化（例如OCR、图像识别等），则无法正常运行，因为流程需要读取并单击屏幕上的内容

4. tagui http://www.TagUI.com.cn/baidu.tag

   运行云端tag

# TagUI命令

## 常见TagUI命令

### web access

just type the link of the web

https://www.R-P-A.com

### click left

顺便讲讲定位方法,其他的定位方法和这个一样,以click举例
定位很关键,你要告诉RPA你要操作的是哪里

- 根据页面名定位

  click Getting Started

- 用xpath定位

  click //a[@class="icon icon-home"]

  xpath教程:https://www.w3schools.com/xml/xpath_intro.asp

  对元素右键>检查>源代码里面复制(选择XPath复制)

  ID,name,class,title都会被用来定位,
  但是很显然一个元素不一定都有这些
  但是我们不用管,知道咋用即可

- 根据坐标定位

  click (500,300)

  我们可以Snipaste F1看它的坐标

- 根据图片定位

  click image/button.png

  把我们的image都放在新建的一个image文件夹下

### type text

- type some-input as some-text

  some-input is the location of element

  some-text is the input text content

- type some-input as [clear]some-text[enter]

  你可以用 [clear] 来清除元素文本，用 [enter] 来按回车
  clear就是退格键

  为什么不是some-text [enter]?
  因为这样会some-text后空格再回车

- type some-input.png as some-text

  对于这里的输入位置,也可以使用图片来定位
  定位都是和click的原理一样

### read

- read some-element to some-variable
  “some-element” 是元素， “some-variable” 是变量

- read (300,400)-(500,550) to some-variable

  **read** 可以使用Visual Automation功能从屏幕区域读取文本。依赖于Visual Automation的读取可能不完全准确。

  **此命令读取在点（300,400）和（500,550）之间形成的矩形中的所有文本**

- read //some-element/@some-attribute to some-variable

  同样地,只要和定位有关就可以用xpath

### assign

row_count = count('row')

变量名=变量值

### echo



echo "title = " + title

https://www.goodreads.com/list/show/118408.New_York_Times_2017_Ten_Best
read /html/body/div[2]/div[3]/div[1]/div[2]/div[3]/div[1]/a to title
echo "------------------The Title is:" + `title`

Output the title to the command window using the [**echo**](https://www.rpa-sg.org/TagUI-Commands/echo.php) command

要用``包住title,这样它才是变量,不然就是字符串title

## 测试&开发模式

这个模式在cmd开启

tagui live

在流程开发中，经常需要测试下一步的操作代码，建议使用Live Mode

启动 Live 模式后，您可以输入一行才买，执行一行，这样调试起来很方便。下面这张图展示了这个操作。进入 Live Mode 时，TagUI 会自动打开Chrome

## 元素定位总结复习

因为这部分真的很重要.告诉TagUI需要操作的目标元素在哪里。
命令中**可以使用多重元素定位**方式

### DOM元素属性定位

click Getting Started

### XPath页面路径定位

click //body/div[1]/nav/div/div[1]/a

click /html/body/div[2]/div[3]/div[1]/div[2]/div[3]/div[1]/a

```md
XPath（XML Path Language）用树状结构来定位元素，表达方式可以理解成“某小区66号楼601室书房里桌子左边的第一个抽屉”。
```

### Point坐标点定位

click (200,300)
使用了TagUi的视觉自动化功能:visual automation

### 屏幕区域定位

read (300,400)-(500,550) to some-variable

visual automation

### 图像定位

click button.png

在全屏范围寻找类似图像文件 `button.png` 的位置进行点击。您首先需要截图保存为 `button.png`， 这将使用TagUI的视觉自动化功能 *visual automation*

### XPath

https://www.zyte.com/blog/an-introduction-to-xpath-with-examples/

- Xpath is a query language that is used for navigating through an XML document.(它是一种用于浏览 XML 文档的查询语言。)

- An HTML web page is also an XML document.

- So we can use XPath to locate the element

- 可以使用XPath  Helper帮忙

  写程序前先验证定位对不对
  我现在发现XPath比图片方便多了,不需要保存图片在本地,只需要一串XPath即可,然后也好获取

  这样不用TagUI我们就知道能不能拿了,然后比如一个列表,它可能中间有一个是li[1],就是其中一个,我们改为li,就是全部获取了
  然后我们也能在界面看到是不是拿到了,非常好用!
  


## If语句

```
if url() contains "success"
  click button1.png
  click button2.png
```

如果 URL网址包含 “success”, 就点击 button1 和 button2
`url()` 是一个 [函数](http://www.tagui.com.cn/main_concepts.html#helper-functions)，用于获取当前网页的网址URL



```
if !exist('some-element')
  https://tagui.readthedocs.io/
```

“如果在等待超时后未出现某些元素，则访问此网页”。
因为判断截至时间是等待超时前



```
if row_count equals to 5
  some steps
```

```
if row_count more than 5
  some steps
```

```
if row_count less than 5
  some steps
```



## For循环

```
for n from 1 to 20
  some step to take
  some other step
  some more step
```

你可以在同一流程中使用循环执行多次同样的流程。为了使用不同变量多次运行一个流程，标准方式是使用 [datatables](http://www.tagui.com.cn/advanced.html#datatables).

DataTables是 [csv 文件](http://www.tagui.com.cn/faq.html#what-are-csv-files)，可以把数据表格作为变化，多次循环运行流程。

Datatable数据文件 (`trade_data.csv`) 类似下面格式:

| #    | trade        | username     | password | pair   | size   | direction |
| ---- | ------------ | ------------ | -------- | ------ | ------ | --------- |
| 1    | Trade USDSGD | test_account | 12345678 | USDSGD | 10000  | BUY       |
| 2    | Trade USDSGD | test_account | 12345678 | USDJPY | 1000   | SELL      |
| 3    | Trade EURUSD | test_account | 12345678 | EURUSD | 100000 | BUY       |

要使用它，您可以使用`tagui my_flow.tag trade_data.csv`。TagUI会使用 `my_flow.tag`运行您的流程。 TagUI将用DataTable中的每一行（除标题行）运行 my_flow.tag 一次。

在流程运行中，TagUI会用到变量 `trade`, `username`, `password`，就像它们处于本地对象存储库中，并且值将来自该运行的行。

To know which iteration your flow is in you can use the `iteration` variable:

```
echo current iteration: `iteration`
if iteration equals to 1
  // go to login URL and do the login steps
  www.xero.com

// do rest of the steps for every iteration
```

## function函数

函数功能是调用 JavaScript 函数，并且返回需要的值
函数后面记得接(),完整的函数列表如下:http://www.tagui.com.cn/reference.html#helper-functions-reference

常见函数如下

### csv_row()

将一些变量转换为CSV文本以写入CSV文件。它将变量作为参数输入，包含在方括号内 `[]` (实际上是一个数组 array).

```
read name_element to name
read price_element to price
read details_element to details
write `csv_row([name, price, details])` to product_list.csv
```

### clipboard() 剪贴板

从剪贴板读取文本：

```
dclick pdf_document.png
wait 3 seconds
keyboard [ctrl]a
keyboard [ctrl]c
text_contents = clipboard()
```

对于大量文本内容输入，建议将文本先放到剪贴板，在用 Ctrl+V 粘贴，这比用 **type** 快很多：

```
long_text = "This is a very long text which takes a long time to type"
clipboard(long_text)
click text_input
keyboard [ctrl]v
keyboard [enter]
```

### mouse_x(), mouse_y() 获得坐标点当前位置

获取鼠标的x或y坐标。

在偏移点击目标时，需要先获得 x 或 y 坐标，以下代码示例表示，鼠标先移动到 `element.png` ，再获得偏移的 x 和 y ，然后单击元素右侧的200像素：

```
hover element.png
x = mouse_x() + 200
y = mouse_y()
click (`x`,`y`)
```

### mouse_xy()

在Live模式下，您可以使用 `echo `mouse_xy()`` 找到鼠标的坐标。注意，这里用的不是单引号 `' `，而是键盘数字1左边按键的 ```

```
echo `mouse_xy()`
```

感觉不如直接Snipaste的F1看

# XPath Tutorial

## 什么是XPath

XML的query语言,就是用来定位XML里面的元素的,然后我们的html解析出来就是xml,所以我们XPath就可以定位网页的任何元素. 这个比坐标定位,图片定位稳健得多!(图片定位截图麻烦,然后还有多图片影响. 定位的话放大缩小界面,坐标就不同了)

## XPath语法

三大块:层级&属性&函数
函数,属性都要写在[]里面

所以很显然,一个元素有多重定位语句,一般推荐用带有//*[]开头的
也就是直接先属性匹配

- 层级

  /直接子级时使用,//需要跳级时使用

  read //*[@id="all_votes"]/table/tbody/tr[1]/td[3]/a/span to book1

  比如这个//*[@id="all_votes"],只要是id="all_votes"就匹配
  如果是//div[@id="all_votes"],那div,才id="all_votes"匹配

  其中*表示选择所有,

  我通过跳级选中所有id="all_votes"的这一级

  read /html/body/div[2]/div[3]/div[1]/div[2]/div[3]/div[1]/a to list_title
  这个很显然, /html不需要跳级

  ```md
  在XPath中，“/”表示路径分隔符，它只能匹配当前节点的子节点。而“//”表示路径分隔符，它可以匹配当前节点的子节点、孙子节点、曾孙节点等任意层级的节点。
  ```

  

- 属性

  @属性访问

  //ul[@class="pager pagenav"]

  //*[@id="all_votes"]/table/tbody/tr[1]/td[3]/a/span

- 函数

  eg text(),contains()

  eg //ul[contains(@class,"pager")]
  匹配类名包含pager的ul
