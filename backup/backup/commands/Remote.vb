Namespace Commands
    Public Class Remote
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("remote", "跟遠端相關的操作",
                        Sub()
                            Console.WriteLine("current remote: " & My.Settings.remote(0) & vbTab & My.Settings.remote(1))
                            Console.WriteLine("all remotes:")
                            Request.SetURI(String.Format("remote/{0}", My.Settings.username))
                        End Sub).
                        AddCommands(
                            New Command("add", "新增遠端位置").
                            AddArgument("remoteName",
                                Sub(args As Dictionary(Of String, String))
                                    Request.Actions.Add(
                                        Sub()
                                            Console.WriteLine("missing remoteURL arguments.")
                                            End
                                        End Sub)
                                End Sub, "遠端的名稱").
                            AddArgument("remoteURL",
                                Sub(args As Dictionary(Of String, String))
                                    If My.Settings.username = "" OrElse My.Settings.password = "" Then
                                        Console.WriteLine("please configure your username and password first.")
                                        End
                                    End If
                                    Request.Actions.Clear()
                                    Request.SetURI("remote")
                                    Request.Method = Request.Methods.Method_POST
                                    Request.Payload.Set("name", args("remoteName"))
                                    Request.Payload.Set("url", args("remoteURL"))
                                    Request.Payload.Set("username", My.Settings.username)
                                    Request.Payload.Set("password", My.Settings.password)
                                End Sub, "遠端的名稱")
                        )
            

        End Function
    End Class
End Namespace