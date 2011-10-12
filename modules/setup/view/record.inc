<?
if($_REQUEST['action_main_update']) {
    foreach ($_REQUEST as $key => $value) {
        $elements = explode("_", $key);

        if(count($elements) == 3) {
            $query = "SELECT SetupID FROM setup WHERE Name='$elements[2]'";
            $row = $_lib['db']->get_row(array('query' => $query));

            if(empty($row)) {
                /* this may be unsafe? */
                $query = "INSERT INTO setup (`Name`, `value`) VALUES ('$elements[2]', '$value')";
                $_lib['db']->db_update($query);
            }
            else {
                $query = "update setup set value='$value' where Name='$elements[2]'";
                #print "$query<br>\n";
                $_lib['db']->db_update($query);
            }
        }
    }
}
?>