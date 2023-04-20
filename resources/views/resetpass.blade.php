<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Reset Password</title>
</head>
<body style="margin:100px">
	<h1>You have requested to reser your password</h1>
	<hr>
	<p>We cant simply send you your old password, A unique link to reset your password has been generated for you.To reset your password click the following link and folloing link and follow the instructions.</p>
	<h1><a href="http://127.0.0.1:3000/api/resetngpassword/{{$token}}">
		Click Here to Reset Password
	</a></h1>
</body>
</html>