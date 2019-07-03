Public Class Request
    Public Shared Payload As New System.Collections.Specialized.NameValueCollection
    Public Shared Headers As New System.Collections.Specialized.NameValueCollection
    Public Shared Method As Methods = Request.Methods.Method_GET
    Public Shared Actions As New List(Of Action)
    Public Shared SuperActions As New List(Of Action)
    Public Shared Active As Boolean = False
    Public Shared MustSuper As Boolean = False
    Private Shared WebClient As New System.Net.WebClient
    Private Shared URI As String = ""

    Public Enum Methods
        Method_GET
        Method_POST
        Method_PUT
        Method_PATCH
        Method_DELETE
    End Enum
    Public Shared Sub SetURI(ByVal uri As String)
        Request.URI = uri
        Request.Active = True
    End Sub

    Public Shared Sub Initialize()
        Request.Headers.Clear()
        Request.Actions.Clear()
        Request.SuperActions.Clear()
        Request.Payload.Set("username", My.Settings.username)
        Request.Payload.Set("password", My.Settings.password)
        Request.Method = Request.Methods.Method_GET
        Request.Active = False
        Request.MustSuper = False
        Request.URI = ""
    End Sub

    Public Shared Function Run(Optional ByVal show As Boolean = True, Optional ByVal validationSuper As Boolean = True) As String
        Dim Response = Nothing
        If Request.Active Then
            Dim URL As String = Functions.URL(Request.URI)
            Dim Methods() As String = {"GET", "POST", "PUT", "PATCH", "DELETE"}
            Dim Method As String = Methods(Request.Method)
            Dim MustSuper As Boolean = Request.MustSuper

            If MustSuper AndAlso Not Request.IsSuperUser() OrElse _
                validationSuper AndAlso Request.Payload.Get("path") = "private/" AndAlso _
                Not Request.IsSuperUser() Then Console.WriteLine("request denied.") : End

            Request.WebClient.Encoding = System.Text.Encoding.UTF8
            For Each header In Request.Headers
                Request.WebClient.Headers.Add(header.Key, header.Value)
            Next

            Try
                If Request.Method = Request.Methods.Method_GET Then
                    Request.WebClient.QueryString.Add(Request.Payload)
                    Response = Request.WebClient.DownloadString(URL)
                Else
                    Response = System.Text.Encoding.UTF8.GetString(Request.WebClient.UploadValues(URL, Method, Request.Payload))
                End If
            Catch ex As Exception
                Console.WriteLine("request fails. please check remote.")
                End
            End Try

            If show Then Console.WriteLine(Response)
        End If
        Dim SuperActions As New List(Of action)(Request.SuperActions.GetRange(0, Request.SuperActions.Count))
        Dim Actions As New List(Of Action)(Request.Actions.GetRange(0, Request.Actions.Count))
        Request.SuperActions.Clear()
        Request.Actions.Clear()
        For Each action In Actions
            action()
        Next

        If SuperActions.Count > 0 Then
            If Request.IsSuperUser() Then
                For Each action In SuperActions
                    action()
                Next
            Else
                Console.WriteLine("request denied.")
                End
            End If
        End If

        Return Response
    End Function

    Public Shared Function IsSuperUser()
        Request.Initialize()
        Request.Method = Request.Methods.Method_POST
        Request.SetURI("login")
        Dim Response = Request.Run(False, False)
        Return Response = 1
    End Function

    Public Shared Sub Pull(ByVal filename As String)
        Dim URL As String = Functions.URL("pull/download")
        Try
            Request.WebClient.QueryString.Set("filename", filename.Replace("/", "@%@"))
            Dim dirs() As String = filename.Split("/")
            Dim dir As String = ""
            For i = 0 To UBound(dirs) - 1
                dir &= dirs(i) & "/"
                If Not My.Computer.FileSystem.DirectoryExists(dir) Then
                    My.Computer.FileSystem.CreateDirectory(dir)
                End If
            Next
            Request.WebClient.DownloadFile(URL, filename)
            Console.WriteLine("pull " & filename & " success.")
        Catch ex As Exception
            Console.WriteLine("request fails. please check remote.")
            End
        End Try
    End Sub

    Public Shared Sub DownloadFile(ByVal uri As String, ByVal path As String, ByVal filename As String, Optional ByVal force As Boolean = False)
        If path = "private" Then
            If Not Request.IsSuperUser() Then Console.WriteLine("request denied.") : End
        End If
        Request.Payload.Set("path", path)

        If My.Computer.FileSystem.FileExists(My.Computer.FileSystem.GetName(filename)) And Not force Then Console.WriteLine("file already exists.") : Return
        If Not Request.CheckFileExists(uri) Then Console.WriteLine(String.Format("file {0} not found.", uri)) : Return

        Dim URL As String = Functions.URL("download")

        Try
            Request.WebClient.QueryString.Set("path", path)
            Request.WebClient.QueryString.Set("filename", uri.Replace("/", "@%@"))

            Request.WebClient.DownloadFile(URL, My.Computer.FileSystem.GetName(filename))
            Console.WriteLine("download file success.")
        Catch ex As Exception
            Console.WriteLine("request fails. please check remote.")
            End
        End Try

    End Sub

    Public Shared Function CheckFileExists(ByVal uri As String)
        Dim Response As String = Nothing
        Try
            Request.Method = Request.Methods.Method_POST
            Request.Payload.Set("filename", uri)
            Request.SetURI("check")
            Response = Request.Run(False)
        Catch ex As Exception
            Console.WriteLine("request fails. please check remote.")
            End
        End Try
        Return Response = 1
    End Function
End Class