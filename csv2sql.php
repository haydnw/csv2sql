<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>CSV to SQL convertor</title>
    </head>
    <body>
    	<h2>CSV to SQL convertor</h2>

    	<!-- Input form begin -->

    	<form name="csv2sql" method="post" action="<?php echo $PHP_SELF; ?>">
        	<fieldset>
        	    <input type="hidden" name="ref" value="csv2sql" />
        	    Delimiting character:<br />
        		<input type="text" name="delimiter" value="," size="1" /><br />
        		<br />
        		First line is column headings:<br />
        		<input type="checkbox" name="headings" value="checked" /><br />
        		<br />
        		Convert column names to lower case:<br />
        		<input type="checkbox" name="lower" value="checked" /><br />
        		<br />
        		Convert column name spaces to underscores:<br />
        		<input type="checkbox" name="convertspaces" value="checked" /><br />
        		<br />
        		Write to file:<br />
        		<input type="text" name="tofile" size="11" /><br />
        		<br />
        		Insertion table:<br />
        		<input type="text" name="table_name" size="50" /><br />
        		<br />
        		CSV file:<br />
        		<input type="file"	name="fromfile" size="11" /><br />
        		<br />
        		CSV data:<br />
        		<textarea name="csv_data" rows="20" cols="100"></textarea><br />
        		<br />
        		<input type="submit" value="     Convert to SQL query     " />
        	</fieldset>
    	</form>

    	<!-- Input form end -->

<?php

// Set default delimiter
$delimiter = ",";

// Parse incoming information if above form was posted
if ($_POST['ref'] == "csv2sql") {

    echo "<h2>SQL Query Output:</h2>";

    // Get information from form & prepare it for parsing
    $delimiter  = $_POST['delimiter'];
    $table_name = $_POST['table_name'];
    if (($_POST['fromfile'] === null) || ($_POST['fromfile'] === '')) {
        $csv_data = $_POST['csv_data'];
    } else {
        $csv_data = file_get_contents($_POST['fromfile']);
    }
    $csv_array    = explode("\n", $csv_data);
    $column_names = explode($delimiter, $csv_array[0]);

    // Generate base query
    $base_query = "INSERT INTO $table_name";

    // Include column headings if required
    if ($_POST['headings'] === 'checked') {
        $base_query .= " (";
        $first      = true;
        foreach ($column_names as $column_name) {
            if ($first === false) {
                $base_query .= ", ";
            }
            $column_name = trim($column_name);
            if ($_POST['lower'] === 'checked') {
                $column_name = strtolower($column_name);
            }
            if ($_POST['convertspaces'] === 'checked') {
                $column_name = str_replace(' ', '_', $column_name);
            }
            $base_query .= "$column_name";
            $first = false;
        }
        $base_query .= ")";
    }
    $base_query .= " ";
    // Loop through all CSV data rows and generate separate
    // INSERT queries based on base_query + the row information
    $last_data_row = count($csv_array) - 1;
    for ($counter = 1; $counter < $last_data_row; $counter++) {
        $value_query = "VALUES ('";
        $first = true;
        $data_row = explode($delimiter, $csv_array[$counter]);
        $value_counter = 0;
        foreach ($data_row as $data_value) {
            if ($first === false) {
                $value_query .= "', '";
            }
            $data_value = trim($data_value);
            $value_query .= "$data_value";
            $first = false;
        }

        $value_query .= "')";

        // Combine generated queries to generate final query
        $query = $base_query .$value_query . ";";

        echo "$query <br />";

        // Append newest query to the string to be written to file
        if (($_POST['tofile'] !== null) && ($_POST['tofile'] !== '')) {
            $sql .= $query . "\n";
        }
    }

    // Write to file if required
    $tofile = $_POST['tofile'];
    if (($tofile !== null) && ($tofile !== '')) {
        $fh = fopen($tofile, 'w')
            or die("Unable to write to file " . $tofile . ".");
        fwrite($fh, $sql);
        fclose($fh);
        echo "<h2>SQL Query Output written to file.</h2>";
    }
}

?>

        <p>
            <a href="http://validator.w3.org/check?uri=referer">
                <img src="http://www.w3.org/Icons/valid-xhtml11" alt="Valid XHTML 1.1" height="31" width="88" />
            </a>
        </p
    </body>
</html>