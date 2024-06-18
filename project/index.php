<?php
// Подключаем класс базы данных
require_once 'backend/sdbh.php';

// Создаем экземпляр объекта базы данных
$dbh = new sdbh();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма расчета стоимости</title>
    <!-- Подключаем стили Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Подключаем скрипт Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <!-- Стили для скрытия блока результата -->
    <style>
        #resultBlock {
            display: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row row-header">
        <div class="col-12">
            <!-- Логотип и заголовок -->
            <img src="assets/img/logo.png" alt="logo" style="max-height:50px"/>
            <h1>Прокат</h1>
        </div>
    </div>
    <div class="row row-body">
        <div class="col-12">
            <h4>Дополнительные услуги:</h4>
            <ul>
                <?php
                // Получаем и десериализуем список дополнительных услуг из базы данных
                $services = unserialize($dbh->mselect_rows('a25_settings', ['set_key' => 'services'], 0, 1, 'id')[0]['set_value']);
                foreach($services as $k => $s) { ?>
                    <!-- Выводим список услуг -->
                    <li><?= htmlspecialchars($k) ?>: <?= htmlspecialchars($s) ?></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="row row-body">
        <div class="col-9">
            <form method="post" id="form">
                <div class="mb-3">
                    <label for="product" class="form-label">Выберите продукт:</label>
                    <!-- Выпадающий список для выбора продукта -->
                    <select class="form-select" name="product" id="product">
                        <option value="100">Продукт 1 за 100</option>
                        <option value="200">Продукт 2 за 200</option>
                        <option value="300">Продукт 3 за 300</option>
                        <option value="400">Продукт 4 за 400</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="days" class="form-label">Количество дней:</label>
                    <!-- Поле для ввода количества дней -->
                    <input type="number" class="form-control" id="days" name="days" min="1" max="30">
                </div>
                <div class="mb-3">
                    <label for="services" class="form-label">Дополнительно:</label>
                    <!-- Чекбоксы для выбора дополнительных услуг -->
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="100" id="flexCheckChecked1" name="services[]">
                        <label class="form-check-label" for="flexCheckChecked1">
                            Дополнительно 1 за 100
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="200" id="flexCheckChecked2" name="services[]">
                        <label class="form-check-label" for="flexCheckChecked2">
                            Дополнительно 2 за 200
                        </label>
                    </div>
                </div>
                <!-- Кнопка для отправки формы -->
                <button type="submit" class="btn btn-primary">Рассчитать</button>
            </form>
        </div>
    </div>
    <div class="row mt-5" id="resultBlock">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Результат расчета:</h5>
                </div>
                <div class="card-body">
                    <!-- Блок с результатами расчета -->
                    <p><strong>Выбранный продукт:</strong> <span id="selectedProduct"></span></p>
                    <p><strong>Количество дней:</strong> <span id="selectedDays"></span></p>
                    <p><strong>Дополнительные услуги:</strong></p>
                    <ul id="selectedServicesList"></ul>
                    <p><strong>Итоговая стоимость:</strong> <span id="totalCost"></span> рублей</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Сценарий JavaScript для обработки формы и вывода результатов
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById('form');
        const resultBlock = document.getElementById('resultBlock');
        const selectedProduct = document.getElementById('selectedProduct');
        const selectedDays = document.getElementById('selectedDays');
        const selectedServicesList = document.getElementById('selectedServicesList');
        const totalCost = document.getElementById('totalCost');

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Получаем данные из формы
            let productPrice = parseInt(form.product.value);
            let days = parseInt(form.days.value);
            let additionalServicesCost = 0;
            let services = [];

            // Обрабатываем выбранные дополнительные услуги
            form.querySelectorAll('input[name="services[]"]:checked').forEach(function(service) {
                additionalServicesCost += parseInt(service.value) * days;
                services.push(service.nextElementSibling.textContent);
            });

            // Вычисляем общую стоимость
            let total = productPrice * days + additionalServicesCost;

            // Выводим результаты на страницу
            selectedProduct.textContent = form.product.options[form.product.selectedIndex].text;
            selectedDays.textContent = days;
            selectedServicesList.innerHTML = '';
            services.forEach(function(service) {
                let li = document.createElement('li');
                li.textContent = service;
                selectedServicesList.appendChild(li);
            });
            totalCost.textContent = total;

            // Показываем блок с результатом
            resultBlock.style.display = 'block';
        });
    });
</script>
</body>
</html>
