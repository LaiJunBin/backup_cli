Namespace Commands
    Public Class Upload
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("upload", "上傳檔案").
                        AddArgument("filename",
                            Sub(args As Dictionary(Of String, String))
                                If Not My.Computer.FileSystem.FileExists(args("filename")) Then
                                    Console.WriteLine("file not found.")
                                    End
                                End If
                                Request.Payload.Set("filename", args("filename"))
                                Request.Payload.Set("raw_data", Functions.ConvertFileToBase64(args("filename")))
                                Request.Active = True
                                Request.Method = Request.Methods.Method_POST
                                Request.MustSuper = True
                            End Sub, "上傳的檔案名稱").
                        AddArgument("rename",
                            Sub(args As Dictionary(Of String, String))
                                Request.Payload.Set("rename", args("rename"))
                            End Sub, "遠端存檔的檔案名稱").
                        AddOption("--force",
                            Sub()
                                Request.Payload.Set("force", True)
                            End Sub, "若遠端檔案存在會覆蓋上去").
                        AddOption("--private",
                            Sub()
                                Request.Payload.Set("path", "private/")
                            End Sub, "將檔案上傳到私有空間")

        End Function
    End Class
End Namespace
