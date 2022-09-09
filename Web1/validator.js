"use strict;"
var inp_y = document.getElementById('input-y');

var inp_r1 = document.getElementById('r1');
var inp_r15 = document.getElementById('r15');
var inp_r2 = document.getElementById('r2');
var inp_r25 = document.getElementById('r25');
var inp_r3 = document.getElementById('r3');

var submitButton = document.getElementById('submit-button');
var current_time = document.getElementById('current_time');
var working_time = document.getElementById('working_time');
var table = document.getElementById('check');
var tbody = table.getElementsByTagName('tbody')[0];
var pred_btn = null;
var error_message = "";

var x_value = null;
var y_value = null;
var r_value = null;


/*---------------Проверка данных--------------------*/

function checkX() {
    if (x_value != null) {
        if (x_value == -4 || x_value == -3 || x_value == -2
            || x_value == -1 || x_value == 0 || x_value == 1
            || x_value == 2 || x_value == 3 || x_value == 4) {
            return true;
        }
        else {
            error_message = "X: Не выбрано значение.\n";
            return false;
        }
    }
    else {
        error_message = "X: Не выбрано значение.\n";
        return false;
    }
}


function checkY() {
    const value_string = y_value.replace('\,', '.');
    if (!isNaN(value_string)) {
        const value = Number.parseFloat(value_string);
        if (value <= -3 || value >= 5) {
            error_message = "Число Y не попадает в указанный диапазон\n";
            return false;
        }
        else return true;
    }
    else {
        erorr_message = "Y не число, кого вы обмануть пытаетесь?";
        return false;
    }
}

function checkR() {
    let counter = 0;
    if (inp_r1.checked) {
        counter += 1;
    }
    if (inp_r15.checked) {
        counter += 1;
    }
    if (inp_r2.checked) {
        counter += 1;
    }
    if (inp_r25.checked) {
        counter += 1;
    }
    if (inp_r3.checked) {
        counter += 1;
    }
    if (counter == 1) {
        return true;
    }
    else {
        error_message = "Должно быть выбрано ровно одно значение R.\n";
        return false;
    }

}

function getData() {

    y_value = inp_y.value;
    if (inp_r1.checked) {
        r_value = inp_r1.value;
    }
    if (inp_r15.checked) {
        r_value = inp_r15.value;
    }
    if (inp_r2.checked) {
        r_value = inp_r2.value;
    }
    if (inp_r25.checked) {
        r_value = inp_r25.value;
    }
    if (inp_r3.checked) {
        r_value = inp_r3.value;
    }
}


/*--------Функция обработки ответа-------*/

var Handler = function (request) {
    console.log(request.responseText);
    var response = JSON.parse(request.responseText);
    if (response.correct == "true") {
        updateTable(response);
        updateTime(response);
    }
    else {
        alert("Неправильно введены данные");
    }

}


function updateTime(response) {
    current_time.innerHTML = response.current_time;
    working_time.innerHTML = response.working_time + ' мс';
}

function updateTable(response) {
    var row = document.createElement("tr");
    var cell_x = document.createElement("td");
    var cell_y = document.createElement("td");
    var cell_R = document.createElement("td");
    var cell_hit = document.createElement("td");
    cell_x.innerHTML = response.x;
    cell_y.innerHTML = response.y;
    cell_R.innerHTML = response.R;
    cell_hit.innerHTML = response.res;
    row.appendChild(cell_x);
    row.appendChild(cell_y);
    row.appendChild(cell_R);
    row.appendChild(cell_hit);
    tbody.appendChild(row);
}


/*----------------Отправка данных на сервер------------*/

function sendRequest(r_handler) {
    var r_path = 'handler.php?x='
        + x_value + '&y='
        + y_value + '&R='
        + r_value;

    var request = new XMLHttpRequest();
    if (!request) {
        return;
    }

    request.open("GET", r_path, true);
    request.responseType = 'text';
    request.setRequestHeader('Content-Type', 'application/x-www-form-url');

    request.addEventListener("readystatechange", () => {
        if (request.readyState === 4 && request.status === 200) {
            r_handler(request);
        }
    });

    request.send();
}


/*-------Кнопка отправить--------*/

function sendData() {
    error_message = "";
    getData();
    console.log(x_value + ' ' + y_value + ' ' + r_value);

    var check1 = checkX();
    var check2 = checkY();
    var check3 = checkR();
    if (check1 && check2 && check3) sendRequest(Handler);
    else alert(error_message);
}

submitButton.addEventListener('click', sendData);


/*------------Определение выбранной кнопки-----------*/

const btns = document.querySelectorAll('button[id^=a]')

btns.forEach(btn => {

    btn.addEventListener('click', event => {
        x_value = event.target.value;
        console.log(event.target.value);
        event.target.classList.add("active_btn");
        if (pred_btn == null) pred_btn = event.target;
        else {
            pred_btn.classList.remove("active_btn");
            if (event.target == pred_btn) {
                pred_btn = null;
                x_value = null;
            }
            else pred_btn = event.target;

        }
    });

});
