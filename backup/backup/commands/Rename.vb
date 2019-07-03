Namespace Commands
    Public Class Rename
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("rename", "修改遠端檔案名稱").
                        AddArgument("old_filename",
                            Sub()
                                Request.Actions.Add(
                                    Sub()
                                        Console.WriteLine("missing new_filename arguments.")
                                        End
                                    End Sub)
                            End Sub, "要修改的檔案名稱").
                        AddArgument("new_filename",
                            Sub(args As Dictionary(Of String, String))
                                Request.Actions.Clear()
                                Request.SetURI("rename")
                                Request.Method = Request.Methods.Method_POST
                                Request.MustSuper = True
                                Request.Payload.Set("new_filename", args("new_filename"))
                                Request.Payload.Set("old_filename", args("old_filename"))
                            End Sub, "新的檔案名稱").
                            AddOption("--private",
                                Sub()
                                    Request.Payload.Set("path", "private/")
                                End Sub, "重新命名private的檔案")
        End Function
    End Class
End Namespace
