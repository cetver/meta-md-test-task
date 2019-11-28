<?php

interface Validator
{
    public function validate(string $value): bool;
}

class ExpressionValidator implements Validator
{
    private const OPEN_BRACKET = '(';
    private const CLOSE_BRACKET = ')';
    private const OPEN_SQUARE_BRACKET = '[';
    private const CLOSE_SQUARE_BRACKET = ']';
    private const OPEN_BRACE = '{';
    private const CLOSE_BRACE = '}';
    /**
     * @var SplStack
     */
    private $stack;

    public function __construct(SplStack $stack)
    {
        $this->stack = $stack;
    }

    public function validate(string $value): bool
    {
        $bracketInitialValue = 1;
        $squareBracketInitialValue = 2;
        $braceInitialValue = 3;
        // подразумевается что тут не будут передаваться многобайтовые строки (ext-mbstring)
        $valueLength = strlen($value);
        for ($i = 0; $i < $valueLength; $i++) {
            $char = $value[$i];
            switch ($char) {
                default:
                    break;
                case self::OPEN_BRACKET:
                    $this->stack->push($bracketInitialValue);
                    break;
                case self::CLOSE_BRACKET:
                    try {
                        // Выбрасывает исключение RuntimeException, когда структура данных пуста.
                        if ($this->stack->pop() !== $bracketInitialValue) {
                            return false;
                        }
                    } catch (RuntimeException $e) {
                        return false;
                    }
                    break;
                case self::OPEN_SQUARE_BRACKET:
                    $this->stack->push($squareBracketInitialValue);
                    break;
                case self::CLOSE_SQUARE_BRACKET:
                    try {
                        // Выбрасывает исключение RuntimeException, когда структура данных пуста.
                        if ($this->stack->pop() !== $squareBracketInitialValue) {
                            return false;
                        }
                    } catch (RuntimeException $e) {
                        return false;
                    }
                    break;
                case self::OPEN_BRACE:
                    $this->stack->push($braceInitialValue);
                    break;
                case self::CLOSE_BRACE:
                    try {
                        // Выбрасывает исключение RuntimeException, когда структура данных пуста.
                        if ($this->stack->pop() !== $braceInitialValue) {
                            return false;
                        }
                    } catch (RuntimeException $e) {
                        return false;
                    }
                    break;
            }
        }

        return $this->stack->isEmpty();
    }
}

$dataProvider = [
    ['current' => '(1+1)', 'expected' => true],
    ['current' => '(1+1', 'expected' => false],
    ['current' => '1+1)', 'expected' => false],

    ['current' => '[1+1]', 'expected' => true],
    ['current' => '[1+1', 'expected' => false],
    ['current' => '1+1]', 'expected' => false],

    ['current' => '{1+1}', 'expected' => true],
    ['current' => '{1+1', 'expected' => false],
    ['current' => '1+1}', 'expected' => false],

    ['current' => '([{ ([{1+1}]) + (({{[[1+1]]}})) }])', 'expected' => true],
    ['current' => '([{ ([{1+1}]) + (({{[[1+1]]}}))', 'expected' => false],
];

function test(array $dataProvider)
{
    foreach ($dataProvider as $item) {
        $current = $item['current'];
        $expected = $item['expected'];
        $validator = new ExpressionValidator(new SplStack());
        $result = $validator->validate($item['current']);
        $message = "Expression $current ";
        $message .= ($result == $expected) ? 'passed' : 'FAILS';
        echo $message;
        echo "\n";
    }
}

test($dataProvider);