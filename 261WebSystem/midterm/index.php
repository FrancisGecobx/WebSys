<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Data</title>
</head>
<body>

    <form action="" method="POST">
        <table>
            <tr>
                <td>Enter Code:</td>
                <td><input type="text" name="code" placeholder="Enter Code" required></td>
            </tr>
            <tr>
                <td>Enter Description:</td>
                <td><input type="text" name="description" placeholder="Enter Description" required></td>
            </tr>
            <tr>
                <td>Enter Address:</td>
                <td><input type="text" name="address" placeholder="Enter Address" required></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td><input type="submit" name="submit" value="Submit"></td>
            </tr>
        </table>
    </form>

<?php
    $conn = mysqli_connect("localhost", "root", "", "test");

    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Process form submission
    if(isset($_POST['submit'])) {
        $code = $_POST['code'];
        $description = $_POST['description'];
        $address = $_POST['address'];

        // Insert new record into database
        $insert_sql = "INSERT INTO school (code, description, address) VALUES ('$code', '$description', '$address')";
        
        if(mysqli_query($conn, $insert_sql)) {
            echo "<p style='color:green;'>New school added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }

    // Fetch and display table data
    $sql = "SELECT * FROM school";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Code</th>";
        echo "<th>Description</th>";
        echo "<th>Address</th>";
        echo "</tr>";

        while($row = mysqli_fetch_assoc($result)) {   
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["code"] . "</td>";
            echo "<td>" . $row["description"] . "</td>";
            echo "<td>" . $row["address"] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "0 results";
    }

    mysqli_close($conn);
?>

</body>
</html>