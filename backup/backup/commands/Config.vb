Namespace Commands
    Public Class Config
        Implements ICommand
        Public Function CreateInstance() As Command Implements ICommand.CreateInstance
            Return New Command("config", "設定").
                AddCommands(
                    New Command("username", "設定使用者帳號",
                        Sub()
                            Console.Write("please input username: ")
                            Dim username As String = Trim(Console.ReadLine())
                            If username = "" Then Console.WriteLine("username can't be empty.") : End
                            My.Settings.username = username
                            My.Settings.Save()
                            Console.WriteLine("username setting ok.")
                        End Sub).
                    AddOption("--show",
                        Sub()
                            Console.WriteLine("username: " & My.Settings.username)
                        End Sub, "顯示使用者帳號"),
                    New Command("password", "設定使用者密碼",
                        Sub()
                            Dim password As String = Trim(CMD.GetPassword())
                            If password = "" Then Console.WriteLine("password can't be empty.") : End
                            My.Settings.password = password
                            My.Settings.Save()
                            Console.WriteLine("password setting ok.")
                        End Sub),
                    New Command("remote", "設定遠端位置",
                        Sub()
                            Console.Write("please input remote url: ")
                            Dim remote As String = Trim(Console.ReadLine())
                            If Not remote.Last = "/" Then remote &= "/"
                            If remote = "" Then Console.WriteLine("remote can't be empty.") : End
                            My.Settings.remote = remote
                            My.Settings.Save()
                            Console.WriteLine("remote setting ok.")
                        End Sub).
                    AddOption("--show",
                        Sub()
                            Console.WriteLine("remote: " & My.Settings.remote)
                        End Sub, "顯示遠端位置")
                    )
        End Function
    End Class
End Namespace
