<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

$dept_sql = "SELECT Name FROM Department ORDER BY Name";
$dept_result = mysqli_query($conn, $dept_sql);
if (!$dept_result) {
  die("Departments query failed: " . mysqli_error($conn));
}

$city_sql = "SELECT City FROM Office ORDER BY City";
$city_result = mysqli_query($conn, $city_sql);
if (!$city_result) {
  die("City query failed: " . mysqli_error($conn));
}

$all_rows = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $params = [];
  $param_types = "";
  $conditions = [];

  $sql = "SELECT e.*, j.Position_Title,d.Name as Department_Name, o.Office_Name, o.City
          FROM Employee AS e
          LEFT JOIN Office AS o on e.Office_ID = o.Office_ID
          LEFT JOIN JobPosition as j on e.Position_ID = j.Position_ID
          LEFT JOIN Department as d on e.Department_ID = d.Department_ID";

  if (!empty($_POST['search_text'])) {
    $search = "%" . $_POST['search_text'] . "%";
    $conditions[] = "(e.Name LIKE ? OR j.Position_Title LIKE ? OR d.Name LIKE ? OR  o.Office_Name LIKE ? OR o.City LIKE ? OR e.Employee_ID LIKE ?)";
    $param_types .= "ssssss";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
  }

  if (!empty($_POST['department'])) {
    $conditions[] = "d.Name = ?";
    $param_types .= "s";
    $params[] = $_POST['department'];
  }

  if (!empty($_POST['city'])) {
    $conditions[] = "o.City = ?";
    $param_types .= "s";
    $params[] = $_POST['city'];
  }

  if (!empty($_POST['start_date'])) {
    $conditions[] = "e.Start_Date >= ?";
    $param_types .= "s";
    $params[] = $_POST['start_date'];
  }


  if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
  }
    
  $stmt = mysqli_prepare($conn, $sql);
  if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
  }
  if ($param_types !== "") {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
  }

  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  if (!$result) {
    die("Getting result set failed: " . mysqli_error($conn));
  }

  $all_rows = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $all_rows[] = $row;
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Employee Directory</title>
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

    form {
      margin-bottom: 20px;
    }
    .cards-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 20px;
    }
    .card {
      background: #7CBDE9;
      border-radius: 8px;
      box-shadow: 2px 4px 10px rgba(1, 30, 63, 0.1);
      padding: 16px;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
      background: #2592DA;
      transform: translateY(-4px);
      box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    .card h3 {
      margin-bottom: 8px;
      font-size: 1.2em;
      color: #333;
    }
    .card p {
      margin-bottom: 4px;
      font-size: 0.95em;
      color: #555;
    }
    a {
      text-decoration: none;
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
  <h1>Search Employees</h1>

  <form method="post" action="">
    <input type="text" name="search_text" placeholder="type here to search" value="<?= htmlspecialchars($_POST['search_text'] ?? '') ?>" /><br><br>
    <h3>Filter By:</h3>

    <label for="department">Department: </label>
    <select name="department">
      <option value="">-- All Departments --</option>
      <?php
        $selected_dept = $_POST['department'] ?? '';
        while ($row = mysqli_fetch_assoc($dept_result)) {
          $dept_name = htmlspecialchars($row['Name']);
          $isSelected = ($dept_name === $selected_dept) ? 'selected' : '';
          echo "<option value=\"$dept_name\"$isSelected>$dept_name</option>";
        }
      ?>
    </select><br>

    <label for="city">City: </label>
    <select name="city">
      <option value="">-- Cities --</option>
      <?php
        $selected_city = $_POST['city'] ?? '';
        while ($row = mysqli_fetch_assoc($city_result)) {
          $city_name = htmlspecialchars($row['City']);
          $isSelected = ($city_name === $selected_city) ? 'selected' : '';
          echo "<option value=\"$city_name\"$isSelected>$city_name</option>";
        }
      ?>
    </select><br>

    <label for="start_date">Start Date: </label>
    <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" /><br><br>


    <button type="submit">Search</button>
  </form>

  <?php if (!empty($all_rows)): ?>
    <div class="cards-container">
    <?php foreach ($all_rows as $emp): ?>
      <a href="profile_detail.php?id=<?= urlencode($emp['Employee_ID']) ?>">
        <div class="card">
          <h3><?= htmlspecialchars($emp['Name'] ?? '') ?></h3>
          <p><strong>ID:</strong> <?= htmlspecialchars($emp['Employee_ID'] ?? '') ?></p>
          <p><strong>Department:</strong> <?= htmlspecialchars($emp['Department_Name'] ?? '') ?></p>
          <p><strong>Job:</strong> <?= htmlspecialchars($emp['Position_Title'] ?? '') ?></p>
        </div>
      </a>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>

</body>
</html>
