Namespace Commands
    Public Class Reset
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("reset", "重設設定").
                    AddCommands(
                        New Command("username", "重設username",
                            Sub()
                                My.Settings.username = ""
                                My.Settings.Save()
                                Console.WriteLine("reset username success.")
                            End Sub),
                        New Command("password", "重設password",
                            Sub()
                                My.Settings.password = ""
                                My.Settings.Save()
                                Console.WriteLine("reset password success.")
                            End Sub),
                        New Command("remote", "重設remote",
                            Sub()
                                My.Settings.remote = ""
                                My.Settings.Save()
                                Console.WriteLine("reset remote success.")
                            End Sub),
                        New Command(".", "重設所有欄位(username, password, remote)",
                            Sub()
                                My.Settings.username = ""
                                My.Settings.password = ""
                                My.Settings.remote = ""
                                My.Settings.Save()
                                Console.WriteLine("reset all success.")
                            End Sub)
                    )
        End Function
    End Class
End Namespace
