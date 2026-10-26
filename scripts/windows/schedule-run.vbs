' Lance « php artisan schedule:run » sans fenêtre (Planificateur de tâches Windows, toutes les minutes).
' Équivalent local du cron serveur : * * * * * cd /chemin/du/projet && php artisan schedule:run >> /dev/null 2>&1
Set fso = CreateObject("Scripting.FileSystemObject")
projectDir = fso.GetParentFolderName(fso.GetParentFolderName(fso.GetParentFolderName(WScript.ScriptFullName)))
phpExe = "C:\laragon\bin\php\php-8.3.29-nts-Win32-vs16-x64\php.exe"

Set shell = CreateObject("WScript.Shell")
shell.CurrentDirectory = projectDir
shell.Run """" & phpExe & """ artisan schedule:run", 0, True
