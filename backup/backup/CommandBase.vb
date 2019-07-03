Public Class CommandBase
    Protected Commands As New List(Of Command)
    Public Function SelectCommand(ByVal command As String) As Command
        Return Me.Commands.Find(Function(x) x.Command = command)
    End Function
    Public Function HasCommand(ByVal command As String) As Boolean
        Return Me.Commands.Exists(Function(x) x.Command = command)
    End Function
    Public Function AddCommand(ByVal command As String, Optional ByVal description As String = Nothing, Optional ByVal action As Action = Nothing) As Command
        If Me.HasCommand(command) Then Throw New Exception("Command exists.")
        Me.Commands.Add(New Command(command, description, action))
        Return Me.Commands.Last
    End Function
    Public Function AddCommand(ByVal command As ICommand) As Command
        Dim _command As Command = command.CreateInstance()
        If Me.HasCommand(_command.Command) Then Throw New Exception("Command exists.")
        Me.Commands.Add(_command)
        Return _command
    End Function
    Public Function AddCommands(ByVal ParamArray commands() As Command)
        Me.Commands.AddRange(commands)
        Return Me
    End Function
    Public Function GetCommands() As List(Of Command)
        Return Me.Commands
    End Function
End Class
