<!DOCTYPE html>
<html>
<head>
    <title>Test File Upload</title>
</head>
<body>
    <h2>Test File Upload</h2>
    <form action="/test-upload" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="test_file" accept=".csv" required>
        <button type="submit">Upload Test File</button>
    </form>
</body>
</html>