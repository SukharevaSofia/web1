"use strict;"
const inp_y = document.getElementById('input-y');

const inp_r1 = document.getElementById('r1');
const inp_r15 = document.getElementById('r15');
const inp_r2 = document.getElementById('r2');
const inp_r25 = document.getElementById('r25');
const inp_r3 = document.getElementById('r3');

const submitButton = document.getElementById('submit-button');
const current_time = document.getElementById('current_time');
const working_time = document.getElementById('working_time');
const table = document.getElementById('check');
const tbody = document.getElementById('results');
var pred_btn = null;


var error_message = "";
var x_value = null;
var y_value = null;
var r_value = null;

/* getting the X value */

const btns = document.querySelectorAll('button[id^=x]')

btns.forEach((btn) => {
    btn.addEventListener("click", (event) => {
      x_value = event.target.value;
      console.log(event.target.value);
      btns.forEach(
        (btnToClean) => {
          btnToClean.classList.remove("active_btn");
        },
      );
      event.target.classList.add("active_btn");
    });
  });


/* getting the Y and R values */

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


/* value check*/

function checkX() {
    if (x_value != null) { 
        return true;
    }
    else {
        error_message = "X: Не выбрано значение.\n";
        return false;
    }
}


function checkY() {
    if (String(y_value.replace('-', '')).length > 5){
        error_message = "Введите число Y с точностью до 3 знака после запятой";
        return false;
    }
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
        error_message = "Y не число :(";
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


/*response manegment function*/

var Handler = function (request) {
    console.log(request.responseText);
    var response = JSON.parse(request.responseText);
    if (response.valid == "true") {
        updateTable(response);
        updateTime(response);
    }
    else {
        alert("Неправильно введены данные");
    }

}

var restoreHandler = function (request) {
    const array = JSON.parse(request.responseText);
    array.forEach((data) => {
      updateTable(JSON.parse(data));
    });
  };

function restoreRequest(r_handler) {
const r_path = "handler.php?restore";
const request = new XMLHttpRequest();
if (!request) {
    return;
}
request.open("GET", r_path, true);
request.responseType = "text";
request.setRequestHeader("Content-Type", "application/x-www-form-url");
request.addEventListener("readystatechange", () => {
    if (request.readyState === 4 && request.status === 200) {
    r_handler(request);
    }
});
request.send();
}

restoreRequest(restoreHandler);



function updateTable(response) {
    var row = document.createElement("tr");
    var cell_x = document.createElement("td");
    var cell_y = document.createElement("td");
    var cell_R = document.createElement("td");
    var cell_hit = document.createElement("td");
    var cell_cur_time = document.createElement("td");
    var cell_work_time = document.createElement("td")

    date = new Date();
    Hour = date.getHours();
    Minutes = date.getMinutes();
    Seconds = date.getSeconds();

    cell_x.innerHTML = response.x;
    cell_y.innerHTML = response.y;
    cell_R.innerHTML = response.R;
    cell_hit.innerHTML = response.res ? "Попадание" : "Нет попадания";
    cell_cur_time.innerHTML =  Hour + ":" + Minutes + ":" + Seconds;
    cell_work_time.innerHTML = response.working_time + ' нс'
    row.appendChild(cell_x);
    row.appendChild(cell_y);
    row.appendChild(cell_R);
    row.appendChild(cell_hit);
    row.appendChild(cell_cur_time);
    row.appendChild(cell_work_time);
    tbody.insertBefore(row, tbody.firstChild);
}


/*Sending data to server*/

function sendRequest(r_handler) {
    var r_path = 'handler.php?x='
        + x_value + '&y='
        + y_value + '&R='
        + r_value;

    var request = new XMLHttpRequest();


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


/* "Send" button */

function sendData() {
    error_message = "Неправильно введены данные";
    getData();
    console.log(x_value + ' ' + y_value + ' ' + r_value);

    if (checkX() && checkY() && checkR())
    {
        sendRequest(Handler);
    }
    else 
    {
        alert(error_message);
    }
}

submitButton.addEventListener('click', sendData);
