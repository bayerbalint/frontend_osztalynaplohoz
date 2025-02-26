<?php

include_once "db.php";

function head()
{
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Orai</title>
        <link rel="stylesheet" href="style.css">
    </head>';
}

function displayBodyStart()
{
    echo '<body>';
}

function displayBodyEnd()
{
    echo '</body><script src="script.js"></script></html>';
}

function showButtonsStart(){
    echo '
        <nav>
        <form action="index.php" method="POST">
            <ul id="buttonHolder">';
}

function showDatabaseDeleteButton(){
    echo '<li><button class="queryButton" id="delete" name="delete" onclick="document.body.style.visibility = `hidden`;">Adatbázis törlése</button></li>';
}

function showDatabaseCreateButton()
{
    echo '<li><button class="queryButton" id="create" name="create" onclick="document.body.style.visibility = `hidden`;">Adatbázis létrehozása</button></li>';
}

function showQueryButtons(){
    echo '
    <li><button class="queryButton" id="grades" name="grades">évfolyamok</button></li>
    <li><button class="queryButton" id="classes" name="classes">osztályok</button></li>
    <li><button class="queryButton" id="students" name="students">tanulók</button></li>
    <li><button class="queryButton" id="studentAverages" name="studentAverages">tanulók átlaga</button></li>
    <li><button class="queryButton" id="classAverage" name="classAverage">osztály átlaga</button></li>
    <li><button class="queryButton" id="best" name="best">10 legjobb tanuló</button></li>
    <li><button class="queryButton" id="hallOfFame" name="hallOfFame">Hall of Fame</button></li>';
}

function showButtonsEnd(){
    echo '
            </ul>
        </form>
        </nav>';
}

function handleQuerys()
{
    $grades = getGrades();
    $subjects = getSubjectNames();

    if (isset($_POST["delete"])) {
        dropDatabase();
        header("refresh: 0.1");
    }


    if (isset($_POST["grades"])) {
        showGrades($grades);
    }

    foreach ($grades as $grade) {
        if (isset($_POST[$grade[0]])) {
            if (isset($_SESSION["grade"]) && isset($_SESSION["class"]) && $grade[0] != $_SESSION["grade"]){
                $_SESSION["class"] = null;
            }
            showGrades($grades);
            $_SESSION["grade"] = $grade[0];
        }
    }

    if (isset($_POST["classes"]) && chosenGrade()) {
        $_SESSION["classes"] = getClasses();
        showClasses();
    }

    if (isset($_SESSION["classes"])){
        foreach ($_SESSION["classes"] as $class) {
            if (isset($_POST[$class[0]])) {
                showClasses();
                $_SESSION["class"] = $class[0];
            }
        }
    }

    if (isset($_POST["students"])) {
        if (chosenGradeAndClass()) {
            showStudents(getStudents());
        }
    }

    if (isset($_POST["studentAverages"])) {
        if (chosenGradeAndClass()) {
            showStudentAverages(getStudentsSubjectAverage(), getStudentsAverage(), $subjects);
        }
    }

    if (isset($_POST["classAverage"])) {
        if (chosenGradeAndClass()) {
            showClassAverage(getClassSubjectAverages(), getClassAverage(), $subjects);
        }
    }

    if (isset($_POST["best"])) {
        if (chosenGradeAndClass()) {
            showBestStudents(get10BestStudentsByGrade());
        }
    }

    if (isset($_POST["hallOfFame"])){
        if (chosenGradeAndClass()){
            showHallOfFame(getBestClass(), get10BestStudents());
        }
    }
}

function getGradeAppendix(){
    $appendix = "";
    if (in_array(substr($_SESSION["grade"], strlen($_SESSION["grade"])-1, 1), ["1","2","4","7","9"])){
        $appendix = "-es";
    }
    else if (in_array(substr($_SESSION["grade"], strlen($_SESSION["grade"])-1, 1), ["3","8"])){
        $appendix = "-as";
    }
    else if (in_array(substr($_SESSION["grade"], strlen($_SESSION["grade"])-1, 1), ["5"])){
        $appendix = "-ös";
    }
    else{
        $appendix = "-os";
    }
    return $appendix;
}

function chosenGrade(){
    $isGood = true;
    if (!isset($_SESSION["grade"])) {
        echo '<script>alert("Válasszon ki egy évfolyamot!");</script>';
        $isGood = false;
    }
    return $isGood;
}

function chosenGradeAndClass()
{
    $isGood = chosenGrade();
    if (!isset($_SESSION["class"]) && $isGood == true) {
        echo '<script>alert("Válasszon ki egy osztályt!");</script>';
        $isGood = false;
    }
    return $isGood;
}

function showGrades($grades){
    echo '<form method="POST"><div id="container">';
    foreach ($grades as $grade){
        echo '<button class="gradesButton" id="'. $grade[0] . '" name="' . $grade[0] . '">' . $grade[0] . '</button>';
    }
    echo '</div></form>';
}

function showClasses(){
    echo '<form method="POST"><div id="container">';
    foreach ($_SESSION["classes"] as $class){
        echo '<button class="classesButton" id="' . $class[0] . '" name="' . $class[0] . '">' . $class[0] . '</button>';
    }
    echo '</div><form>';
}

function showStudents($students){
    echo '<div id="tableContainer"><table><tr><th>' . $_SESSION["grade"] . ': ' . $_SESSION["class"] . ' tanulók</th></tr>';
    foreach ($students as $student){
        echo '<tr><td>' . $student[0] . '</td></tr>';
    }
    echo '</table></div>';
}

function showStudentAverages($averages, $ovaAverages, $subjects){
    echo '<div id="tableContainer"><table><tr><th colspan="' . count($subjects)+2 . '">' . $_SESSION["grade"] . ': ' . $_SESSION["class"] . ' tanulók átlaga</th></tr><tr><td>name</td>';
    foreach ($subjects as $subject){
        echo '<td>' . $subject[0] . '</td>';
    }
    echo '<td>average</td>';

    for ($i = 0; $i < count($averages); $i++) {
        if ($i % count($subjects) == 0){
            echo '</tr><tr><td>' . $averages[$i][0] . '</td>';
        }
        echo '<td>' . $averages[$i][1] . '</td>';
        if ($i % count($subjects) == count($subjects) - 1) {
            echo '<td>' . $ovaAverages[$i / count($subjects)][0] . '</td>';
        }
    }
    echo '</tr></table></div>';
}

function showClassAverage($averages, $ovaAverage, $subjects){
    echo '<div id="tableContainer"><table><tr><th colspan="2">' . $_SESSION["grade"] . ': ' . $_SESSION["class"] . ' osztály átlaga</th></tr><tr><td>subject</td><td>average</td></tr>';
    for ($i = 0; $i < count($averages); $i++){
        echo '<tr><td>' . $subjects[$i][0] . '</td><td>' . $averages[$i][0] . '</td></tr>';
    }
    echo '<tr><td>class average</td><td>' . $ovaAverage[0][0] . '</td></tr></table></div>';
}

function showBestStudents($students){
    echo '<div id="tableContainer"><table><tr><th colspan="3">' . $_SESSION["grade"] . getGradeAppendix() . ' évfolyam 10 legjobb tanulói</th></tr><tr><td>class</td><td>name</td><td>average</td></tr>';
    foreach ($students as $student){
        echo '<tr><td>' . $student[0] . '</td><td>' . $student[1] . '</td><td>' . $student[2] . '</td></tr>';
    }
    echo '</table></div>';
}

function showHallOfFame($class, $students){
    echo '<div id="tableContainer"><table><tr><th colspan="3">Legjobb osztály az iskolában</th></tr><tr><td>grade</td><td>class</td><td>average</td></tr><tr><td>' . $class[0][0] . '</td><td>' . $class[0][1] . '</td><td>' . $class[0][2] . '</td></tr></table>';
    echo '<table><tr><th colspan="4">10 legjobb tanuló az iskolában</th></tr><tr><td>grade</td><td>class</td><td>name</td><td>average</td></tr>';
    foreach ($students as $student){
        echo '<tr><td>' . $student[0] . '</td><td>' . $student[1] . '</td><td>' . $student[2] . '</td><td>' . $student[3] . '</td></tr>';
    }
}