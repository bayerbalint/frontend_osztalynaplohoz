<?php

include_once "config.php";
include_once "db.php";
include_once "html.php";

session_start();
// session_destroy();

head();
displayBodyStart();
showButtonsStart();

if (databaseExists()){
    showQueryButtons();
    showDatabaseDeleteButton();
    showButtonsEnd();
    handleQuerys();
}else{
    showDatabaseCreateButton();
    showButtonsEnd();
    if (isset($_POST["create"])) {
        createDatabase();
        createTableSubjects();
        createTableClasses();
        createTableMarks();
        createTableStudents();
        fillTables();
        header("refresh: 0.1");
    }
}

displayBodyEnd();