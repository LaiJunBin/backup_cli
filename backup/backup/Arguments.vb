Public Class Arguments
    Public KeyName As String
    Public Description As String
    Public Action As Action(Of Dictionary(Of String, String))
    Private Shared Arguments As New Dictionary(Of String, String)
    Public Sub New(ByVal keyName As String, ByVal action As Action(Of Dictionary(Of String, String)), Optional ByVal description As String = Nothing)
        Me.Description = description
        Me.KeyName = keyName
        Me.Action = action
    End Sub
    Public Sub Execute(ByVal arg As String)
        Arguments.Add(Me.KeyName, arg)
        Me.Action(Arguments)
    End Sub
End Class