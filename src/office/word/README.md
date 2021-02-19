# TagUI Word AddIn (Beta)
**The TagUI Word AddIn (Beta) is part of [TagUI](https://github.com/kelaberetiv/TagUI/)'s upcoming Office RPA suite which enable users to run and deploy RPA work flows from within a commonly used platform such as Microsoft Word.**

---
## Requirements
This Word AddIn requires the following prerequisites:
1. TagUI [(see installation steps)](https://tagui.readthedocs.io/en/latest/setup.html)
2. Microsoft Word 2010 (or later) x64-bit
3. Microsoft .NET Framework 4.72
4. VSTO Runtime >="10.0.30319" or Office Runtime >="10.0.21022"

## Installation
Double click the setup.exe file and follow the installation guide. After installing, open Microsoft Word, and the TagUI Word AddIn task pane will be displayed on the right side of the document.

## Running a Work Flow

To run a TagUI work flow, select the Run Options of your choice and click the Run button.

- Note: The work flow will be based on the TagUI script in the document. You may run a work flow with or without saving the document. If the document is saved, a .tag file will be generated in the same folder with the same file name as the saved document. This .tag file will reflect the TagUI script of the last ran work flow.


## Deploying a Work Flow
To deploy a Work Flow, select the Run Options of your choice and click the Deploy button.

- Note:
The document must be saved before deploying a work flow. The work flow will be based on the TagUI script in the document. Upon deployment, a .tag file and .exe file will be generated in the same folder with the same file name as the saved document.
The deployed work flow (.exe file) takes reference from the .tag file when running. To run the work flow, double click on the .exe file.

## Troubleshooting
- If the work flow does not run or deploy, check if TagUI has been successfully installed (see prerequisite 1 in Requirements section above).
- If the task pane does not show upon opening Microsoft Word, check if Microsoft VSTO prerequisites are met (see prerequisites 2-3 in Requirements section above).
- Please raise a support ticket [here](https://github.com/kelaberetiv/TagUI/issues) for any other issues related to TagUI/TagUI Word Addin.

## Maintainers
This project is supported by the National Research Foundation, Singapore under its AI Singapore Programme (AISG-RP-2019-050). Any opinions, findings and conclusions or recommendations expressed in this material are those of the author(s) and do not reflect the views of National Research Foundation, Singapore.