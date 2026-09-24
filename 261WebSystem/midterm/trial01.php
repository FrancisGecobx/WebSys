<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Data</title>
</head>
<body>

    <!-- ADD SCHOOL FORM -->
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
    // Connect to the database
    $conn = mysqli_connect("localhost", "root", "", "test");

    if(!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // ==================== ADD RECORD ====================
    if(isset($_POST['submit'])) {
        $code = $_POST['code'];
        $description = $_POST['description'];
        $address = $_POST['address'];

        $insert_sql = "INSERT INTO school (code, description, address) VALUES ('$code', '$description', '$address')";

        if(mysqli_query($conn, $insert_sql)) {
            echo "<p style='color:green;'>New school added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }

    // ==================== UPDATE RECORD ====================
    if(isset($_POST['update'])) {
        $id = $_POST['id'];
        $code = $_POST['code'];
        $description = $_POST['description'];
        $address = $_POST['address'];

        $update_sql = "UPDATE school SET code='$code', description='$description', address='$address' WHERE id='$id'";

        if(mysqli_query($conn, $update_sql)) {
            echo "<p style='color:green;'>School updated successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }

    // ==================== DELETE RECORD ====================
    if(isset($_POST['delete'])) {
        $id = $_POST['id'];

        $delete_sql = "DELETE FROM school WHERE id='$id'";

        if(mysqli_query($conn, $delete_sql)) {
            echo "<p style='color:green;'>School deleted successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
        }
    }

    // ==================== DISPLAY RECORDS ====================
    $sql = "SELECT * FROM school";
    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        echo "<th>ID</th>";
        echo "<th>Code</th>";
        echo "<th>Description</th>";
        echo "<th>Address</th>";
        echo "<th>Action</th>";
        echo "</tr>";

        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["code"] . "</td>";
            echo "<td>" . $row["description"] . "</td>";
            echo "<td>" . $row["address"] . "</td>";

            // Update and Delete buttons
            echo "<td>";
            echo "<form action='' method='POST' style='display:inline;'>";
            echo "<input type='hidden' name='id' value='" . $row["id"] . "'>";
            echo "<input type='hidden' name='code' value='" . $row["code"] . "'>";
            echo "<input type='hidden' name='description' value='" . $row["description"] . "'>";
            echo "<input type='hidden' name='address' value='" . $row["address"] . "'>";
            echo "<input type='submit' name='update' value='Update'>";
            echo "<input type='submit' name='delete' value='Delete' onclick=\"return confirm('Are you sure you want to delete this record?')\">";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }

    // Close the database connection
    mysqli_close($conn);
    ?>

</body>
</html>