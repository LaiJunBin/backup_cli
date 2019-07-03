Namespace Commands
    Public Class Delete
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("delete", "刪除遠端檔案").
                        AddArgument("filename",
                            Sub(args As Dictionary(Of String, String))
                                Request.SetURI("delete")
                                Request.Payload.Set("filename", args("filename"))
                                Request.Method = Request.Methods.Method_POST
                                Request.MustSuper = True
                            End Sub, "刪除的檔案名稱").
                            AddOption("--private",
                                Sub()
                                    Request.Payload.Set("path", "private/")
                                End Sub, "刪除private的檔案")
        End Function
    End Class
End Namespace
