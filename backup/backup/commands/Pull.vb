Namespace Commands
    Public Class Pull
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("pull", "從伺服器相對位置下載專案").
                    AddArgument("directory",
                        Sub(args As Dictionary(Of String, String))
                            Request.Payload.Set("directory", args("directory"))
                            Request.SetURI("pull")
                            Request.Method = Request.Methods.Method_POST
                            Request.MustSuper = True
                            Dim Response As String = Request.Run(False)
                            Dim Files() As String = Response.Split({",@%@"}, StringSplitOptions.RemoveEmptyEntries)
                            For Each File In Files
                                Request.Pull(File)
                            Next
                            End
                        End Sub, "要從伺服器下載的資料夾")
        End Function
    End Class
End Namespace
