<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <title>Document</title>
</head>
<body class="bg-black ">
    <div class="flex h-screen items-center text-[#E4E2DD] flex-col justify-center">
        <div class="border px-10 py-10 rounded-md justify-center items-center flex flex-col"> 
        <img src="images/CS31A-small.png" alt="" width="100px">
        <p class="font-worksans text-[2.2rem] font-black mb-4 text-[#E4E2DD]">Login account</p>
        <div class="flex flex-col gap-2">
        <input type="text" name="username" id="username" placeholder="Username" size="30" class="pl-2 text-black rounded-sm placeholder:text-gray-500"> 
        <input type="password" name="password" id="password" placeholder="Password" size="30" class="pl-2 text-black rounded-sm placeholder:text-gray-500">
        </div>
        <div class="flex justify-end items-end w-full">
            <button type="submit" id="submit" name="submit" class="bg-[#E4E2DD] text-black rounded-md mt-3 px-3 py-0.5 flex">Submit</button>
        </div>
        <hr class="w-[15rem] pt-1 justify-start mt-5 ">
        <div class="flex pt-1">
        <p>New user?</p><a href="signup.php" class="pl-1 text-gray-400">Register Here</a>
        </div>
        </div>
    </div>
</body>
</html>