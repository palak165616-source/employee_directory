<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid employee ID");
}
$emp_id = intval($_GET['id']);

$sql  = "SELECT e.*, j.Position_Title, d.Name AS Department_Name, c.*, o.Office_Name,o.City
         FROM Employee AS e
         JOIN JobPosition AS j   ON e.Position_ID   = j.Position_ID
         LEFT JOIN Department   AS d ON e.Department_ID = d.Department_ID
         LEFT JOIN Contract AS c ON e.Contract_ID = c.Contract_ID
         LEFT JOIN Office AS o ON e.Office_ID = o.Office_ID
         WHERE e.Employee_ID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $emp_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (!$emp = mysqli_fetch_assoc($result)) {
    die("Employee not found");
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Profile – <?= htmlspecialchars($emp['Name']) ?></title>
  <style>
    .table-container {
      width: 100%;
      overflow-x: auto;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #aaa;
      padding: 8px;
      text-align: left;
      word-wrap: break-word;  
    }
    .btn-home {
      display: inline-block;
      padding: 8px 16px;
      background-color: #A8D3F0;
      color: white;
      text-decoration: none;
      border-radius: 4px;
    }
    .btn-home:hover {
      background-color: #2592DA;
    }

  </style>
</head>
<body style="
      background-color: #E9F4FB;
      margin: 0;
      padding: 20px;
      font-family: Arial, sans‑serif;
    ">
  <a href="home.php" class="btn-home">Home</a>
  <div style="
      max-width: 800px;
      margin: 40px auto;
      padding: 20px;
      background: #A8D3F0;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      font-family: Arial, sans-serif;
    ">
  <div class="profile-box">
    <h1>Employee Details</h1>
    <p><label>Name:</label> <?= htmlspecialchars($emp['Name'] ?? '') ?></p>
    <p><label>Employee ID:</label> <?= htmlspecialchars($emp['Employee_ID'] ?? '') ?></p>
    <p><label>Job Title:</label> <?= htmlspecialchars($emp['Position_Title'] ?? '') ?></p>
    <p><label>Department:</label> <?= htmlspecialchars($emp['Department_Name'] ?? '') ?></p>
    <h2>Contact Information</h2>
    <p><label>Email:</label> <?= htmlspecialchars($emp['Email_ID'] ?? '') ?></p>
    <p><label>Phone:</label> <?= htmlspecialchars($emp['Phone_Number'] ?? '') ?></p>
    <h2>Office</h2>
    <p><label>Office Name:</label> <?= htmlspecialchars($emp['Office_Name'] ?? '') ?></p>
    <p><label>City:</label> <?= htmlspecialchars($emp['City'] ?? '') ?></p>
    <h2>Contract Details</h2>
    <p><label>Contract Type:</label> <?= htmlspecialchars($emp['Contract_Type'] ?? '') ?></p>
    <p><label>Date Hired:</label> <?= htmlspecialchars($emp['Start_Date'] ?? '') ?></p>
    <p><label>Salary: </label> <?= htmlspecialchars($emp['Salary'] ?? '') ?></p>
  </div>

</body>
</html>
