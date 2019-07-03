Namespace Commands
    Public Class List
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("list", "列出遠端的檔案",
                                Sub()
                                    Request.SetURI("")
                                    Request.Method = Request.Methods.Method_GET
                                End Sub)
        End Function
    End Class
End Namespace
