Public Class CMD
    Inherits CommandBase

    Public Sub New()
        Me.AddCommand("help", "幫助訊息", AddressOf Me.ShowHelp)
    End Sub
    Public Sub Execute(ByVal _args() As String)
        Dim args As New Queue(Of String)(_args)
        If args.Count = 0 Then Me.ShowHelp() : End
        Dim arg As String = args.Dequeue
        Dim Command As Command = Me.SelectCommand(arg)
        If Command Is Nothing Then Console.WriteLine(String.Format("Unknow command {0}.", arg)) : End
        If args.Count = 0 Then If Command.Action Is Nothing Then Me.ShowHelp(Command) Else Command.Action()

        While args.Count > 0
            arg = args.Dequeue
            Dim _command As Command = Command.SelectCommand(arg)
            If Not _command Is Nothing Then
                If args.Count = 0 Then If _command.Action Is Nothing Then Me.ShowHelp(_command) Else _command.Action()
                Command = _command : Continue While
            End If

            Dim options As Options = Command.GetOption(arg)
            If Not options Is Nothing Then options.Action(arg.Split("=").Last) : Continue While

            Dim arguments As Arguments = Command.GetNextArgument()
            If Not arguments Is Nothing Then arguments.Execute(arg) : Continue While

            Console.WriteLine(String.Format("Unknow command {0}.", arg))
            End
        End While

    End Sub
    Public Sub ShowHelp(Optional ByVal command As Command = Nothing)
        Console.WriteLine("幫助訊息:")
        If Not command Is Nothing Then showCommand(command) : Return
        For Each command In Me.Commands
            showCommand(command)
        Next
    End Sub
    Private Sub showCommand(ByVal command As Command, Optional ByVal prefix As String = "", Optional ByVal spacesize As Integer = 0)
        Console.WriteLine(String.Format("指令:{0}{1} => {2}", prefix, command.Command, command.Description))

        Dim ArgumentsMessages() As String = command.Arguments.Select(Function(x, i) StrDup(spacesize + 4, " ") & String.Format("{0}.{1} => {2}", i + 1, x.KeyName, x.Description)).ToArray
        If ArgumentsMessages.Length > 0 Then Console.WriteLine(StrDup(spacesize, " ") & String.Format("參數:{0}{1}", vbNewLine, Strings.Join(ArgumentsMessages, vbNewLine)))

        Dim OptionsMessages() As String = command.Options.Select(Function(x, i) StrDup(spacesize + 4, " ") & String.Format("{0}.{1} => {2}", i + 1, x.Command, x.Description)).ToArray
        If OptionsMessages.Length > 0 Then Console.WriteLine(StrDup(spacesize, " ") & String.Format("選項:{0}{1}", vbNewLine, Strings.Join(OptionsMessages, vbNewLine)))

        Dim commands As New List(Of Command)(command.GetCommands())
        If commands.Count = 0 AndAlso prefix = "" Then Console.WriteLine(StrDup(40, "-")) : Return

        For i = 0 To commands.Count - 1
            showCommand(commands(i), prefix & command.Command & " ", spacesize + 2)
            If i = commands.Count - 1 Then Console.WriteLine(StrDup(40, "-"))

        Next

    End Sub

    Public Shared Function GetPassword(Optional ByVal passwordMask As Char = "*"c) As String
        Dim pwd As String = String.Empty
        Dim sb As New System.Text.StringBuilder()
        Dim cki As ConsoleKeyInfo = Nothing

        'Get the password
        Console.Write("Enter password: ")
        While (True)
            While Console.KeyAvailable() = False
                System.Threading.Thread.Sleep(50)
            End While
            cki = Console.ReadKey(True)
            If cki.Key = ConsoleKey.Enter Then
                Console.WriteLine()
                Exit While
            ElseIf cki.Key = ConsoleKey.Backspace Then
                If sb.Length > 0 Then
                    sb.Length -= 1
                    Console.Write(ChrW(8) & ChrW(32) & ChrW(8))
                End If
                Continue While
            ElseIf Asc(cki.KeyChar) < 32 OrElse Asc(cki.KeyChar) > 126 Then
                Continue While
            End If
            sb.Append(cki.KeyChar)
            Console.Write(passwordMask)
        End While
        pwd = sb.ToString()
        Return pwd
    End Function
End Class