<?php
if (isset($_GET["id"]) && isset($_GET["request_id"]) && isset($_GET["code"])) {
    header("Location: https://minecraft.id/#/demo/" . $_GET["id"] . "," . $_GET["request_id"] . "," . $_GET["code"]);
}
