using Microsoft.Win32;
using System;
using System.Collections;
using System.Collections.Generic;
using System.ComponentModel;
using System.Configuration.Install;

namespace InstallerClassDll
{
    [RunInstaller(true)]
    public partial class InstallerClass : System.Configuration.Install.Installer
    {
        public InstallerClass()
        {
            InitializeComponent();
        }

        [System.Security.Permissions.SecurityPermission(System.Security.Permissions.SecurityAction.Demand)]
        public override void Install(IDictionary stateSaver)
        {
            base.Install(stateSaver);
        }

        [System.Security.Permissions.SecurityPermission(System.Security.Permissions.SecurityAction.Demand)]
        public override void Commit(IDictionary savedState)
        {
            base.Commit(savedState);
            string environmentKey = @"Environment";
            string pathUrl = this.Context.Parameters["targetdir"].Trim('\\') + @"\tagui\src";
            string oldPath = (string)Registry.CurrentUser.CreateSubKey(environmentKey).GetValue("PATH", "", RegistryValueOptions.DoNotExpandEnvironmentNames);
            var index = oldPath.IndexOf(pathUrl);
            if (index < 0)
            {
                Registry.CurrentUser.CreateSubKey(environmentKey).SetValue("PATH", pathUrl + ";" + oldPath, RegistryValueKind.ExpandString);
            }

        }

        [System.Security.Permissions.SecurityPermission(System.Security.Permissions.SecurityAction.Demand)]
        public override void Rollback(IDictionary savedState)
        {
            base.Rollback(savedState);


        }

        [System.Security.Permissions.SecurityPermission(System.Security.Permissions.SecurityAction.Demand)]
        public override void Uninstall(IDictionary savedState)
        {
            base.Uninstall(savedState);
            string environmentKey = "Environment";
            string pathUrl = "";
            string removeString = "";
            string oldPath = (string)Registry.CurrentUser.CreateSubKey(environmentKey).GetValue("Path", "", RegistryValueOptions.DoNotExpandEnvironmentNames);
            string[] pathArr = oldPath.Split(';');
            foreach (string path in pathArr)
            {
                if (path.Contains(@"tagui\src"))
                {
                    pathUrl = path;
                    removeString = path + ";";
                    break;
                }
            }

            var index = oldPath.IndexOf(removeString);
            if (index < 0)
            {
                removeString = pathUrl;
                index = oldPath.IndexOf(removeString);
            }

            if (index > -1)
            {
                oldPath = oldPath.Remove(index, pathUrl.Length);
                Registry.CurrentUser.CreateSubKey(environmentKey).SetValue("Path", oldPath, RegistryValueKind.ExpandString);
            }
        }
    }
}
