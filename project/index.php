<?php
require_once 'backend/sdbh.php';
$dbh = new sdbh();

// Обработка отправки формы расчета
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из формы и их фильтрация
    $selected_product_id = isset($_POST['product']) ? intval($_POST['product']) : 0;
    $days = isset($_POST['days']) ? intval($_POST['days']) : 1;

    // Получение данных о выбранном продукте из БД
    $product = $dbh->mselect_rows('a25_products', ['ID' => $selected_product_id], 0, 1, 'ID, NAME, PRICE, TARIFF')[0];

    // Расчет стоимости продукта
    $product_price = calculate_product_price($product, $days);

    // Расчет стоимости дополнительных услуг
    $additional_services = isset($_POST['services']) ? $_POST['services'] : [];
    $services_price = calculate_services_price($dbh, $additional_services, $days);

    // Итоговая стоимость
    $total_price = $product_price + $services_price;
}

// Функция для расчета стоимости продукта
function calculate_product_price($product, $days)
{
    $tariffs = unserialize($product['TARIFF']);
    foreach ($tariffs as $days_limit => $price) {
        if ($days >= $days_limit) {
            return $price * $days;
        }
    }
    // Если не указан тариф для указанного количества дней, берется базовая цена
    return $product['PRICE'] * $days;
}

// Функция для расчета стоимости дополнительных услуг
function calculate_services_price($dbh, $selected_services, $days)
{
    // Получение списка дополнительных услуг из базы данных
    $services = unserialize($dbh->mselect_rows('a25_settings', ['set_key' => 'services'], 0, 1, 'id')[0]['set_value']);
    $total_price = 0;
    foreach ($selected_services as $service_id) {
        foreach ($services as $service) {
            if ($service['id'] == $service_id) {
                $total_price += $service['price'] * $days;
                break;
            }
        }
    }
    return $total_price;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма расчета стоимости</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <div class="row row-header">
        <div class="col-12">
            <img src="assets/img/logo.png" alt="logo" style="max-height:50px"/>
            <h1>Прокат</h1>
        </div>
    </div>
    <div class="row row-body">
        <div class="col-12">
            <h4>Дополнительные услуги:</h4>
            <ul>
                <?php
                // Получение списка дополнительных услуг из базы данных
                $services = unserialize($dbh->mselect_rows('a25_settings', ['set_key' => 'services'], 0, 1, 'id')[0]['set_value']);
                foreach ($services as $service) {
                    echo "<li>{$service['name']}: {$service['price']}</li>";
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="row row-form">
        <div class="col-12">
            <h4>Форма расчета:</h4>
            <form method="post">
                <div class="mb-3">
                    <label for="product" class="form-label">Выберите продукт:</label>
                    <select class="form-select" id="product" name="product">
                        <?php
                        // Получение списка продуктов из БД и вывод их в селекторе
                        $products = $dbh->mselect_rows('a25_products', [], 0, 1000, 'ID, NAME');
                        foreach ($products as $product) {
                            echo "<option value=\"{$product['ID']}\">{$product['NAME']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="days" class="form-label">Количество дней:</label>
                    <input type="number" class="form-control" id="days" name="days" value="1" min="1" max="30">
                </div>
                <div class="mb-3">
                    <label class="form-label">Дополнительно:</label>
                    <?php
                    // Вывод чекбоксов для дополнительных услуг
                    foreach ($services as $service) {
                        echo "<div class=\"form-check\">";
                        echo "<input class=\"form-check-input\" type=\"checkbox\" id=\"service{$service['id']}\" name=\"services[]\" value=\"{$service['id']}\">";
                        echo "<label class=\"form-check-label\" for=\"service{$service['id']}\">{$service['name']} за {$service['price']}</label>";
                        echo "</div>";
                    }
                    ?>
                </div>
                <button type="submit" class="btn btn-primary">Рассчитать</button>
            </form>
            <?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>
                <div class="mt-3">
                    <h5>Итоговая стоимость:</h5>
                    <p><?= isset($total_price) ? htmlspecialchars($total_price) : "Выберите продукт и количество дней" ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>
