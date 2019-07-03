Public Class Options
    Public Command As String
    Public Description As String
    Public Action As Action(Of String)
    Public Sub New(ByVal command As String, ByVal action As Action(Of String), Optional ByVal description As String = Nothing)
        Me.Command = command
        Me.Action = action
        Me.Description = description
    End Sub
End Class
