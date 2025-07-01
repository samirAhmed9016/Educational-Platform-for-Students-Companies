<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Certificate</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            text-align: center;
            padding: 50px;
        }

        .certificate-box {
            border: 10px solid #0b3d91;
            padding: 50px;
        }

        h1 {
            font-size: 36px;
            color: #0b3d91;
        }

        p {
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div class="certificate-box">
        <h1>Certificate of Completion</h1>
        <p>This is to certify that</p>
        <h2>{{ $user->name }}</h2>
        <p>has successfully completed the course</p>
        <h2>{{ $course->title }}</h2>
        {{-- <p>on {{ $issuedDate }}</p> --}}
    </div>
</body>

</html>
