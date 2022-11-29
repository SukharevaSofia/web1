<!DOCTYPE html>
<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" %>
<jsp:useBean id="chartBean" class="beans.DataBean" scope="session" />
<html>

<head>
    <meta charset="utf-8">
    <title>✨🧼🥀👁️‍🗨️❤️‍🔥</title>
    <link rel="stylesheet" href="styles/index.css">
    <link rel="icon" href="styles/icon.png">
    <script src="js/validator.js" defer></script>
</head>


<body>
<table class="maintable" cellpadding="10" cellspacing="0" width="100%">
    <tr class="graphics">
        <td class="boxheaderl" colspan="2">Вариант 3167</td>
        <td class="boxheaderr" colspan="1">Сухарева Софья, Р32131</td>
    </tr>
    <tr>
        <td rowspan="4" align="center">
            <canvas id="graph" height="280" width="400">Что-то не так с вашим браузером :(</canvas>
            <script src="js/canvas.js"></script>
        </td>
        <td class="area">
            <!--area here-->
        </td>
        <td>
            Изменение X:
            <table class="x-select" id="x-select">
                <tr>
                    <td><button class="x-button" id="x5" name="x" value="5">5</button></td>
                    <td><button class="x-button" id="x4" name="x" value="4">4</button></td>
                    <td><button class="x-button" id="x3" name="x" value="3">3</button></td>
                </tr>
                <tr>
                    <td><button class="x-button" id="x2" name="x" value="2">2</button></td>
                    <td><button class="x-button" id="x1" name="x" value="1">1</button></td>
                    <td><button class="x-button" id="x0" name="x" value="0">0</button></td>
                </tr>
                <tr>
                    <td><button class="x-button" id="x-1" name="x" value="-1">-1</button></td>
                    <td><button class="x-button" id="x-2" name="x" value="-2">-2</button></td>
                    <td><button class="x-button" id="x-3" name="x" value="-3">-3</button></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="area">
            <!--area here-->
        </td>
        <td>
            <label for="input-y">Изменение Y:</label>
            <div class="Y-text" id="Y">
                <input id="input-y" placeholder="(-3; 5)">
            </div>
        </td>
    </tr>
    <tr>
        <td class="area">
            <!--area here-->
        </td>
        <td class="inputz">Изменение R:<br>
            <!-- radio -->
            <input type="radio" id="r1" name="r" value="1">
            <label for="r1"> 1</label><br>
            <input type="radio" id="r2" name="r" value="2">
            <label for="r2"> 2</label><br>
            <input type="radio" id="r3" name="r" value="3">
            <label for="r3"> 3</label><br>
            <input type="radio" id="r4" name="r" value="4">
            <label for="r4"> 4</label><br>
            <input type="radio" id="r5" name="r" value="5">
            <label for="r5"> 5</label><br>
        </td>

    </tr>
    <tr>
        <td class="area">
            <!--area here-->
        </td>
        <td>
            <button id="submit-button">Отправить</button>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <table id="check" class="table_check" align="center">
                <tr class="table_header">
                    <th align="left">X</th>
                    <th align="left">Y</th>
                    <th align="left">R</th>
                    <th align="left">Результат</th>
                    <th align="left">Текущее время</th>
                    <th align="left">работа скрипта</th>
                </tr>
                <tbody id="results"></tbody>
            </table>
        </td>
        <td width="30%">

        </td>
    </tr>
</table>
</body>

</html>