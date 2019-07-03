Public Class Functions
    Public Shared Function URL(ByVal uri As String) As String
        Return My.Settings.remote & uri
    End Function
    Public Shared Function ConvertFileToBase64(ByVal fileName As String) As String
        Return Convert.ToBase64String(System.IO.File.ReadAllBytes(fileName))
    End Function
End Class
