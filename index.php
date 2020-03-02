<?php
include "./app/DescriptionAutomation.php";
$output_directory_path = getcwd().'/output';
$automation = new Automation($output_directory_path);
$automation->initializer();
$automation->replace_custom_data();
?>