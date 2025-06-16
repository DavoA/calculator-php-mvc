<?php
$router->addRoute('', [
    'controller' => 'Calculator',
    'action' => 'redirectToCalculate'
]);

$router->addRoute('calculate/{operation}/{num1:-?\d*\.?\d+}/{num2:-?\d*\.?\d+}', [
    'controller' => 'Calculator',
    'action' => 'compute'
]);

$router->addRoute('calculate', [
    'controller' => 'Calculator',
    'action' => 'index'
]);

$router->addRoute('calculate/submit', [
    'controller' => 'Calculator',
    'action' => 'submit'
]);

$router->addRoute('calculate/history', [
    'controller' => 'Calculator',
    'action' => 'history'
]);

$router->addRoute('.*', [
    'controller' => 'Calculator',
    'action' => 'error'
]);