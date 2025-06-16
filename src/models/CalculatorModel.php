<?php
namespace Models;

use Models\DatabaseModel;

class CalculatorModel {
    private $db;
    private $display;
    private $expression;

    public function __construct($state) {
        $this->db = new DatabaseModel();
        $this->display = $state['display'] ?? '0';
        $this->expression = $state['expression'] ?? '';
    }

    public function processInput($button) {
        error_log("Processing input: {$button}, current display={$this->display}, expression={$this->expression}");
        switch ($button) {
            case 'C':
                $this->clear();
                break;
            case '⌫':
                $this->backspace();
                break;
            case '=':
                $result = $this->calculate($this->expression);
                if ($result === null) {
                    $this->display = 'Error';
                    $this->expression = '';
                } else {
                    $this->display = $this->formatResult($result['result']);
                    $this->expression = $this->display;
                    $this->db->createCalculation($result);
                }
                break;
            case '+':
            case '-':
            case '×':
            case '÷':
            case '*':
            case '/':
                $this->addOperator($button === '*' ? '×' : ($button === '/' ? '÷' : $button));
                break;
            case '.':
                $this->addDecimal();
                break;
            default:
                if (is_numeric($button)) {
                    $this->addNumber($button);
                }
                break;
        }
        error_log("After processing: display={$this->display}, expression={$this->expression}");
        return [
            'expression' => $this->expression,
            'display' => $this->display,
        ];
    }

    private function clear() {
        $this->expression = '';
        $this->display = '0';
    }

    private function backspace() {
        $this->expression = substr($this->expression, 0, -1);
        $this->display = $this->expression ?: '0';
    }

    public function calculate($expression) {
        if (empty($expression)) {
            return null;
        }
        $pattern = '/^(-?\d*\.?\d*)\s*([\+\-\×\÷])\s*(-?\d*\.?\d*)$/';
        if (preg_match($pattern, $expression, $matches)) {
            error_log(" $matches[1]");
            $firstNumber = floatval($matches[1]);
            $operator = $this->convertOperatorSymbol($matches[2]);
            $secondNumber = floatval($matches[3]);
            $result = $this->performCalculation($firstNumber, $secondNumber, $operator);
            if ($result === false) {
                return null;
            }
            return [
                'first_number' => $firstNumber,
                'operation' => $operator,
                'second_number' => $secondNumber,
                'result' => $result
            ];
        }
        return null;
    }

    private function performCalculation($first, $second, $operator) {
        switch ($operator) {
            case '+': return $first + $second;
            case '-': return $first - $second;
            case '*': return $first * $second;
            case '/': return $second != 0 ? $first / $second : false;
            default: return false;
        }
    }

    private function addOperator($operator) {
        error_log("addOperator called with operator: '$operator', expression: '$this->expression'");
        if (!empty($this->expression) || !preg_match('/[\+\-\×\÷]$/', $this->expression)) {
            error_log("Adding operator: '$operator'");
            $this->expression .= " $operator ";
            $this->display = $operator;
        } else {
            error_log("Operator not added: empty expression or ends with operator");
        }
    }

    private function convertOperatorSymbol($operator) {
        return match ($operator) {
            '×' => '*',
            '÷' => '/',
            default => $operator
        };
    }

    private function addDecimal() {
        if (empty($this->expression) || preg_match('/[\+\-\×\÷]$/', $this->expression)) {
            $this->expression .= '0.';
            $this->display = '0.';
        } elseif (!preg_match('/\.\d*$/', $this->expression)) {
            $this->expression .= '.';
            $this->display .= '.';
        }
    }

    private function addNumber($number) {
        error_log("addNumber called with number: '$number', expression: '$this->expression'");
        $this->expression .= $number;
        if ($this->display === '0' || preg_match('/[\+\-\×\÷]$/', $this->display)) {
            $this->display = $number;
        } else {
            $this->display .= $number;
        }
        error_log("After addNumber: expression='{$this->expression}', display='{$this->display}'");
    }

    public function formatResult($result) {
        return rtrim(rtrim(sprintf('%.10f', $result), '0'), '.');
    }

    public function createCalculation($data) {
        return $this->db->createCalculation($data);
    }

    public function selectAll(): array {
        return $this->db->selectAll();
    }
}