Imports backup.Functions
Module Backup

    Sub Main(ByVal args() As String)

        Request.Initialize()

        Dim CMD As New CMD

        CMD.AddCommand(New Commands.List())
        CMD.AddCommand(New Commands.Config())
        CMD.AddCommand(New Commands.Reset())
        CMD.AddCommand(New Commands.Upload())
        CMD.AddCommand(New Commands.Download())
        CMD.AddCommand(New Commands.Delete())
        CMD.AddCommand(New Commands.Rename())
        CMD.AddCommand(New Commands.Move())
        CMD.AddCommand(New Commands.Publish())
        CMD.AddCommand(New Commands.Pull())

        'debug
        'My.Settings.username = "username"
        'My.Settings.password = "password"
        'My.Settings.remote = "http://localhost:8000/"
        'args = "config username".Split
        'Console.SetIn(New System.IO.StringReader("admin"))
        'args = "upload a.txt aaa.txt --private".Split


        CMD.Execute(args)
        Request.Run()

    End Sub
End Module