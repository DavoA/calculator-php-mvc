<?php
namespace Controllers;

use Models\CalculatorModel;

class CalculatorController {
    private $calculatorModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $state = [
            'expression' => $_SESSION['expression'] ?? '',
            'display' => $_SESSION['calculator_display'] ?? '0',
        ];
        error_log("Controller init: expression='{$state['expression']}', display='{$state['display']}'");
        $this->calculatorModel = new CalculatorModel($state);
    }

    public function redirectToCalculate() {
        session_write_close();
        header('Location: ' . URLROOT . '/calculate');
        return;
    }

    public function index($params = []) {
        $data = [
            'result' => $_SESSION['calculator_display'] ?? '0'
        ];
        view('calculator', $data);
    }

    public function submit($params = []) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['button'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $button = $_POST['button'];
            error_log("Submit received button: '$button'");
            $newState = $this->calculatorModel->processInput($button);
            
            $_SESSION['expression'] = $newState['expression'];
            $_SESSION['calculator_display'] = $newState['display'];
            error_log("Submit updated session: expression='{$newState['expression']}', display='{$newState['display']}'");
            
            session_write_close();
            header('Location: ' . URLROOT . '/calculate');
            return;
        }
        $this->redirectToCalculate();
    }

    public function history($params = []) {
        $data = [
            'calculations' => $this->calculatorModel->selectAll()
        ];
        view('history', $data);
    }

    public function compute($params = []) {
        if (!isset($params['operation'], $params['num1'], $params['num2'])) {
            return $this->error(['message' => 'Invalid parameters']);
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $operationMap = [
            'add' => '+',
            'subtract' => '-',
            'multiply' => '×',
            'divide' => '÷'
        ];
        $operator = $operationMap[$params['operation']] ?? '';
        if (!$operator) {
            return $this->error(['message' => 'Invalid operation']);
        }
        $expression = "{$params['num1']} $operator {$params['num2']}";
        $result = $this->calculatorModel->calculate($expression);

        if ($result === null) {
            $_SESSION['calculator_display'] = 'Error';
            $_SESSION['expression'] = '';
        } else {
            $_SESSION['calculator_display'] = $this->calculatorModel->formatResult($result['result']);
            $_SESSION['expression'] = $_SESSION['calculator_display'];
            $this->calculatorModel->createCalculation($result);
        }
        
        session_write_close();
        header('Location: ' . URLROOT . '/calculate');
        return;
    }

    public function error($params = []) {
        $message = $params['message'] ?? 'Page not found';
        http_response_code(404);
        echo "<h1>Error</h1><p>$message</p>";
        return;
    }
}