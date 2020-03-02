<?php
include "./app/Automation.php";
$output_directory_path = getcwd().'/output';
$automation = new Automation($output_directory_path);
$automation->initializer();
$automation->add_manufacturers();
?>