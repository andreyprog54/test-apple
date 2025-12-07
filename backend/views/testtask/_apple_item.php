<?php

use common\models\Apple;
use yii\helpers\Html;

/** @var Apple $model */

$remainingPercent = max(0, min(100, 100 - (int)$model->eaten_percent));
$angle = (int)round($remainingPercent * 3.6); // 100% -> 360deg

$colorHex = $model->getColorHex() ?: '#cccccc';
$isRotten = $model->isRotten();
$isOnTree = $model->isOnTree();

// затемняем испорченное яблоко
$filter = $isRotten ? 'brightness(0.4)' : 'none';

$background = sprintf(
    'conic-gradient(%1$s 0deg %2$ddeg, rgba(0,0,0,0.06) %2$ddeg 360deg)',
    $colorHex,
    $angle
);
?>
<div class="card h-100 shadow-sm">
    <div class="card-body d-flex flex-column align-items-center text-center">
        <div
            class="mb-3"
            style="width: 120px; height: 120px; border-radius: 50%; background: <?= Html::encode($background) ?>; filter: <?= Html::encode($filter) ?>; border: 3px solid <?= Html::encode($colorHex) ?>; box-shadow: 0 0 10px rgba(0,0,0,0.15);">
        </div>

        <h5 class="card-title mb-2">Яблоко #<?= Html::encode($model->id) ?></h5>
        <p class="mb-1 small text-muted">
            Цвет: <?= Html::encode($model->color) ?>,
            осталось: <?= Html::encode($remainingPercent) ?>%
        </p>

        <?php if ($isRotten): ?>
            <p class="text-danger small mb-2"><?= Html::encode($model->getStatusLabel()) ?></p>
        <?php else: ?>
            <p class="small mb-2"><?= Html::encode($model->getStatusLabel()) ?></p>
        <?php endif; ?>

        <div class="mt-auto d-flex flex-wrap justify-content-center gap-2">
            <?php if ($isOnTree): ?>
                <?= Html::a('Упасть', ['fall', 'id' => $model->id], [
                    'class' => 'btn btn-warning btn-sm',
                    'data' => [
                        'method' => 'post',
                        'pjax' => 1,
                    ],
                ]) ?>
            <?php elseif (!$isRotten && $remainingPercent > 0): ?>
                <?= Html::a('Съесть целиком', ['eat', 'id' => $model->id, 'percent' => 100], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'method' => 'post',
                        'pjax' => 1,
                    ],
                ]) ?>
                <?= Html::a('Съесть половину', ['eat', 'id' => $model->id, 'percent' => 50], [
                    'class' => 'btn btn-outline-danger btn-sm',
                    'data' => [
                        'method' => 'post',
                        'pjax' => 1,
                    ],
                ]) ?>
                <?= Html::a('Съесть четверть', ['eat', 'id' => $model->id, 'percent' => 25], [
                    'class' => 'btn btn-outline-danger btn-sm',
                    'data' => [
                        'method' => 'post',
                        'pjax' => 1,
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
