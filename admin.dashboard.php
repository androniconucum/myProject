<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Admin Dashboard</title>
</head>
<body class="bg-[#080914] text-[#E4E2DD]">
    <div class="container mx-auto py-12">
        <h1 class="text-3xl font-bold mb-6 text-center">Admin Dashboard</h1>

        <!-- User Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-[#13141F] text-left rounded-lg">
                <thead>
                    <tr>
                        <th class="py-2 px-4 bg-[#3539cc] text-[#E4E2DD]">User ID</th>
                        <th class="py-2 px-4 bg-[#3539cc] text-[#E4E2DD]">Username</th>
                        <th class="py-2 px-4 bg-[#3539cc] text-[#E4E2DD]">Email</th>
                        <th class="py-2 px-4 bg-[#3539cc] text-[#E4E2DD]">Role</th>
                        <th class="py-2 px-4 bg-[#3539cc] text-[#E4E2DD]">Status</th>
                        <th class="py-2 px-4 bg-[#3539cc] text-[#E4E2DD]">Actions</th>
                    </tr>
                </thead>
                <tbody id="user-table-body">
                    <!-- Example User Data - Replace with dynamic data from backend -->
                    <tr>
                        <td class="py-2 px-4">2</td>
                        <td class="py-2 px-4">normaluser</td>
                        <td class="py-2 px-4">normaluser@example.com</td>
                        <td class="py-2 px-4">Normal User</td>
                        <td class="py-2 px-4">Unlocked</td>
                        <td class="py-2 px-4 flex space-x-2">
                            <button class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-700">
                                Delete
                            </button>
                            <button class="bg-yellow-500 text-white py-1 px-3 rounded hover:bg-yellow-700">
                                Lock
                            </button>
                        </td>
                    </tr>
                    <!-- More user rows will be dynamically added here -->
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
