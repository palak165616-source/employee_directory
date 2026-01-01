<?php
include 'db.php'; 

$sql = "
  SELECT Employee_ID, Name, Home_Address, Date_Of_Birth, Email_ID
  FROM Employee
  WHERE MONTH(Date_Of_Birth) = MONTH(CURDATE())
  ORDER BY DAY(Date_Of_Birth)
";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Birthday</title>
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
  <h1 style = "
      color: black;
      text-align: center;
      ">Employees With Birthdays in <?= date('F'); ?></h1>

  <?php if ($result && mysqli_num_rows($result) > 0): ?>
    <table style="border-collapse: collapse; width: 100%; ">
      <tr><th style="padding: 10px; text-align: center;">ID</th>
      <th style="padding: 10px; text-align: center;">Name</th>
      <th style="padding: 10px; text-align: center;">Date of Birth</th>
      <th style="padding: 10px; text-align: center;">Email_ID</th>
      <th style="padding: 10px; text-align: center;">Home_Address</th></tr>
      <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td style="padding: 8px;"><?= htmlspecialchars($row['Employee_ID']) ?></td>
          <td style="padding: 8px;"><?= htmlspecialchars($row['Name']) ?></td>
          <td style="padding: 8px;"><?= htmlspecialchars($row['Date_Of_Birth']) ?></td>
          <td style="padding: 8px;"><?= htmlspecialchars(!empty($row['Email_ID'])  ? $row['Email_ID']  : 'N/A') ?></td>
          <td style="padding: 8px;"><?= htmlspecialchars($row['Home_Address']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php else: ?>
    <p>No birthdays this month.</p>
  <?php endif; ?>
</body>
</html>
