Namespace Commands
    Public Class Download
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Dim force As Boolean = False
            Dim filename As String = ""
            Dim path As String = "public"
            Dim rename As String = ""
            Return New Command("download", "下載").
                            AddArgument("filename",
                                Sub(args As Dictionary(Of String, String))
                                    Request.Active = False
                                    filename = args("filename")
                                    Request.Actions.Add(
                                        Sub()
                                            Request.DownloadFile(filename, path, filename, force)
                                        End Sub)
                                End Sub, "要下載的檔案名稱").
                            AddArgument("rename",
                                Sub(args As Dictionary(Of String, String))
                                    rename = args("rename")
                                    Request.Actions.Clear()
                                    Request.Actions.Add(
                                        Sub()
                                            Request.DownloadFile(filename, path, rename, force)
                                        End Sub)
                                End Sub, "下載完成的檔案名稱").
                            AddOption("--force",
                                Sub()
                                    force = True
                                End Sub, "若有同名的檔案會覆蓋").
                            AddOption("--private",
                                Sub()
                                    path = "private"
                                End Sub, "下載private的檔案")

        End Function
    End Class
End Namespace
