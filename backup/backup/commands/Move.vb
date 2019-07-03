Namespace Commands
    Public Class Move
        Implements ICommand

        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("move", "移動檔案").
                            AddArgument("filename",
                                Sub(args As Dictionary(Of String, String))
                                    Request.Actions.Add(
                                        Sub()
                                            Console.WriteLine("missing destination arguments.")
                                            End
                                        End Sub)
                                End Sub, "要移動的檔案名稱").
                            AddArgument("destination",
                                Sub(args As Dictionary(Of String, String))
                                    Request.Actions.Clear()
                                    Request.SuperActions.Add(
                                        Sub()
                                            Request.Payload.Add("filename", args("filename"))
                                            Request.Payload.Add("destination", args("destination"))
                                            Request.SetURI("move")
                                            Request.Method = Request.Methods.Method_POST
                                            Request.Run()
                                        End Sub
                                    )
                                End Sub, "要移動到的目的地").
                                AddOption("-d",
                                    Sub()
                                        Request.Payload.Set("dir", True)
                                    End Sub, "移動資料夾")
        End Function
    End Class
End Namespace