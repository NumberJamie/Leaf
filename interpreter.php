<?php
enum Operations: string {
    case IncreasePointer = '>';
    case DecreasePointer = '<';
    case IncreaseValue = '+';
    case DecreaseValue = '-';
    case OutputValue = '.';
    case InputValue = ',';
    case JumpZero = '[';
    case JumpNotZero = ']';
}

class Interpreter {
    private array $stack;
    private int $stackSize = 30000;
    private string $allowedCharacters = '<>+-.,[]';

    function __construct() {
        $this->stack = array_fill(0, $this->stackSize, 0);
    }

    function runProgramFile(string $filepath): void {
        $operations = $this->fileString($filepath);
        $this->runProgramString($operations);
    }

    function runProgramString(string $operations): void {
        $jumpLookup = $this->seekJumps($operations);
        $operationArray = str_split($operations);
        $pointer = 0;
        $operationIndex = 0;
        while ($operationIndex < strlen($operations)) {
            $currentOperator = $operationArray[$operationIndex];
            $operationIndex += 1;
            if ($currentOperator === Operations::IncreasePointer->value) {
                if ($pointer >= $this->stackSize) { throw new CompileError('Pointer out of bounds.'); }
                $pointer++;
            }
            if ($currentOperator === Operations::DecreasePointer->value) {
                if ($pointer <= 0) { throw new CompileError('Pointer out of bounds.'); }
                $pointer--;
            }
            if ($currentOperator === Operations::IncreaseValue->value) {
                if ($pointer >= 256) { throw new CompileError('Value larger or equal to 256.'); }
                $this->stack[$pointer] = $this->stack[$pointer] + 1;
            }
            if ($currentOperator === Operations::DecreaseValue->value) {
                if ($this->stack[$pointer] < 0) { throw new CompileError('Value is negative.'); }
                $this->stack[$pointer] = $this->stack[$pointer] - 1;
            }
            if ($currentOperator === Operations::OutputValue->value) {
                echo chr($this->stack[$pointer]);
            }
            if ($currentOperator === Operations::InputValue->value) {
                echo 'Enter input integer value: ';
                $inputValue = (int)readline();
                if ($inputValue <= 0) { throw new CompileError('Value is negative.'); }
                if ($inputValue >= 256) { throw new CompileError('Value larger or equal to 256.'); }
                $this->stack[$pointer] = $inputValue;
            }
            if ($currentOperator === Operations::JumpZero->value) {
                if ($this->stack[$pointer] !== 0) continue;
                $operationIndex = $jumpLookup[$operationIndex - 1] + 1;
            }
            if ($currentOperator === Operations::JumpNotZero->value) {
                if ($this->stack[$pointer] === 0) continue;
                $operationIndex = array_search($operationIndex - 1, $jumpLookup) + 1;
            }
        }
    }

    private function fileString(string $filepath): string {
        $filteredString = '';
        $fileContent = file_get_contents($filepath);
        if (!$fileContent) { throw new ValueError('Could not read file content.'); }
        for ($index = 0; $index < strlen($fileContent); $index++){
            if (str_contains($this->allowedCharacters, $fileContent[$index])) {
                $filteredString .= $fileContent[$index];
            }
        }
        return $filteredString;
    }

    private function seekJumps(string $operations): array {
        $jumpLookup = [];
        $openingIndexes = [];
        for ($index = 0; $index < strlen($operations); $index++) {
            $char = $operations[$index];
            if ($char === Operations::JumpZero->value) {
                $openingIndexes[] = $index;
            }
            if ($char === Operations::JumpNotZero->value) {
                if (count($openingIndexes) === 0){ throw new CompileError('Jump mismatch'); }
                $lastElement = array_pop($openingIndexes);
                $jumpLookup[$lastElement] = $index;
            }
        }
        return $jumpLookup;
    }
}

$interpreter = new Interpreter();
$interpreter->runProgramFile('example.bf');