<?php
$tests = [
    [[6, 3, 2, 2], 10],
    [[8, 0, 4, 6], 10],
    [[1, 9, 2, 5], 10],
    [[3, 3, 2, 1], 10],
    [[4, 4, 6, 8], 10],
    [[9, 9, 9, 9], 10],
    // 以下は簡易版では解けなかった問題
    [[4, 0, 6, 8], 10],
    [[9, 5, 2, 1], 10],
    [[1, 3, 2, 3], 10],
    [[6, 4, 8, 4], 10],
    [[3, 4, 7, 8], 10],
    [[1, 1, 5, 8], 10],
    // 以下は動画内で実際に出題されていた問題
    [[2, 5, 5, 9], 13],
    [[4, 8, 8, 9], 23],
    [[3, 5, 8, 9], 24],
    [[1, 3, 5, 7], 19],
    [[1, 2, 2, 6, 6, 9], 16],
    [[2, 3, 4, 6, 8, 9], 9],
    [[1, 2, 3, 3, 3, 9], 13],
    [[1, 4, 5, 6, 6, 6], 9],
    [[1, 1, 4, 6, 6, 8], 17],
    [[4, 4, 5, 7, 8], 16],
    [[3, 3, 4, 7], 25],
    [[2, 2, 6, 8, 9], 17],
    [[1, 8, 9, 9], 16],
    [[2, 5, 5, 6, 6, 8], 15],
    [[2, 2, 3, 6, 8], 12],
    [[6, 6, 7, 8, 9], 22],
    [[1, 6, 8, 8], 18],
    [[3, 3, 4, 8], 19],
    [[5, 7, 8, 9], 20],
    [[2, 2, 4, 6, 7, 9], 13],
    [[5, 6, 9, 9], 8],
    [[1, 2, 3, 4, 8, 9], 22],
    [[1, 2, 7, 8], 17],
    [[1, 1, 3, 6, 9], 14],
    [[2, 6, 7, 9], 21],
    [[2, 4, 6, 7], 23],
    [[1, 4, 4, 6, 8], 12],
    [[1, 3, 5, 9], 14],
    [[1, 1, 3, 5, 5, 7], 24],
    [[1, 2, 2, 3, 6, 9], 25],
    [[3, 4, 5, 6, 6], 23],
    [[1, 4, 7, 9], 14],
    [[2, 4, 6, 7, 8], 17],
    [[2, 3, 7, 8], 23],
    [[6, 6, 7, 8, 9], 22],
    [[1, 2, 7, 7, 8], 10],
    [[1, 3, 3, 4, 6, 8], 15],
    [[2, 2, 5, 6], 12],
    [[1, 6, 8, 8], 18],
    [[1, 3, 4, 6], 13],
    [[2, 6, 6, 8, 8], 19],
    [[3, 4, 4, 9], 11],
    [[1, 2, 6, 9], 10],
    [[1, 4, 6, 9], 17],
    [[4, 7, 8, 8], 10],
    [[1, 6, 8, 9], 18],
    [[3, 4, 6, 8], 18],
    [[1, 2, 4, 8, 9], 13],
    [[3, 7, 8, 8], 18],
    [[1, 2, 3, 3, 6], 18],
    [[5, 6, 7, 8], 21],
    [[4, 5, 9, 9, 9], 17],
    [[5, 6, 7, 9], 19],
    [[1, 3, 4, 8, 8, 9], 14],
    [[3, 6, 6, 8], 21],
    [[1, 2, 7, 7, 8], 17],
    [[2, 3, 6, 6], 19],
];

foreach ($tests as [$numbers, $answer]) {
    // 与えられた数をオペランドの配列の形にして、solve() 関数で答えを見つける
    $operands = [];
    foreach ($numbers as $number) {
        $operands[] = new Operand(strval($number), $number);
    }
    $expression = solve($operands, $answer);

    echo ($expression ? sprintf('%s = %d', $expression, $answer) : '解けませんでした') . PHP_EOL;
}

// オペランド（項）を表すクラス
final readonly class Operand
{
    public function __construct(
        public string $expression,
        public int|float $value,
    ) {
    }
}

// オペランドの配列を渡すと、与えられた答えになる計算式を返す関数
/** @param Operand[] $operands
 * @throws Exception
 */
function solve(array $operands, int $answer): ?string
{
    // 次に計算する2つのオペランドの組み合わせを全パターン列挙
    $nextTwoOperandsCombinations = [];
    for ($i = 0; $i < count($operands); $i++) {
        for ($j = 0; $j < count($operands); $j++) {
            if ($i !== $j) {
                $nextTwoOperandsCombinations[] = [$operands[$i], $operands[$j]];
            }
        }
    }

    // 全パターンについて四則演算をすべて試す
    foreach ($nextTwoOperandsCombinations as [$operand1, $operand2]) {
        foreach (['+', '-', '*', '/'] as $operation) {

            // $operand2が0で割り算の場合はスキップ
            if (isEqual($operand2->value, 0) && $operation === '/') {
                continue;
            }

            $result = calculate($operand1, $operand2, $operation);

            // 残っているオペランドを列挙
            $restOperands = [];
            foreach ($operands as $operand) {
                if ($operand !== $operand1 && $operand !== $operand2) {
                    $restOperands[] = $operand;
                }
            }

            // もうオペランドが残っていない場合は、計算結果が答えになっていたら処理終了
            if (count($restOperands) === 0 && isEqual($result, $answer)) {
                return sprintf('(%s %s %s)', $operand1->expression, $operation, $operand2->expression);
            }
            if (count($restOperands) > 0) {
                $restOperands[] = new Operand(
                    sprintf('(%s %s %s)', $operand1->expression, $operation, $operand2->expression),
                    $result,
                );
                $expression = solve($restOperands, $answer);
                if ($expression !== null) {
                    return $expression;
                }
            }
        }
    }

    // 全パターンについて四則演算をすべて試しても答えが見つからなかった場合は、解なしの意味で null を返す
    return null;
}

// 誤差を無視して2つの数値が等しいかどうかを判定する関数（手抜き）
function isEqual(int|float $a, int|float $b): bool
{
    return abs($a - $b) < 0.0001;
}

// 2つのオペランドを指定の演算子で計算する関数
/**
 * @throws Exception
 */
function calculate(Operand $operand1, Operand $operand2, string $operation): float|int
{
    return match ($operation) {
        '+' => $operand1->value + $operand2->value,
        '-' => $operand1->value - $operand2->value,
        '*' => $operand1->value * $operand2->value,
        '/' => $operand1->value / $operand2->value,
        default => throw new Exception("不明な演算子: {$operation}"),
    };
}
