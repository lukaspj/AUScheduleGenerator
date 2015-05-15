<?php
/**
 * Created by PhpStorm.
 * User: Lukas
 * Date: 03-03-2015
 * Time: 16:31
 */

$title = "Indtast Årskortnummer:";
$placeholder = "Årskort";
$checktext = "Vis tidligere uger: ";
$submit = "Gå til skema";
$urlprepend = "\"\"";
$localization = '<a href="./en">en</a>';
$supports = "Virker kun for Science And Technology, Arts og BSS";
$add_student_card = "Tilføj Årskort";

if(isset($_GET['en']) && $_GET['en'] == "1")
{
    $title = "Input Studentcard-number:";
    $placeholder = "Studentcard";
    $checktext = "Show previous weeks: ";
    $submit = "Go to schedule";
    $urlprepend = "\"en/\"";
    $localization = '<a href="./">da</a>';
    $supports = "Only works for Science And Technology, Arts and BSS";
    $add_student_card = "Add Studentcard";
}


?>
<!doctype html>
<html class="no-js" lang="en">
<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AU schedule generator</title>
    <link rel="stylesheet" href="/auskema/css/foundation.css" />
    <script src="/auskema/js/vendor/modernizr.js"></script>

    <link rel="stylesheet" href="/auskema/style.css">
    <link rel="stylesheet" href="/auskema/generated.css">
    <link rel="script" href="/auskema/popup.js">

    <script src="/auskema/popup.js"></script>

    <script type="text/javascript" language="JavaScript">
        var inputCount = 1;
        window.onload=function() {
            document.getElementById("aarskortSubmit").onsubmit=function() {
                var allweeks = document.getElementById("allweeksCheck").checked == true ? "/allweeks" : "";
                var aarskort = document.getElementById("aarskortText1").value;
                for(var i = 2; i <= inputCount; i++)
                    if(document.getElementById("aarskortText" + i).value != "")
                        aarskort += ";" + document.getElementById("aarskortText" + i).value;
                window.location = "./" + <?=$urlprepend;?> + aarskort + allweeks;
                return false;
            };
            var addInput = document.getElementById("addInput");
            addInput.onclick = function () {
                inputCount++;
                var newInput = document.createElement("input");
                newInput.type = "text";
                newInput.className = "flat_inputText";
                newInput.setAttribute("placeholder", "<?=$placeholder?>");
                newInput.id = "aarskortText" + inputCount;
                newInput.name = "aarskort" + inputCount;
                addInput.parentNode.parentNode.insertBefore(newInput, addInput.parentNode);
                var contentWrapper = document.getElementsByClassName("content-wrapper")[0];
                var content = document.getElementsByClassName("content")[0];
                content.style.height = (350 + 53*(inputCount-1)) + "px";
                contentWrapper.style.marginTop = (-175-(27*(inputCount-1))) + "px";
                return false;
            }
        }
    </script>

</head>
<body>
    <div class="content-wrapper">
        <div class="content panel">
            <h3><?=$title?></h3>
            <form id="aarskortSubmit" action="DisplaySkema.php" method="get">
                <div id="inputs">
                    <input type="text" class="flat_inputText" placeholder="<?=$placeholder?>" id="aarskortText1" name="aarskort">
                    <!-- <div class="add-card"><a id="addInput" href="">+</a></div> -->
                    <a class="small button" id="addInput" href=""><?=$add_student_card?></a>
                </div>
                <span style="font-size: 8pt"><i><?=$supports;?></i></span><br>
                <label for="allweeksCheck"><?=$checktext?></label>
                <input type="checkbox" id="allweeksCheck" name="allweeks"><br>
                <input class="small-10 success button" type="submit" value="<?=$submit?>">
            </form>
            <div id="footer">
                <div class="disclaimer-box"><a data-reveal-id="disclaimerModal" href="#">Disclaimer</a></div>
                <div class="changelog-container"><a data-reveal-id="changeLogModal" href="">Changelog</a></div>
                <div class="localization-box"><?=$localization;?></div>
            </div>
        </div>
    </div>

    <div id="disclaimerModal" class="reveal-modal" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
        <h2 id="modalTitle">Disclaimer</h2>
        <p>
            Use of this week-schedule generator is at your own risk. I do
            not take responsibility for any discrepancies between the schedule
            displayed here, and the actual schedule.<br />
            Feel free to check the generated scheme against your actual scheme.
        </p>
        <p>
            The Science and Technology schedule is retrieved from:
            <a href="http://services.science.au.dk/apps/skema/VaelgElevskema.asp?webnavn=skema">http://services.science.au.dk/apps/skema/VaelgElevskema.asp?webnavn=skema</a><br />
            The BSS and Arts schedule is retrieved from <a href="http://springschedule.au.dk/dk/">http://springschedule.au.dk/dk/</a> and
            <a href="http://autumnschedule.au.dk/dk/">http://autumnschedule.au.dk/dk/</a>.<br />
            <br />
            The schedule is regenerated once every week, you might feel a slow
            loading time because of this, but in between these regenerations,
            display of your schedule should happen quickly.
        </p>
        <a class="close-reveal-modal" aria-label="Close">&#215;</a>
    </div>

    <div id="changeLogModal" class="reveal-modal" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
        <h2 id="modalTitle">Changelog</h2>
        <p class="lead">15-04-2015</p>
        <ul>
            <li>Major redesign.</li>
        </ul>
        <p class="lead">08-04-2015</p>
        <ul>
            <li>Fixed a bug where some classes wouldn't show.</li>
        </ul>
        <p class="lead">11-03-2014</p>
        <ul>
            <li>Added changelog</li>
            <li>Added support for multiple studentcards</li>
        </ul>
        <a class="close-reveal-modal" aria-label="Close">&#215;</a>
    </div>

    <script src="/auskema/js/vendor/jquery.js"></script>
    <script src="/auskema/js/foundation.min.js"></script>
    <script>
        $(document).foundation();
    </script>
</body>
</html>