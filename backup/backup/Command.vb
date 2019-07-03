Imports System.Text.RegularExpressions
Public Class Command
    Inherits CommandBase
    Public Command As String
    Public Action As Action
    Public Description As String
    Public Arguments As New List(Of Arguments)
    Public Options As New List(Of Options)
    Private argumentsIndex As Integer = -1
    Public Sub New(ByVal command As String, Optional ByVal description As String = Nothing, Optional ByVal action As Action = Nothing)
        Me.Command = command
        Me.Description = description
        Me.Action = action
    End Sub
    Public Function AddOption(ByVal command As String, ByVal action As Action(Of String), Optional ByVal description As String = Nothing) As Command
        Me.Options.Add(New Options(command, action, description))
        Return Me
    End Function
    Public Function GetOption(ByVal command As String) As Options
        Return Me.Options.Find(Function(x) Regex.IsMatch(command, String.Format("^{0}$|^{0}=\S+", x.Command)))
    End Function
    Public Function AddArgument(ByVal argument As String, ByVal action As Action(Of Dictionary(Of String, String)), Optional ByVal description As String = Nothing) As Command
        Me.Arguments.Add(New Arguments(argument, action, description))
        Return Me
    End Function
    Public Function GetNextArgument() As Arguments
        Me.argumentsIndex += 1
        Return Me.Arguments.ElementAtOrDefault(Me.argumentsIndex)
    End Function
End Class