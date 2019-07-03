Namespace Commands
    Public Class Publish
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Dim Command As Command = New Command("publish", "發布網頁專案到伺服器上").
                AddArgument("file",
                    Sub()
                        Request.Actions.Add(
                            Sub()
                                Console.WriteLine("missing path arguments.")
                                End
                            End Sub)
                    End Sub, "要上傳的檔案(.為全選)").
                AddArgument("path",
                    Sub(args As Dictionary(Of String, String))
                        Request.Actions.Clear()
                        Request.SuperActions.Add(
                            Sub()
                                Request.SetURI("publish")
                                Request.Method = Request.Methods.Method_POST
                                Request.Payload.Set("publish_path", args("path"))
                                Dim Files As New List(Of String)({args("file")})
                                Dim Temp() As String = args("file").Split("/")
                                If Temp.Last = "." Then
                                    Files = GetFiles(args("file"), Strings.Left(args("file"), args("file").Length - 1))
                                    'Files = My.Computer.FileSystem.GetFiles(args("file")).ToList
                                    'Files = Files.Select(Function(x) Strings.Left(args("file"), args("file").Length - 1) & My.Computer.FileSystem.GetName(x)).ToList
                                ElseIf Temp.Last = "" Then
                                    Files = GetFiles(args("file"), args("file"))
                                End If

                                For Each File In Files
                                    If Not My.Computer.FileSystem.FileExists(File) Then Console.WriteLine("file " & File & " not found.") : Continue For
                                    Request.Payload.Set("raw_data", Functions.ConvertFileToBase64(File))
                                    Request.Payload.Set("filename", File)
                                    Console.WriteLine(Request.Run(False, False))
                                Next
                            End Sub)
                    End Sub, "上傳到伺服器根目錄下的相對位置").
                AddOption("--force",
                    Sub()
                        Request.Payload.Set("force", True)
                    End Sub, "若遠端檔案存在會覆蓋上去")
            

            Command.AddCommands(
                New Command("list", "顯示伺服器相對位置下的檔案").
                    AddArgument("directory",
                        Sub(args As Dictionary(Of String, String))
                            Request.Payload.Set("directory", args("directory"))
                            Request.SetURI("publish/list")
                            Request.Method = Request.Methods.Method_POST
                            Request.MustSuper = True
                        End Sub, "要列出檔案的資料夾"),
                New Command("clear", "顯示伺服器相對位置下的檔案").
                    AddArgument("directory",
                        Sub(args As Dictionary(Of String, String))
                            Request.Payload.Set("directory", args("directory"))
                            Request.SetURI("publish/clear")
                            Request.Method = Request.Methods.Method_POST
                            Request.MustSuper = True
                        End Sub, "要從伺服器清除的資料夾")
            )

            Return Command
        End Function
        Function GetFiles(ByVal dir As String, ByVal basedir As String) As List(Of String)
            Dim CurrentFiles As New List(Of String)(My.Computer.FileSystem.GetFiles(dir).Select(Function(x) basedir & My.Computer.FileSystem.GetName(x)))
            Dim CurrentDirectories As New List(Of String)(My.Computer.FileSystem.GetDirectories(dir))
            Return CurrentFiles.Concat(CurrentDirectories.SelectMany(Function(x) GetFiles(x, basedir & My.Computer.FileSystem.GetName(x) & "/"))).ToList
        End Function
    End Class
End Namespace
